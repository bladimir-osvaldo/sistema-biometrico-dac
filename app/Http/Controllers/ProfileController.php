<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Attendance;
use App\Models\AuditLog;

/**
 * Controlador para la Gestión del Perfil del Usuario Autenticado.
 */
class ProfileController extends Controller
{
    /**
     * Muestra la página del perfil propio del usuario en sesión.
     */
    public function show()
    {
        $user = auth()->user();

        // Estadísticas personales del período actual
        $mesActual = date('Y-m');
        $totalAsistencias = Attendance::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$mesActual])->count();
        $puntuales = Attendance::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$mesActual])
            ->where('estado', 'PUNTUAL')->count();
        $tardanzas = Attendance::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$mesActual])
            ->where('estado', 'TARDANZA')->count();
        $porcentajePuntualidad = $totalAsistencias > 0
            ? round(($puntuales / $totalAsistencias) * 100, 1)
            : 0;

        return view('profile.show', compact(
            'user',
            'totalAsistencias',
            'puntuales',
            'tardanzas',
            'porcentajePuntualidad'
        ));
    }

    /**
     * Muestra el formulario de edición del perfil propio.
     */
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Actualiza la información del perfil del usuario en sesión.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users,email,' . $user->id,
            'telefono'    => 'nullable|string|max:20',
            'password'    => 'nullable|string|min:8|confirmed',
            'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'      => 'El nombre completo es obligatorio.',
            'email.unique'       => 'Este correo electrónico ya está registrado por otro usuario.',
            'password.min'       => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'foto_perfil.max'    => 'La imagen no puede superar los 2 MB.',
        ]);

        // Subida y almacenamiento de foto de perfil en storage/app/public/avatars
        if ($request->hasFile('foto_perfil')) {
            if ($user->foto_perfil) {
                Storage::disk('public')->delete($user->foto_perfil);
            }
            $fotoPath = $request->file('foto_perfil')->store('avatars', 'public');
            $user->foto_perfil = $fotoPath;
        }

        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->telefono = $request->telefono;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        AuditLog::create([
            'user_id'    => $user->id,
            'accion'     => 'ACTUALIZAR_PERFIL_PROPIO',
            'modulo'     => 'Perfil',
            'detalles'   => "El usuario {$user->email} actualizó sus datos de perfil.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('profile.edit')->with('success', '✅ Perfil actualizado correctamente.');
    }
}
