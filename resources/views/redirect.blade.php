<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:url" content="{{ $url }}">
    <title>Redirecting...</title>
    <!-- Other head elements as needed -->
</head>

<body>
    <script>
        window.location.href = "{{ $url }}";
    </script>
</body>

</html>
