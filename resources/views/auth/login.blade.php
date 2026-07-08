<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Our Journey Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Lora:wght@400;500&family=DM+Mono:wght@400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Lora', Georgia, serif; }
        .font-display { font-family: 'Cormorant Garamond', Georgia, serif; }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-[#F6F8F1] via-[#eef2e6] to-[#e6eed9] flex items-center justify-center p-4">

<div class="w-full max-w-md">
    {{-- Logo / Branding --}}
    <div class="text-center mb-8">
        <div class="text-5xl mb-3 select-none">🌿</div>
        <h1 class="font-display text-2xl text-[#2B2E27] italic">Our Journey</h1>
        <p class="text-sm text-[#8FA283] mt-1">Admin Panel</p>
    </div>

    {{-- Card --}}
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/60 p-8">
        <h2 class="text-lg font-semibold text-[#2B2E27] mb-6">Masuk ke Panel Admin</h2>

        {{-- Session Status --}}
        @if(session('status'))
            <div class="mb-4 text-sm text-green-600 bg-green-50 rounded-lg px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            {{-- Username (disimpan di kolom email) --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                <input id="email" type="text" name="email" value="{{ old('email') }}"
                       required autofocus autocomplete="username"
                       class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-[#435A3C] focus:ring-[#435A3C] transition-colors"
                       placeholder="dini atau ubay">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input id="password" type="password" name="password"
                       required autocomplete="current-password"
                       class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-[#435A3C] focus:ring-[#435A3C] transition-colors"
                       placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded border-gray-300 text-[#435A3C] focus:ring-[#435A3C]">
                <label for="remember_me" class="ms-2 text-sm text-gray-600">Ingat saya</label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full rounded-xl bg-[#435A3C] hover:bg-[#364a30] text-white font-medium py-2.5 text-sm transition-colors mt-2">
                Masuk
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        Hanya akun yang diotorisasi yang dapat mengakses panel ini.
    </p>
</div>

</body>
</html>
