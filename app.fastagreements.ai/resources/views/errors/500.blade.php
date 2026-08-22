<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error | Fast Agreement</title>

    <!-- Favicon -->
    <link href="{{ asset('assets/img/logo/logo.jpeg') }}" rel="icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css?family=Outfit:300,400,600,700|Plus+Jakarta+Sans:400,500,600,700" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

    <style>
        body {
            background-color: #0b0c10;
            background-image: radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.03) 0%, transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.02) 0%, transparent 40%);
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .error-container {
            max-width: 600px;
            width: 100%;
            text-align: center;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 50px 40px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .brand-logo img {
            max-height: 45px;
            height: auto;
            object-fit: contain;
            filter: brightness(0) invert(1);
            margin-bottom: 25px;
        }

        .brand-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            letter-spacing: 1px;
            font-size: 1.8rem;
            color: #ffffff;
            margin-bottom: 25px;
        }

        .error-code {
            font-family: 'Outfit', sans-serif;
            font-size: 7.5rem;
            font-weight: 700;
            line-height: 1;
            margin: 0;
            background: linear-gradient(180deg, #ffffff 30%, #444444 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: float 4s ease-in-out infinite;
        }

        .error-icon {
            font-size: 3.5rem;
            color: rgba(255, 255, 255, 0.9);
            margin: 20px 0;
        }

        .error-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .error-message {
            font-size: 1rem;
            color: #b3b3b3;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .btn-bw-primary {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #ffffff;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 8px;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-bw-primary:hover {
            background-color: transparent;
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.8);
        }

        .btn-bw-secondary {
            background-color: transparent;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 8px;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-bw-secondary:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.4);
            color: #ffffff;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>

<body>

    <div class="error-container">
        <!-- Logo and Branding -->
        <div class="brand-logo">
            @if(file_exists(public_path('assets/img/logo/name.jpeg')))
                <img src="{{ asset('assets/img/logo/name.jpeg') }}" alt="Fast Agreement">
            @else
                <div class="brand-text">Fast Agreement</div>
            @endif
        </div>

        <!-- Floating Error Code -->
        <div class="error-code">500</div>

        <!-- Error Icon -->
        <div class="error-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>

        <!-- Error Heading -->
        <h1 class="error-title">Internal Server Error</h1>

        <!-- Error Description -->
        <p class="error-message">
            Something went wrong on our servers. Rest assured, our team is already looking into it.
        </p>

        <!-- Dynamic Action Buttons -->
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <button onclick="window.history.back();" class="btn btn-bw-secondary">
                <i class="bi bi-arrow-left me-2"></i>Go Back
            </button>
            @auth
                <a href="{{ route('dashboard.index') }}" class="btn btn-bw-primary">
                    <i class="bi bi-grid me-2"></i>Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-bw-primary">
                    <i class="bi bi-house me-2"></i>Go to Home
                </a>
            @endauth
        </div>
    </div>

</body>

</html>
