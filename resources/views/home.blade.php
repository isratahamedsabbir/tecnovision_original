<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
        }

        .container {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #333;
        }

        p {
            font-size: 16px;
            color: #555;
            margin-bottom: 25px;
        }

        .btn-warning {
            display: inline-block;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background-color: #ffc107;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .btn-primary {
            display: inline-block;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.03);
        }
    </style>
</head>
<body>
    <div class="container">
        @if(auth()->check())
            <h1>Welcome, {{ auth()->user()->name }}!</h1>
            <p>You are logged in.</p>
            <a href="{{ route('logout') }}" class="btn-warning">Logout</a>
            <a href="{{ route('admin.dashboard') }}" class="btn-primary">Dashboard</a>
        @else
            <h1>Welcome to Our Application!</h1>
            <p>Please log in to continue.</p>
            <a href="{{ route('login') }}" class="btn-primary">Login</a>
        @endif
    </div>
</body>
</html>
