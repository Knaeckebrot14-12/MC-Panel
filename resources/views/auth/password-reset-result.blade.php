<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ config('app.name', 'Recoded Ptero') }}</title>
    @include('partials.branding')
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #1f2933; color: #e4e7eb; font-family: system-ui, sans-serif; }
        .card { max-width: 420px; margin: 16px; padding: 28px; background: #323f4b; border-radius: 8px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,.3); }
        h1 { font-size: 20px; margin: 0 0 12px; }
        p { color: #cbd2d9; line-height: 1.5; margin: 0 0 20px; }
        a { display: inline-block; padding: 10px 18px; border-radius: 4px; background: var(--rp-accent, #2563eb); color: #fff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ $valid ? trans('passwords.confirm_page.sent_title') : trans('passwords.confirm_page.invalid_title') }}</h1>
        <p>{{ $valid ? trans('passwords.confirm_page.sent_text') : trans('passwords.confirm_page.invalid_text') }}</p>
        <a href="/auth/login">{{ trans('passwords.confirm_page.login') }}</a>
    </div>
</body>
</html>
