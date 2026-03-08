<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Enter one of your backup recovery codes to log in.
        Each code can only be used once.
    </div>

    <form method="POST" action="{{ route('two-factor.recovery.verify') }}">
        @csrf

        <div>
            <x-input-label for="recovery_code" :value="__('Recovery Code')" />
            <x-text-input
                id="recovery_code"
                name="recovery_code"
                type="text"
                autofocus
                class="block mt-1 w-full font-mono"
                placeholder="xxxxxxxxxx-xxxxxxxxxx"
            />
            <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-primary-button class="w-full justify-center">
                {{ __('Verify Recovery Code') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('two-factor.challenge') }}"
           class="text-sm text-gray-600 hover:text-gray-900 underline">
            Use verification code instead
        </a>
    </div>
</x-guest-layout>