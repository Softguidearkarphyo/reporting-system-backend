<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Backend</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <style>
        body {
            margin: 0;
            font-family: 'Instrument Sans', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        .container {
            max-width: 600px;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        p {
            color: #94a3b8;
        }

        .status {
            margin-top: 20px;
            padding: 10px 20px;
            background: #22c55e;
            color: #022c22;
            border-radius: 8px;
            display: inline-block;
            font-weight: 600;
        }

        .links {
            margin-top: 30px;
        }

        .links a {
            color: #38bdf8;
            text-decoration: none;
            margin: 0 10px;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Backend API Running</h1>
    <p>Your Laravel backend is alive and ready.</p>

    <div class="status">
        Status: OK
    </div>

    <!-- <div class="links">
        <a href="/api">API Base</a>
        <a href="/api/staff">Staff Endpoint</a>
    </div> -->

    <p style="margin-top:40px; font-size:12px;">
        Laravel {{ app()->version() }} | PHP {{ phpversion() }}
    </p>
</div>

</body>
</html>