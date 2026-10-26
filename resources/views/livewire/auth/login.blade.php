<div @class(['w-full max-w-md', 'mx-auto px-4 py-12 sm:py-20' => $citizen ?? false])>
    <div class="rounded-2xl bg-white p-6 shadow-xl sm:p-8">
        <h1 class="text-xl font-bold text-slate-900">{{ $heading }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $subheading }}</p>

        @if (session('status'))
            <x-ui.alert type="success" class="mt-5">{{ session('status') }}</x-ui.alert>
        @endif

        <form wire:submit="login" class="mt-6 space-y-5">
            <x-ui.input name="email" type="email" wire:model="email" :label="__('Email')" required autofocus autocomplete="username" class="ltr-nums" />
            <x-ui.input name="password" type="password" wire:model="password" :label="__('Password')" required autocomplete="current-password" class="ltr-nums" />

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-600">
                {{ __('Remember me') }}
            </label>

            <x-ui.button type="submit" class="w-full" size="lg" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">{{ __('Log in') }}</span>
                <span wire:loading wire:target="login">{{ __('Signing in…') }}</span>
            </x-ui.button>
        </form>

        @if ($citizen ?? false)
            <p class="mt-6 border-t border-slate-100 pt-5 text-sm text-slate-500">
                {{ __('New to the portal?') }}
                <a href="{{ route('citizen.register') }}" class="font-medium text-brand-700 hover:underline">{{ __('Create an account') }}</a>
            </p>
        @endif
    </div>
</div>
