<x-guest-layout>
    <div class="mb-4">
        <h2 class="text-lg font-medium text-gray-900">Set Up Authenticator App</h2>
        <p class="mt-1 text-sm text-gray-600">
            Scan the QR code below with your authenticator app
            (Google Authenticator, Authy, etc.), then enter the
            6-digit code to confirm.
        </p>
    </div>

    {{-- QR Code --}}
    <div class="flex justify-center my-6">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrUrl) }}"
             alt="QR Code"
             class="border-4 border-white shadow rounded"
        />
    </div>

    {{-- Manual entry secret --}}
    <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200 text-center">
        <p class="text-xs text-gray-500 mb-1">Can't scan? Enter this key manually:</p>
        <code class="text-sm font-mono font-bold tracking-widest text-gray-800">
            {{ $secret }}
        </code>
    </div>

    {{-- Confirm code --}}
    <form method="POST" action="{{ route('two-factor.confirm.totp') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('6-Digit Code from App')" />
            <x-text-input
                id="code"
                name="code"
                type="text"
                inputmode="numeric"
                maxlength="6"
                autofocus
                class="block mt-1 w-full tracking-widest text-center text-2xl"
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-primary-button class="w-full justify-center">
                {{ __('Confirm & Enable 2FA') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>