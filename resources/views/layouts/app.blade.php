<!DOCTYPE html>
<html lang="es" class="{{ session('darkMode') ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Moto Repuesto Divino Niño' }}</title>
    
    <link rel="stylesheet" href="{{ asset('css/stylesmenu.css') }}">
    <link href="https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    
    @livewireStyles
    
    @stack('styles')
</head>
<body class="{{ session('darkMode') ? 'dark' : '' }}">
    {{ $slot }}
    
    @livewireScripts
</body>
</html>