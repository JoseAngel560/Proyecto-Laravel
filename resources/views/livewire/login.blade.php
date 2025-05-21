<div>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Repuestos de Motos</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<div class="container">
    <div class="particles"></div>
    <div class="login-section">
        <div class="login-container">
            <div class="logo-container">
                <img src="{{ asset('Images/Recurso 28.png') }}" alt="Logotipo de Repuestos de Motos" class="logo">
            </div>
            
            @error('username') 
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @enderror
            
            @error('password') 
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @enderror
            
            @error('email') 
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @enderror
            
            @error('newPassword') 
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @enderror
            
            @error('newPasswordConfirmation') 
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @enderror

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="form-container {{ $showResetForm ? '' : 'active' }}" id="loginFormContainer">
                <header>¡Bienvenido!</header>
                <form wire:submit.prevent="login">
                    <div class="input-field">
                        <input type="text" id="username" wire:model="username" class="input" placeholder="Usuario" required autofocus>
                        <i class='bx bx-user'></i>
                    </div>
                    <div class="input-field">
                        <input type="password" id="password" wire:model="password" class="input" placeholder="Contraseña" required>
                        <i class='bx bx-lock-alt'></i>
                    </div>
                    <div class="input-field">
                        <button type="submit" class="submit">Iniciar Sesión</button>
                    </div>
                </form>
                <div class="bottom">
                    <div class="left">
                        <input type="checkbox" id="remember" wire:model="remember">
                        <label for="remember">Recordarme</label>
                    </div>
                    <div class="right">
                        <label><a href="#" wire:click.prevent="showPasswordResetForm">¿Olvidaste la contraseña?</a></label>
                    </div>
                </div>
            </div>
            
            <div class="form-container {{ $showResetForm ? 'active' : '' }}" id="passwordResetFormContainer">
                <header>{{ $resetToken ? 'Cambiar Contraseña' : 'Recuperar Contraseña' }}</header>
                
                @if($resetToken)
                    <form wire:submit.prevent="resetPassword">
                        <div class="input-field">
                            <input type="email" id="email" wire:model="email" class="input" placeholder="Correo electrónico" required readonly>
                            <i class='bx bx-envelope'></i>
                        </div>
                        <div class="input-field">
                            <input type="password" id="newPassword" wire:model="newPassword" class="input" placeholder="Nueva Contraseña" required>
                            <i class='bx bx-lock-alt'></i>
                        </div>
                        <div class="input-field">
                            <input type="password" id="newPasswordConfirmation" wire:model="newPasswordConfirmation" class="input" placeholder="Confirmar Contraseña" required>
                            <i class='bx bx-lock-alt'></i>
                        </div>
                        <div class="input-field">
                            <button type="submit" class="submit">Cambiar Contraseña</button>
                        </div>
                    </form>
                @else
                    <form wire:submit.prevent="sendResetLink">
                        <div class="input-field">
                            <input type="email" id="email" wire:model="email" class="input" placeholder="Correo electrónico" required>
                            <i class='bx bx-envelope'></i>
                        </div>
                        <div class="input-field">
                            <button type="submit" class="submit">Verificar correo</button>
                        </div>
                    </form>
                @endif
                
                <div class="bottom center">
                    <label><a href="#" wire:click.prevent="backToLogin">Volver al inicio de sesión</a></label>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('js/login-particles.js') }}"></script>
</body>
</html>
</div>