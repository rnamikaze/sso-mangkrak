<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Feedback Poll</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/styles/main.css?v=2') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/fontaws/css/all.min.css') }}">
    <style>
        html {
            font-size: 90%;
            scroll-behavior: smooth;
        }

        * {
            padding: 0px;
            margin: 0px;
        }
    </style>
</head>
