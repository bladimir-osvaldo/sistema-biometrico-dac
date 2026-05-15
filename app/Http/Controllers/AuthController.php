<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

/**
 * Controlador de Autenticación del Sistema Biométrico DAC.
 * Gestiona el inicio de sesión web y la autenticación de usuarios.
 */
class AuthController extends Controller
{
    /**
     * Muestra la vista del formulario de login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Procesa el inicio de sesión del usuario.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // El sistema no usa la opción "Recordar sesión" (checkbox eliminado por seguridad).
        if (Auth::attempt($credentials, false)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Auditoría de acceso
            AuditLog::create([
                'user_id' => $user->id,
                'accion' => 'LOGIN_EXITOSO',
                'modulo' => 'Autenticación',
                'detalles' => "Inicio de sesión web para el usuario {$user->email}",
                'ip_address' => $request->ip(),
            ]);

            return redirect()->intended(route('dashboard'))
                ->with('success', "¡Bienvenido de nuevo, {$user->name}!");
        }

        AuditLog::create([
            'accion' => 'LOGIN_FALLIDO',
            'modulo' => 'Autenticación',
            'detalles' => "Intento fallido con correo: {$request->email}",
            'ip_address' => $request->ip(),
        ]);

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cierra la sesión activa del usuario.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'accion' => 'LOGOUT',
                'modulo' => 'Autenticación',
                'detalles' => "Cierre de sesión de {$user->email}",
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Sesión cerrada correctamente.');
    }
}
