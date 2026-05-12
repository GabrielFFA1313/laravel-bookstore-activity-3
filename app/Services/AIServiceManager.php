<?php

namespace App\Services;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class AIServiceManager
{
    public function generate(string $prompt, string $provider = null): array
    {
        $provider = $provider ?? config('ai.default_provider');

        try {
            $result = match($provider) {
                'gemini' => $this->callGemini($prompt),
                'ollama' => $this->callOllama($prompt),
                default  => $this->callGemini($prompt),
            };

            $this->logUsage($provider, $prompt, $result['text'], true);
            return $result;

        } catch (\Exception $e) {
            Log::warning("AI provider '{$provider}' failed: " . $e->getMessage());
            $this->logUsage($provider, $prompt, '', false, $e->getMessage());

            if (config('ai.fallback_enabled') && $provider !== 'ollama') {
                if (config('ai.providers.ollama.enabled')) {
                    Log::info('Falling back to Ollama...');
                    $result = $this->callOllama($prompt);
                    $this->logUsage('ollama', $prompt, $result['text'], true);
                    return $result;
                }
                throw new \RuntimeException('All AI providers unavailable. Ollama is disabled.');
                }

            throw new \RuntimeException('All AI providers unavailable.');
            }
        }

    private function callGemini(string $prompt): array
    {
        $apiKey   = config('ai.providers.gemini.api_key');
        $endpoint = config('ai.providers.gemini.endpoint');

        $response = Http::timeout(30)->post("{$endpoint}?key={$apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
        ]);

        if ($response->failed()) {
            throw new \Exception('Gemini API error: ' . $response->body());
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        return ['text' => $text, 'provider' => 'gemini'];
    }

    private function callOllama(string $prompt): array
    {
        $baseUrl = config('ai.providers.ollama.base_url');
        $model   = config('ai.providers.ollama.model');

        $response = Http::timeout(120)->post("{$baseUrl}/api/generate", [
            'model'  => $model,
            'prompt' => $prompt,
            'stream' => false,
        ]);

        if ($response->failed()) {
            throw new \Exception('Ollama error: ' . $response->body());
        }

        return ['text' => $response->json('response'), 'provider' => 'ollama'];
    }

    private function logUsage(
        string $provider,
        string $prompt,
        string $output,
        bool $success,
        string $error = null
    ): void {
        $tokens = AiUsageLog::estimateTokens($prompt . $output);

        AiUsageLog::create([
            'provider'      => $provider,
            'feature'       => 'review_analysis',
            'tokens_used'   => $tokens,
            'cost_estimate' => AiUsageLog::estimateCost($provider, $tokens),
            'success'       => $success,
            'error_message' => $error,
        ]);
         // Audit log every AI decision
        Log::channel('single')->info('AI Decision', [
            'feature'       => 'review_analysis',
            'input_hash'    => md5($prompt),
            'output_hash'   => md5($output),
            'provider_used' => $provider,
            'tokens_used'   => $tokens,
            'cost_estimate' => AiUsageLog::estimateCost($provider, $tokens),
            'success'       => $success,
            'error'         => $error,
            'user_id'       => auth()->id() ?? 'system',
            'timestamp'     => now(),
                ]);
    }
}