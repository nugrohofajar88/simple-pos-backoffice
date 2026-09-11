<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('app.name') }}</title>
    @include('layouts.tailwind-config')
</head>
<body class="bg-surface min-h-screen flex items-center justify-center font-body-md text-body-md text-on-surface">
    <div class="w-full max-w-sm bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.08)] p-6">
        <h1 class="font-headline-sm text-headline-sm text-primary mb-4 text-center">{{ config('app.name') }}</h1>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-error bg-error-container px-4 py-2 font-body-sm text-body-sm text-on-error-container">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container">
            </div>
            <div>
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container">
            </div>
            <button type="submit"
                    class="w-full rounded-lg bg-primary text-on-primary py-2 font-label-lg text-label-lg hover:opacity-90">
                Masuk
            </button>
        </form>
    </div>
</body>
</html>
