<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        @if($type === 'email')
            A 6-digit verification code has been sent to your email address.
            It expires in 10 minutes.
        @else
            Open your authenticator app and enter the 6-digit code shown.
        @endif
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Verification Code')" />
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
                {{ __('Verify') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('two-factor.recovery') }}"
           class="text-sm text-gray-600 hover:text-gray-900 underline">
            Use a recovery code instead
        </a>
    </div>
</x-guest-layout>