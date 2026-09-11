<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Login - Fast Agreements</title>
    <meta content="Fast Agreements Admin Login" name="description">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/logo/logo.jpeg') }}" rel="icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <style>
        :root {
            --brand-ink: #17213a;
            --brand-navy: #17356f;
            --brand-gold: #c9a227;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Open Sans", sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        .login-page {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: #0f1c3f url('{{ asset('assets/img/logo/background.png') }}') center center / cover no-repeat;
        }

        .login-page::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(
                    circle at center,
                    rgba(238, 245, 252, 0.10) 0%,
                    rgba(10, 27, 55, 0.20) 100%
                );
        }

        .login-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.94);
            -webkit-backdrop-filter: blur(14px);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.65);
            border-radius: 18px;
            box-shadow: 0 24px 65px rgba(15, 35, 70, 0.22);
            max-width: 380px;
            width: 100%;
            padding: 26px 30px 24px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            margin-bottom: 20px;
        }

        .brand-logo img {
            max-height: 56px;
            width: auto;
            object-fit: contain;
        }

        .brand-logo .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: linear-gradient(135deg, #102653, #1d427e);
            color: #ffffff;
            font-size: 1.05rem;
        }

        .brand-logo .brand-text {
            font-family: "Poppins", "Open Sans", sans-serif;
            font-size: 1.2rem;
            line-height: 1;
            color: var(--brand-navy);
        }

        .brand-logo .brand-text .brand-fast {
            font-weight: 400;
        }

        .brand-logo .brand-text .brand-agreements {
            font-weight: 600;
        }

        .brand-logo .brand-sub {
            display: block;
            font-family: "Open Sans", sans-serif;
            font-size: 0.6rem;
            font-weight: 600;
            letter-spacing: 0.16em;
            color: var(--brand-gold);
            margin-top: 3px;
        }

        .login-title {
            text-align: center;
            font-weight: 600;
            color: var(--brand-ink);
            font-size: 1.3rem;
            margin: 0 0 4px;
        }

        .login-subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 0.85rem;
            margin: 0 0 22px;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            font-size: 0.875rem;
            margin-bottom: 6px;
        }

        .field {
            position: relative;
        }

        .field .field-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #8a97ac;
            font-size: 0.95rem;
            pointer-events: none;
        }

        .field .toggle-password {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #8a97ac;
            padding: 4px 8px;
            font-size: 0.95rem;
            line-height: 1;
            cursor: pointer;
        }

        .field .toggle-password:hover {
            color: var(--brand-navy);
        }

        .form-control,
        input.form-control {
            height: 48px;
            background: #ffffff;
            border: 1px solid #d7dfeb;
            border-radius: 9px;
            color: #17213a;
            font-size: 0.9rem;
            padding: 0 14px 0 38px;
        }

        .field.no-icon .form-control {
            padding-left: 14px;
        }

        #password {
            padding-right: 40px;
        }

        .form-control::placeholder {
            color: #9aa6b8;
            font-size: 0.88rem;
        }

        .form-control:focus {
            background: #ffffff;
            border-color: var(--brand-navy);
            box-shadow: 0 0 0 4px rgba(23, 53, 111, 0.10);
            outline: none;
        }

        .form-control.is-invalid,
        .was-validated .form-control:invalid {
            border-color: #e0736f;
            background-image: none;
            padding-right: 14px;
        }

        .form-control.is-invalid:focus,
        .was-validated .form-control:invalid:focus {
            box-shadow: 0 0 0 4px rgba(224, 115, 111, 0.12);
        }

        .invalid-feedback {
            width: auto;
            font-size: 0.8rem;
            margin-top: 6px;
        }

        .invalid-feedback:not(:empty) {
            display: block;
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset;
            -webkit-text-fill-color: #17213a;
            caret-color: #17213a;
        }

        .form-check {
            margin: 12px 0 18px;
        }

        .form-check-label {
            font-size: 0.82rem;
        }

        .form-check-input:checked {
            background-color: var(--brand-navy);
            border-color: var(--brand-navy);
        }

        .sign-in-btn {
            width: 100%;
            height: 50px;
            border: 0;
            border-radius: 9px;
            background: linear-gradient(135deg, #102653, #1d427e);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.03em;
            box-shadow: 0 8px 20px rgba(16, 38, 83, 0.20);
            transition: 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .sign-in-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 11px 24px rgba(16, 38, 83, 0.27);
            color: #ffffff;
        }

        .sign-in-btn:disabled {
            opacity: 0.75;
            transform: none;
            cursor: progress;
        }

        .btn-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.45);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: btn-spin 0.7s linear infinite;
        }

        @keyframes btn-spin {
            to { transform: rotate(360deg); }
        }

        .error-message {
            font-size: 0.8rem;
            color: #c0564f;
            margin-top: 6px;
        }

        .hint-message {
            font-size: 0.78rem;
            color: #8a6d3b;
            margin-top: 6px;
        }

        .alert-custom {
            border-radius: 9px;
            font-size: 0.84rem;
            border: 1px solid;
            background-color: #fdfdfd;
        }

        .security-note {
            margin-top: 14px;
            padding: 9px 12px;
            border-radius: 9px;
            background: rgba(23, 53, 111, 0.06);
            color: #4b5a76;
            font-size: 0.78rem;
            text-align: center;
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #9ca3af;
            font-size: 0.74rem;
        }

        @media (max-width: 420px) {
            .login-card {
                padding: 24px 20px 22px;
            }

            .login-page {
                background-position: top center;
            }
        }
    </style>
</head>

<body>

    <div class="login-page"></div>

    <div class="login-card">
        <!-- Logo and Branding -->
        <div class="brand-logo">
            <img src="{{ asset('assets/img/logo/fast_agreements.png') }}?v={{ file_exists(public_path('assets/img/logo/fast_agreements.png')) ? filemtime(public_path('assets/img/logo/fast_agreements.png')) : 1 }}" alt="Fast Agreements">
        </div>

        <h1 class="login-title">Welcome Back!</h1>
        <p class="login-subtitle">Sign in to continue to Fast Agreements.</p>

        <!-- Alert Messages (populated on page load and via AJAX) -->
        <div id="loginAlert"
            class="alert alert-custom alert-dismissible fade show{{ session('success') ? ' alert-success border-success text-success' : (session('error') ? ' alert-danger border-danger text-danger' : ' d-none') }}"
            role="alert">
            <i class="bi {{ session('success') ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-1" id="loginAlertIcon"></i>
            <span id="loginAlertText">{{ session('success') ?? session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
            @csrf

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <div class="field">
                    <i class="bi bi-envelope field-icon"></i>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="you@company.com" required autofocus autocomplete="username">
                </div>
                <div class="invalid-feedback" id="emailError"></div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="field">
                    <i class="bi bi-lock field-icon"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="toggle-password" tabindex="-1" onclick="togglePassword()"><i class="bi bi-eye" id="togglePasswordIcon"></i></button>
                </div>
                <div class="invalid-feedback" id="passwordError"></div>
                <div class="hint-message" id="capsLockHint" style="display: none;">
                    <i class="bi bi-capslock-fill"></i> Caps Lock is on
                </div>
            </div>

            <!-- Remember Me -->
            <div class="mb-4 form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label for="remember" class="form-check-label text-muted small">Remember this device</label>
            </div>

            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="sign-in-btn" id="signInBtn">
                    <span class="btn-label">Sign In</span>
                </button>
            </div>

            <div class="security-note d-none" id="securityNote">
                <i class="bi bi-shield-lock"></i> For your security, access is monitored. Please check your credentials and try again.
            </div>
        </form>

        <div class="login-footer">
            &copy; {{ date('Y') }} Fast Agreements &middot; Authorized Access Only
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function togglePassword() {
            var input = document.getElementById('password');
            var icon = document.getElementById('togglePasswordIcon');
            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !isHidden);
            icon.classList.toggle('bi-eye-slash', isHidden);
        }

        (function () {
            var passwordInput = document.getElementById('password');
            var capsHint = document.getElementById('capsLockHint');
            if (passwordInput && capsHint) {
                var updateCaps = function (e) {
                    var on = e.getModifierState && e.getModifierState('CapsLock');
                    capsHint.style.display = on ? 'block' : 'none';
                };
                passwordInput.addEventListener('keydown', updateCaps);
                passwordInput.addEventListener('keyup', updateCaps);
                passwordInput.addEventListener('blur', function () {
                    capsHint.style.display = 'none';
                });
            }

            var form = document.getElementById('loginForm');
            var btn = document.getElementById('signInBtn');
            var alertBox = document.getElementById('loginAlert');
            var alertIcon = document.getElementById('loginAlertIcon');
            var alertText = document.getElementById('loginAlertText');
            var securityNote = document.getElementById('securityNote');
            var emailInput = document.getElementById('email');
            var passwordInput2 = document.getElementById('password');
            var emailError = document.getElementById('emailError');
            var passwordError = document.getElementById('passwordError');

            function showAlert(type, message) {
                alertBox.classList.remove('d-none', 'alert-success', 'alert-danger', 'border-success', 'border-danger', 'text-success', 'text-danger');
                alertBox.classList.add('alert-' + type, 'border-' + type, 'text-' + type);
                alertIcon.className = 'bi ' + (type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle') + ' me-1';
                alertText.textContent = message;
            }

            function hideAlert() {
                alertBox.classList.add('d-none');
            }

            function clearFieldErrors() {
                [emailInput, passwordInput2].forEach(function (el) {
                    el.classList.remove('is-invalid');
                });
                emailError.textContent = '';
                passwordError.textContent = '';
                securityNote.classList.add('d-none');
            }

            function setFieldError(input, errorEl, messages) {
                input.classList.add('is-invalid');
                errorEl.textContent = messages[0];
            }

            function resetButton() {
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-label">Sign In</span>';
            }

            if (form && btn) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    // Bootstrap's built-in (native) validation
                    if (!form.checkValidity()) {
                        emailError.textContent = emailInput.validationMessage;
                        passwordError.textContent = passwordInput2.validationMessage;
                        form.classList.add('was-validated');
                        return;
                    }
                    form.classList.add('was-validated');

                    clearFieldErrors();
                    hideAlert();
                    btn.disabled = true;
                    btn.innerHTML = '<span class="btn-spinner"></span><span class="btn-label">Signing in…</span>';

                    var formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                return { status: response.status, body: data };
                            });
                        })
                        .then(function (result) {
                            if (result.status === 200 && result.body.success) {
                                showAlert('success', result.body.message || 'Logged in successfully.');
                                window.location.href = result.body.redirect || '/';
                                return;
                            }

                            if (result.status === 422 && result.body.errors) {
                                var errors = result.body.errors;
                                if (errors.email) {
                                    setFieldError(emailInput, emailError, errors.email);
                                }
                                if (errors.password) {
                                    setFieldError(passwordInput2, passwordError, errors.password);
                                }
                                securityNote.classList.remove('d-none');
                                resetButton();
                                return;
                            }

                            showAlert('danger', (result.body && result.body.message) || 'Something went wrong. Please try again.');
                            resetButton();
                        })
                        .catch(function () {
                            showAlert('danger', 'Unable to reach the server. Please try again.');
                            resetButton();
                        });
                });
            }
        })();
    </script>

</body>

</html>
