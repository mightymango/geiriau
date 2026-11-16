<!DOCTYPE html>
<html lang="cy">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Geiriau</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-stone-50 text-stone-900 antialiased select-none dark:bg-stone-950 dark:text-stone-100">
    @include('partials.launch-intro')

    <div class="mx-auto flex min-h-dvh max-w-md flex-col"
         style="padding-top: env(safe-area-inset-top); padding-bottom: calc(4.5rem + env(safe-area-inset-bottom));">
        <main class="flex-1 px-5 py-6">
            {{ $slot }}
        </main>
    </div>

    @php
        $tabs = [
            ['home', 'Heddiw', 'M3 10.5 12 3l9 7.5V21h-6v-6H9v6H3z'],
            ['study', 'Ymarfer', 'M4 6h16M4 12h16M4 18h10'],
            ['words', 'Geiriau', 'M4 5h7v14H4zM13 5h7v14h-7z'],
            ['settings', 'Gosodiadau', 'M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm8 3-2-1 .5-2-2-1.5L15 6l-1-2h-4L9 6 7.5 7.5l-2 1.5L6 11l-2 1 2 1-.5 2 2 1.5L9 18l1 2h4l1-2 1.5-1.5 2-1.5L18 13z'],
        ];
    @endphp
    <nav class="fixed inset-x-0 bottom-0 border-t border-stone-200 bg-white/95 backdrop-blur dark:border-stone-800 dark:bg-stone-900/95"
         style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="mx-auto grid max-w-md grid-cols-4">
            @foreach ($tabs as [$route, $label, $path])
                <a href="{{ route($route) }}" wire:navigate
                   class="flex flex-col items-center gap-1 py-2.5 text-xs {{ request()->routeIs($route) ? 'text-red-700 dark:text-red-400 font-semibold' : 'text-stone-500' }}">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="{{ $path }}"/></svg>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    @livewireScripts
</body>
</html>
