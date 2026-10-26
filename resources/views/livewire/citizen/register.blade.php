<div class="mx-auto w-full max-w-2xl px-4 py-12 sm:py-16">
    <div class="rounded-2xl bg-white p-6 shadow-xl sm:p-8">
        <h1 class="text-xl font-bold text-slate-900">{{ __('Create an account') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Register once to file complaints from abroad and follow every update online.') }}</p>

        <form wire:submit="register" class="mt-6 space-y-5" novalidate>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input name="form.name" wire:model="form.name" :label="__('Full name')" required autocomplete="name"
                            :placeholder="__('e.g. Ahmed Khan')" wrapper="sm:col-span-2" />

                <x-ui.select name="form.country" wire:model="form.country" :label="__('Country of residence')" required
                             :placeholder="__('Select country')" :options="$countries" />

                <x-ui.input name="form.phone" wire:model="form.phone" :label="__('WhatsApp number')" required type="tel"
                            inputmode="tel" placeholder="+447700900123" class="ltr-nums" autocomplete="tel"
                            :hint="__('With country code. We use it to reach you about your complaint.')" />

                <x-ui.input name="form.cnic_passport" wire:model="form.cnic_passport" :label="__('CNIC / NICOP or passport number')" required
                            placeholder="12345-1234567-1" class="ltr-nums uppercase" wrapper="sm:col-span-2"
                            :hint="__('Your complaints are registered against this number.')" />

                <x-ui.input name="form.email" wire:model="form.email" :label="__('Email')" required type="email"
                            class="ltr-nums" autocomplete="email" placeholder="name@example.com" wrapper="sm:col-span-2" />

                <x-ui.input name="form.password" wire:model="form.password" :label="__('Password')" required type="password"
                            class="ltr-nums" autocomplete="new-password" :hint="__('At least 8 characters, with letters and numbers.')" />

                <x-ui.input name="form.password_confirmation" wire:model="form.password_confirmation" :label="__('Confirm password')" required
                            type="password" class="ltr-nums" autocomplete="new-password" />
            </div>

            <x-ui.button type="submit" class="w-full" size="lg" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="register">{{ __('Create account') }}</span>
                <span wire:loading wire:target="register">{{ __('Creating account…') }}</span>
            </x-ui.button>
        </form>

        <p class="mt-6 border-t border-slate-100 pt-5 text-sm text-slate-500">
            {{ __('Already registered?') }}
            <a href="{{ route('citizen.login') }}" class="font-medium text-brand-700 hover:underline">{{ __('Log in') }}</a>
        </p>
    </div>
</div>
