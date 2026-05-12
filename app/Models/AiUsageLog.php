<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'provider', 'feature', 'tokens_used',
        'cost_estimate', 'book_id', 'user_id',
        'success', 'error_message',
    ];

    // Approximate cost per token for each provider
    public static function estimateCost(string $provider, int $tokens): float
    {
        return match($provider) {
            'gemini' => $tokens * 0.000000125, // $0.125 per 1M tokens
            'openai' => $tokens * 0.000000150, // $0.150 per 1M tokens
            'ollama' => 0.0,                   // Free local
            default  => 0.0,
        };
    }

    // Rough token estimator (1 token ≈ 4 chars)
    public static function estimateTokens(string $text): int
    {
        return (int) (strlen($text) / 4);
    }
}