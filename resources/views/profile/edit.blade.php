@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        Profile Information
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Update your account's profile information and email address.
                    </p>
                </header>

                <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
                    @csrf
                    @method('patch')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input 
                            id="name" 
                            name="name" 
                            type="text" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                            value="{{ old('name', $user->name) }}" 
                            required 
                            autofocus 
                            autocomplete="name"
                        >
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                            value="{{ old('email', $user->email) }}" 
                            required 
                            autocomplete="username"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-2">
                                <p class="text-sm text-gray-800">
                                    Your email address is unverified.
                                    <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900">
                                        Click here to re-send the verification email.
                                    </button>
                                </p>

                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-2 font-medium text-sm text-green-600">
                                        A new verification link has been sent to your email address.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Save
                        </button>

                        @if (session('status') === 'profile-updated')
                            <p class="text-sm text-gray-600">Saved.</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        Update Password
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Ensure your account is using a long, random password to stay secure.
                    </p>
                </header>

                <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
                    @csrf
                    @method('put')

                    <div>
                        <label for="update_password_current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input 
                            id="update_password_current_password" 
                            name="current_password" 
                            type="password" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                            autocomplete="current-password"
                        >
                        @error('current_password', 'updatePassword')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="update_password_password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input 
                            id="update_password_password" 
                            name="password" 
                            type="password" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                            autocomplete="new-password"
                        >
                        @error('password', 'updatePassword')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input 
                            id="update_password_password_confirmation" 
                            name="password_confirmation" 
                            type="password" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                            autocomplete="new-password"
                        >
                        @error('password_confirmation', 'updatePassword')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Save
                        </button>

                        @if (session('status') === 'password-updated')
                            <p class="text-sm text-gray-600">Saved.</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        Delete Account
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Once your account is deleted, all of its resources and data will be permanently deleted.
                    </p>
                </header>

                <button 
                    type="button"
                    class="mt-6 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700"
                    onclick="if(confirm('Are you sure you want to delete your account? This cannot be undone.')) { document.getElementById('delete-account-form').submit(); }"
                >
                    Delete Account
                </button>

                <form id="delete-account-form" method="post" action="{{ route('profile.destroy') }}" class="hidden">
                    @csrf
                    @method('delete')
                </form>
            </div>
        </div>
    </div>
@endsection