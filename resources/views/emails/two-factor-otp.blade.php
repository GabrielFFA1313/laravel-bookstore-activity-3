<x-mail::message>
# Your Verification Code

Use the code below to complete your login.
This code expires in **10 minutes**.

<x-mail::panel>
# {{ $code }}
</x-mail::panel>

If you did not attempt to log in, please change your password immediately.

Thanks,
{{ config('app.name') }}
</x-mail::message>