<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration - TIL Performance Appraisal System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-card {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }

        .register-body {
            padding: 40px;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 5px;
        }

        .btn-outline-primary {
            background: transparent;
            color: #fff;
            border: 2px solid transparent;
            padding: 12px 30px;
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
    <div class="register-card">
        <div class="register-header">
            <h3 class="mb-0">Employee Registration</h3>
            <p class="mb-0 mt-2">TIL Performance Appraisal System</p>
            <p class="mb-0"><small>Tosrifa Industries Limited</small></p>
        </div>
        <div class="register-body">
            <div class="info-box mb-4">
                <h5 class="text-primary mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                        class="bi bi-info-circle-fill me-2" viewBox="0 0 16 16" style="vertical-align: middle;">
                        <path
                            d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
                    </svg>
                    Self-Registration Not Available
                </h5>
                <p class="mb-3">The TIL Performance Appraisal System uses a centralized user management approach. All
                    employee accounts are created and managed by the HR Administration department.</p>
                <p class="mb-0"><strong>This ensures:</strong></p>
                <ul class="mb-3">
                    <li>Accurate employee information in the system</li>
                    <li>Proper role assignment and permissions</li>
                    <li>Correct department and reporting structure</li>
                    <li>Security and compliance with company policies</li>
                </ul>
            </div>

            <div class="alert alert-info">
                <h6 class="alert-heading">New Employee?</h6>
                <p class="mb-0">Please contact the <strong>HR Administration</strong> department to have your account
                    created. They will provide you with your login credentials once your account is set up.</p>
            </div>

            <div class="alert alert-warning">
                <h6 class="alert-heading">Already Have an Account?</h6>
                <p class="mb-0">If you've already been provided with login credentials, you can access the system
                    using the login page.</p>
            </div>

            <div class="d-grid gap-2 mt-4">
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        class="bi bi-box-arrow-in-right me-2" viewBox="0 0 16 16" style="vertical-align: middle;">
                        <path fill-rule="evenodd"
                            d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z" />
                        <path fill-rule="evenodd"
                            d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
                    </svg>
                    Go to Login Page
                </a>
            </div>

            <hr class="my-4">
            <div class="text-center text-muted">
                <small>
                    <strong>Contact HR Administration:</strong><br>
                    Email: hr@tosrifa.com | Phone: +880-XXXX-XXXXXX
                </small>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
