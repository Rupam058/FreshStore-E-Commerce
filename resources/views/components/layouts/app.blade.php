<!DOCTYPE html>
<html
   lang="{{ str_replace('_', '-', app()->getLocale()) }}"
   class="h-full"
>

<head>
   <meta charset="utf-8">
   <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0"
   >

   <title>{{ $title ?? 'FreshStore' }}</title>
   @vite(['resources/css/app.css', 'resources/js/app.js'])
   @livewireStyles()
</head>

<body class="bg-slate-200 dark:bg-slate-700 h-full flex flex-col">
   @livewire('partials.navbar')
   <main class="flex-grow">
      {{ $slot }}
   </main>
   @livewire('partials.footer')
   @livewireScripts()
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
