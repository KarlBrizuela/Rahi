<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rahi')</title>


    <script src="https://unpkg.com/lucide@latest"></script>

     <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
        @yield('styles')
        @yield('Styles')
</head>
<body>
       @yield('content')

       
<script>
    lucide.createIcons();
</script>


    @stack('scripts')
    @yield('scripts')
</body>


</html>