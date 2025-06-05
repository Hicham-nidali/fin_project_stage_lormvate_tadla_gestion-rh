<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système de Gestion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <!-- Background overlay -->
        <div class="background-overlay"></div>
        
        <!-- Login Card -->
        <div class="login-card">
            <!-- Left Side - Image Section -->
            <div class="login-image-section">
                <div class="image-overlay"></div>
                <div class="logo-container">
                    <div class="logo">
                        <i class="fas fa-seedling"></i>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form Section -->
            <div class="login-form-section">
                <div class="form-container">
                    <h2 class="welcome-title">Welcome !</h2>
                    
                    @if ($errors->any())
                        <div class="error-message">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="success-message">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="error-message">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif
                    
                    <form action="{{ route('login') }}" method="POST" class="login-form">
                        @csrf
                        <div class="input-group">
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input type="email" 
                                       class="form-input" 
                                       id="email" 
                                       name="email" 
                                       placeholder="@username" 
                                       value="{{ old('email') }}" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" 
                                       class="form-input" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Password" 
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="password-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <label class="remember-checkbox">
                                <input type="checkbox" name="remember" id="remember">
                                <span class="checkmark"></span>
                                Se souvenir de moi
                            </label>
                            <a href="#" class="forgot-password">Forgot Password ?</a>
                        </div>
                        
                        <button type="submit" class="login-button">
                            <span>LOGIN</span>
                            <div class="button-loader"></div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Floating elements for animation -->
        <div class="floating-elements">
            <div class="floating-element floating-element-1"></div>
            <div class="floating-element floating-element-2"></div>
            <div class="floating-element floating-element-3"></div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordEye = document.getElementById('password-eye');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordEye.classList.remove('fa-eye');
                passwordEye.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordEye.classList.remove('fa-eye-slash');
                passwordEye.classList.add('fa-eye');
            }
        }

        // Form submission animation
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            const button = document.querySelector('.login-button');
            const span = button.querySelector('span');
            const loader = button.querySelector('.button-loader');
            
            button.classList.add('loading');
            span.style.opacity = '0';
            loader.style.display = 'block';
        });

        // Input focus animations
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });
            
            // Check if input has value on page load
            if (input.value !== '') {
                input.parentElement.classList.add('focused');
            }
        });

        // Floating elements animation
        function createFloatingAnimation() {
            const elements = document.querySelectorAll('.floating-element');
            elements.forEach((element, index) => {
                const duration = 3000 + (index * 1000);
                element.style.animationDuration = duration + 'ms';
            });
        }

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            createFloatingAnimation();
            
            // Add entrance animation to card
            setTimeout(() => {
                document.querySelector('.login-card').classList.add('visible');
            }, 100);
        });
    </script>
</body>
</html>