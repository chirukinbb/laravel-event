<x-mail::message>
    {{-- Шапка с вашим логотипом --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            <img src="https://vibecheck.guten.website/icon.png" alt="{{ config('app.name') }}"
                 style="max-height: 50px; width: auto;">
        </x-mail::header>
    </x-slot:header>

    # Hello {{ $name }}!

    Thank you for registering on our platform.
    Your account has been created successfully.

    Here are your login credentials:
    - **Email:** {{ $email }}
    - **Password:** {{ $password }}

    Please keep this password secure. You can change it after logging in.

    <x-mail::button :url="$actionUrl">
        Login Now
    </x-mail::button>

    If you did not register, no further action is required.

    Best regards,<br>
    {{ config('app.name') }}
</x-mail::message>