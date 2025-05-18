<?php

namespace App\Livewire;

use App\Models\Empleado;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class Login extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;
    public $showResetForm = false;
    public $email = '';
    public $newPassword = '';
    public $newPasswordConfirmation = '';
    public $resetToken = null;
    
    protected $messages = [
        'username.required' => 'El nombre de usuario es obligatorio',
        'password.required' => 'La contraseña es obligatoria',
        'email.required' => 'El correo electrónico es obligatorio',
        'email.email' => 'Ingrese un correo electrónico válido',
        'newPassword.required' => 'La nueva contraseña es obligatoria',
        'newPassword.min' => 'La contraseña debe tener al menos 6 caracteres',
        'newPasswordConfirmation.same' => 'Las contraseñas no coinciden'
    ];
    
    // Inicializa el componente y carga datos de cookies si existen
    public function mount($token = null, $email = null)
    {
        if ($token && $email) {
            $this->showResetForm = true;
            $this->resetToken = $token;
            $this->email = $email;
        }
        
        if (Cookie::has('remember_user') && Cookie::has('remember_pass')) {
            $this->username = Cookie::get('remember_user');
            $this->password = Cookie::get('remember_pass');
            $this->remember = true;
        }
    }
    
    // Autentica al usuario y establece la sesión
    public function login()
    {
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        $empleado = Empleado::activo()->where('usuario', $this->username)->first();
        
        if ($empleado && Hash::check($this->password, $empleado->contraseña)) {
            Session::put('empleado_id', $empleado->id);
            Session::put('empleado_nombre', $empleado->nombre . ' ' . $empleado->apellido);
            Session::put('empleado_cargo', $empleado->cargo);
            Session::put('empleado_usuario', $empleado->usuario);
            Session::put('auth', true);
            
            if ($this->remember) {
                Cookie::queue('remember_user', $this->username, 43200); 
                Cookie::queue('remember_pass', $this->password, 43200); 
            } else {
                Cookie::queue(Cookie::forget('remember_user'));
                Cookie::queue(Cookie::forget('remember_pass'));
            }
            
            return redirect()->route('dashboard');
        }
        
        session()->flash('error', 'Las credenciales proporcionadas no coinciden.');
    }
    
    // Muestra el formulario de restablecimiento de contraseña
    public function showPasswordResetForm()
    {
        $this->showResetForm = true;
    }
    
    // Regresa al formulario de login
    public function backToLogin()
    {
        $this->showResetForm = false;
        $this->resetValidation();
    }
    
    // Verifica el correo para restablecer la contraseña
    public function sendResetLink()
    {
        $this->validate([
            'email' => 'required|email',
        ]);
        
        $empleado = Empleado::activo()->where('email', $this->email)->first();
        
        if (!$empleado) {
            session()->flash('error', 'No encontramos un usuario activo con ese correo electrónico.');
            return;
        }
        
        $this->resetToken = 'direct-reset'; 
        session()->flash('success', 'Correo verificado. Por favor, establezca su nueva contraseña.');
    }
    
    // Restablece la contraseña del usuario
    public function resetPassword()
    {
        $this->validate([
            'email' => 'required|email',
            'newPassword' => 'required|min:6',
            'newPasswordConfirmation' => 'required|same:newPassword',
        ]);
        
        $empleado = Empleado::activo()->where('email', $this->email)->first();
        
        if (!$empleado) {
            session()->flash('error', 'No encontramos un usuario activo con ese correo electrónico.');
            return;
        }
        
        $empleado->update([
            'contraseña' => Hash::make($this->newPassword)
        ]);
        
        session()->flash('success', 'Tu contraseña ha sido restablecida correctamente.');
        $this->showResetForm = false;
        $this->resetForm();
    }
    
    // Resetea los campos del formulario
    private function resetForm()
    {
        $this->username = '';
        $this->password = '';
        $this->email = '';
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
        $this->resetToken = null;
    }
    
    // Renderiza la vista del componente
    public function render()
    {
        return view('livewire.login')
            ->layout('layouts.guest');
    }
}