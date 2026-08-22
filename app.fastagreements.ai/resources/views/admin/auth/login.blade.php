<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Login - Fast Agreement</title>
    <meta content="Fast Agreement Admin Login" name="description">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/logo/logo.jpeg') }}" rel="icon">
    <link href="{{ asset('assets/img/logo/logo.jpeg') }}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <style>
        body {
            background-color: #f6f9ff;
            font-family: "Open Sans", sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            max-width: 420px;
            width: 100%;
            padding: 40px 30px;
            transition: all 0.3s ease;
        }

        .login-card:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo img {
            max-width: 100%;
            height: auto;
            max-height: 60px;
            object-fit: contain;
        }

        .btn-black {
            background-color: #111111;
            color: #ffffff;
            border: 1px solid #111111;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 6px;
            transition: all 0.2s ease-in-out;
        }

        .btn-black:hover, .btn-black:focus {
            background-color: #ffffff;
            color: #111111;
            border-color: #111111;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus {
            border-color: #111111;
            box-shadow: 0 0 0 0.2rem rgba(17, 17, 17, 0.15);
        }

        .form-check-input:checked {
            background-color: #111111;
            border-color: #111111;
        }

        .form-label {
            font-weight: 500;
            color: #444444;
        }

        .error-message {
            font-size: 0.85rem;
            color: #dc3545;
            margin-top: 5px;
        }

        .alert-custom {
            border-radius: 6px;
            font-size: 0.9rem;
            border: 1px solid;
            background-color: #fdfdfd;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <!-- Logo and Branding -->
        <div class="brand-logo">
            @if(file_exists(public_path('assets/img/logo/dashboard_logo.png')))
                <img src="{{ asset('assets/img/logo/dashboard_logo.png') }}?v={{ filemtime(public_path('assets/img/logo/dashboard_logo.png')) }}" alt="Fast Agreement">
            @else
                <h3 class="fw-bold text-dark m-0">Fast Agreement</h3>
                <span class="text-muted small">ADMIN PORTAL</span>
            @endif
        </div>
        <h4 class="text-center fw-bold text-dark mb-4">Admin Authentication</h4>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success alert-custom alert-dismissible fade show border-success text-success" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="alert alert-danger alert-custom alert-dismissible fade show border-danger text-danger" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email')
                    <div class="error-message">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                @error('password')
                    <div class="error-message">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="mb-4 form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label for="remember" class="form-check-label text-muted small">Remember this device</label>
            </div>

            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-black">Sign In</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

</body>

</html>
