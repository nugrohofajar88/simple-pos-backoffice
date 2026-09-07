<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-semibold mb-4 text-center">{{ config('app.name') }}</h1>

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-2 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800">
            </div>
            <button type="submit"
                    class="w-full rounded bg-gray-900 text-white py-2 text-sm font-medium hover:bg-gray-800">
                Masuk
            </button>
        </form>
    </div>
</body>
</html>
