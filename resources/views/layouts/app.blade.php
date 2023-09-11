<!DOCTYPE html>
<html>
    <body>
        <div id="app">
            @yield('content')
        </div>

        @vite(['resources/js/app.js', 'resources/scss/app-pos.scss'])
    </body>
</html>