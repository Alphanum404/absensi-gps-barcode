<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  <!-- Scripts -->
  @if (app()->environment('local'))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <link rel="stylesheet" href="{{ asset('build/assets/app-BJ2defzI.css') }}">
    <script src="{{ asset('build/assets/app-0o1AOg_t.js') }}" defer></script>
@endif

  <!-- Styles -->
  @livewireStyles
</head>

<body class="font-sans antialiased">
  <div class="font-sans text-gray-900 antialiased dark:text-gray-100">

    <div class="absolute right-4 top-4">
      <x-theme-toggle x-data />
    </div>

    {{ $slot }}
  </div>

  @livewireScripts
</body>

</html>
