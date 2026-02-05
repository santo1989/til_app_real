<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - TIL Performance Appraisal System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }

        .login-body {
            padding: 30px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-outline-primary {
            background: transparent;
            color: #fff;
            border: 2px solid transparent;
            background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-origin: border-box;
            -webkit-background-clip: padding-box;
            background-clip: padding-box;
        }

        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <h3 class="mb-0">TIL Performance Appraisal</h3>
            <p class="mb-0 mt-2">Tosrifa Industries Limited</p>
        </div>
        <div class="login-body">
            @if (session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                        required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required
                        autocomplete="current-password">
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-outline-primary btn-lg">Log In</button>
                </div>

                <div class="text-center">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none">Forgot your password?</a>
                    @endif
                </div>
            </form>

            <hr class="my-4">
            <p class="text-center text-muted mb-0">
                <small>For new employees, please contact HR Admin to create your account.</small>
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
