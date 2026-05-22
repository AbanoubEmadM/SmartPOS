<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SmartPOS' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/pos.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
    {{ $slot }}

    @livewireScripts
    <script>
        function updateClock() {
            const el = document.getElementById('digital-clock');
            if (!el) return;
            el.textContent = new Date().toLocaleString('ar-EG', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>
</html>
