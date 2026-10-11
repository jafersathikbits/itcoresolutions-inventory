<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Welcome back</h2>
        <p class="mt-1 text-sm text-slate-500">Sign in to your inventory account.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email"
                          type="email"
                          name="email"
                          :value="old('email')"
                          placeholder="you@company.com"
                          required
                          autofocus
                          autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div x-data="{ show: false }">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-brand-700 hover:underline" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <div class="relative">
                <x-text-input id="password"
                              class="pr-10"
                              x-bind:type="show ? 'text' : 'password'"
                              type="password"
                              name="password"
                              required
                              autocomplete="current-password" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 mt-1 flex items-center px-3 text-slate-400 hover:text-slate-600" :aria-label="show ? 'Hide password' : 'Show password'">
                    <x-icon name="eye" class="h-5 w-5" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <label for="remember_me" class="inline-flex items-center gap-2">
            <input id="remember_me" type="checkbox" class="form-checkbox" name="remember">
            <span class="text-sm text-slate-600">{{ __('Remember me') }}</span>
        </label>

        <x-primary-button class="w-full py-2.5">
            {{ __('Log in') }}
        </x-primary-button>
    </form>
</x-guest-layout>
