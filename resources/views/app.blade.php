<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chess Game</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="mb-0">Chess Game</h1>
                        </div>
                        <div class="card-body">
                            <p>Welcome to the Chess Game! This is a Laravel + Vue.js 3 + Bootstrap setup.</p>
                            <button class="btn btn-primary" @click="startGame">Start Game</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>