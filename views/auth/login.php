<?php
$title = 'Login - Football Betting Platform';
require_once 'views/layout/header.php';
?>

<div class="container">
    <div class="row justify-content-center" style="min-height: 70vh; align-items: center;">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle display-1 text-primary"></i>
                        <h2 class="mt-3">Welcome Back!</h2>
                        <p class="text-muted">Sign in to your account</p>
                    </div>
                    
                    <form action="/login" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-2"></i>Email Address
                            </label>
                            <input type="email" class="form-control" name="email" id="email" required 
                                   placeholder="Enter your email">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-2"></i>Password
                            </label>
                            <div class="position-relative">
                                <input type="password" class="form-control" name="password" id="password" required 
                                       placeholder="Enter your password">
                                <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent" 
                                        onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-toggle"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Sign In
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">
                            Don't have an account? 
                            <a href="/register" class="text-decoration-none">
                                <strong>Sign up here</strong>
                            </a>
                        </p>
                    </div>
                    
                    <div class="text-center mt-4 pt-4 border-top">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <i class="bi bi-shield-check text-success h4 mb-2"></i>
                                    <div class="small text-muted">Secure Login</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <i class="bi bi-lightning-fill text-warning h4 mb-2"></i>
                                    <div class="small text-muted">Instant Access</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = document.getElementById(fieldId + '-toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        toggle.className = 'bi bi-eye';
    }
}
</script>

<?php require_once 'views/layout/footer.php'; ?>