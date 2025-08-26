<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfluBoss - Connexion</title>
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        .login-container {
            animation: fadeIn 0.8s ease-in-out;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        }
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .input-group input {
            padding: 0.75rem 1rem 0.75rem 3rem;
            transition: all 0.3s ease;
        }
        .input-group input:focus {
            transform: translateY(-2px);
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #ef6110;
        }
        .btn-login {
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(220, 38, 38, 0.3);
        }
        .brand-logo {
            height: 60px;
            margin-bottom: 1rem;
        }
        .brand-name {
            background: linear-gradient(90deg, #ef6110,rgb(231, 114, 46));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: 700;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-control-animated label {
            position: absolute;
            top: 0.75rem;
            left: 3rem;
            transition: all 0.2s ease;
            pointer-events: none;
            color: #9ca3af;
        }
        .form-control-animated input:focus ~ label,
        .form-control-animated input:not(:placeholder-shown) ~ label {
            top: -0.5rem;
            left: 0.75rem;
            font-size: 0.75rem;
            color: #ef6110;
            background-color: white;
            padding: 0 0.25rem;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="login-container w-full max-w-md">
        <div class="login-card p-8 rounded-2xl">
            <div class="text-center mb-8">
                <div class="flex justify-center">
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="InfluBoss Logo" class="brand-logo" onerror="this.src='https://via.placeholder.com/150x60?text=InfluBoss'; this.onerror=null;"> -->
                </div>
                <h1 class="text-4xl brand-name mb-2">InfluBoss</h1>
                <p class="text-gray-600 text-sm">Plateforme de gestion d'Ambassadeurs</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email -->
                <div class="input-group form-control-animated">
                    <span class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email" id="email" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 @error('email') border-red-500 @enderror"
                           value="{{ old('email') }}" required placeholder=" ">
                    <label for="email">Email</label>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="input-group form-control-animated">
                    <span class="input-icon">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" id="password" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 @error('password') border-red-500 @enderror"
                           required placeholder=" ">
                    <label for="password">Mot de passe</label>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" 
                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 text-sm text-gray-700">Se souvenir de moi</label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="btn-login w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i> Se connecter
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200 text-center text-sm text-gray-600">
                <p>© {{ date('Y') }} McCCANN. Tous droits réservés.</p>
            </div>
        </div>
    </div>

    <script>
        // Animation des champs de formulaire
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                // Vérifier si le champ a déjà une valeur au chargement
                if (input.value) {
                    input.classList.add('has-value');
                }
                
                // Ajouter des écouteurs d'événements
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                    if (this.value) {
                        this.classList.add('has-value');
                    } else {
                        this.classList.remove('has-value');
                    }
                });
            });
        });
    </script>
</body>
</html>
