<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="/pos/two-factor-challenge">
            @csrf

            <!-- TOTP code -->
            <div>
                <x-input-label for="code" value="2FA Code" />
                <x-text-input id="code" class="block mt-1 w-full" type="text" pattern="[0-9]*" inputmode="numeric" name="code" autocomplete="off" required autofocus />
                
                @if(str_contains(Request::server('HTTP_REFERER'), 'two-factor-challenge'))
                <x-input-error :messages="['error'=>'The code is invalid']" class="mt-2" />
                @endif
          
            </div>


                <x-primary-button class="ml-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>