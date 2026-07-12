<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Our Journey</title>

    {{-- Tailwind CSS v3 CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'green-deep': '#435A3C',
                        'green-soft': '#8FA283',
                        'bloom':      '#C97B84',
                        'gold':       '#C7A15A',
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- SortableJS --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    {{-- Google Fonts: Cormorant Garamond + Lora + DM Mono --}}
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Lora:wght@400;500&family=DM+Mono:wght@400&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
        .sortable-ghost { opacity: 0.4; }
        .sortable-drag  { opacity: 1; box-shadow: 0 10px 30px rgba(0,0,0,.15); }
        body, input, select, textarea, button { font-family: 'Lora', Georgia, serif !important; }
        .font-display, h1, h2, h3 { font-family: 'Cormorant Garamond', Georgia, serif !important; }
    </style>
</head>
<body class="h-full font-sans antialiased" x-data>

{{-- Navbar --}}
<nav class="bg-green-deep shadow-md">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-white text-xl">🌿</span>
                <a href="{{ route('admin.memories.index') }}" class="text-white font-semibold text-lg tracking-wide">
                    Our Journey <span class="text-green-300 font-normal text-sm">Admin</span>
                </a>
                <div class="hidden sm:flex items-center gap-4 ml-6 border-l border-green-700 pl-6">
                    <a href="{{ route('admin.memories.index') }}"
                       class="text-sm transition-colors {{ request()->routeIs('admin.memories.*') ? 'text-white font-semibold' : 'text-green-200 hover:text-white' }}">
                        Memories 🌱
                    </a>
                    <a href="{{ route('admin.wishlists.index') }}"
                       class="text-sm transition-colors {{ request()->routeIs('admin.wishlists.*') ? 'text-white font-semibold' : 'text-green-200 hover:text-white' }}">
                        Bucket List 📝
                    </a>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" target="_blank"
                   class="text-green-200 hover:text-white text-sm transition-colors">
                    ↗ Lihat Website
                </a>
                <span class="text-green-300 text-sm">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-md bg-green-700 hover:bg-green-600 px-3 py-1.5 text-sm text-white transition-colors">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

{{-- Flash Messages --}}
@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         x-transition:leave="transition duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-4 right-4 z-50 flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-3 text-white shadow-lg">
        <span>✓</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-4 right-4 z-50 flex items-center gap-2 rounded-lg bg-red-600 px-4 py-3 text-white shadow-lg">
        <span>✗</span>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- Main Content --}}
<main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
</main>

@stack('scripts')
</body>
</html>
