<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;

/**
 * Controlador de Gestión de Usuarios y Perfiles Docentes.
 * Maneja el CRUD completo de docentes y usuarios del sistema DAC con DB::transaction.
 */
class UserController extends Controller
{
    /**
     * Muestra la lista paginada de todos los usuarios con sus roles (Eager Loading).
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')->latest()->paginate(15);
        $roles = Role::all();
        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Muestra el formulario de creación de un nuevo usuario/docente.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Guarda un nuevo usuario en la base de datos dentro de una transacción.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|string|email|max:255|unique:users',
            'password'       => 'required|string|min:8|confirmed',
            'role'           => 'required|string',
            'dni'            => 'nullable|string|max:20|unique:users',
            'codigo_docente' => 'nullable|string|max:20|unique:users',
            'telefono'       => 'nullable|string|max:20',
            'carrera'        => 'nullable|string|max:255',
            'cargo'          => 'nullable|string|max:100',
            'estado'         => 'nullable|in:ACTIVO,INACTIVO,LICENCIA',
            'foto_perfil'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'      => 'El nombre completo es obligatorio.',
            'email.unique'       => 'Este correo ya está registrado en el sistema.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'dni.unique'         => 'Este CI/DNI ya está registrado.',
            'codigo_docente.unique' => 'Este código de docente ya existe.',
            'foto_perfil.max'    => 'La foto no puede superar los 2 MB.',
        ]);

        $user = DB::transaction(function () use ($request) {
            $fotoPath = null;
            if ($request->hasFile('foto_perfil')) {
                $fotoPath = $request->file('foto_perfil')->store('fotos/docentes', 'public');
            }

            $u = User::create([
                'name'           => $request->name,
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'role'           => $request->role,
                'dni'            => $request->dni,
                'codigo_docente' => $request->codigo_docente,
                'telefono'       => $request->telefono,
                'carrera'        => $request->carrera,
                'cargo'          => $request->cargo ?? 'Docente',
                'estado'         => $request->estado ?? 'ACTIVO',
                'foto_perfil'    => $fotoPath,
            ]);

            // Sincronizar también rol de Spatie si existe en DB
            if (Role::where('name', $request->role)->exists()) {
                $u->assignRole($request->role);
            }

            AuditLog::create([
                'user_id'    => auth()->id(),
                'accion'     => 'CREAR_USUARIO',
                'modulo'     => 'Usuarios',
                'detalles'   => "Usuario {$u->email} registrado con rol {$request->role} por " . auth()->user()->name,
                'ip_address' => $request->ip(),
            ]);

            return $u;
        });

        return redirect()->route('users.index')
            ->with('success', "✅ Usuario «{$user->name}» registrado correctamente en el sistema.");
    }

    /**
     * Muestra el perfil detallado de un usuario/docente con asistencias y estadísticas por mes.
     */
    public function show(Request $request, User $user)
    {
        $this->authorize('view', $user);

        $mes = $request->get('mes', date('Y-m'));
        
        $user->load(['roles', 'schedules', 'huellas']);

        $attendancesQuery = $user->attendances()->with(['schedule', 'device']);
        if ($mes) {
            $attendancesQuery->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$mes]);
        }
        $attendances = $attendancesQuery->latest('fecha')->paginate(15);

        // Estadísticas del período
        $monthQuery = $user->attendances();
        if ($mes) {
            $monthQuery->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$mes]);
        }
        $totalAsistencias = (clone $monthQuery)->count();
        $puntuales        = (clone $monthQuery)->where('estado', 'PUNTUAL')->count();
        $tardanzas        = (clone $monthQuery)->where('estado', 'TARDANZA')->count();
        $faltas           = (clone $monthQuery)->whereIn('estado', ['FALTA_INJUSTIFICADA', 'FALTA_JUSTIFICADA'])->count();
        $justificadas     = (clone $monthQuery)->where('estado', 'FALTA_JUSTIFICADA')->count();
        $puntualidadPct   = $totalAsistencias > 0
            ? round(($puntuales / $totalAsistencias) * 100, 1)
            : 0;

        return view('users.show', compact(
            'user',
            'attendances',
            'mes',
            'totalAsistencias',
            'puntuales',
            'tardanzas',
            'faltas',
            'justificadas',
            'puntualidadPct'
        ));
    }

    /**
     * Muestra el formulario de edición de un usuario existente.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Actualiza los datos de un usuario existente dentro de una transacción.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'       => 'nullable|string|min:8|confirmed',
            'role'           => 'required|string',
            'dni'            => 'nullable|string|max:20|unique:users,dni,' . $user->id,
            'codigo_docente' => 'nullable|string|max:20|unique:users,codigo_docente,' . $user->id,
            'telefono'       => 'nullable|string|max:20',
            'carrera'        => 'nullable|string|max:255',
            'cargo'          => 'nullable|string|max:100',
            'estado'         => 'nullable|in:ACTIVO,INACTIVO,LICENCIA',
            'foto_perfil'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        DB::transaction(function () use ($request, $user) {
            if ($request->hasFile('foto_perfil')) {
                if ($user->foto_perfil) {
                    Storage::disk('public')->delete($user->foto_perfil);
                }
                $user->foto_perfil = $request->file('foto_perfil')->store('fotos/docentes', 'public');
            }

            $user->name           = $request->name;
            $user->email          = $request->email;
            $user->role           = $request->role;
            $user->dni            = $request->dni;
            $user->codigo_docente = $request->codigo_docente;
            $user->telefono       = $request->telefono;
            $user->carrera        = $request->carrera;
            $user->cargo          = $request->cargo;
            $user->estado         = $request->estado ?? 'ACTIVO';

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            if (Role::where('name', $request->role)->exists()) {
                $user->syncRoles([$request->role]);
            }

            AuditLog::create([
                'user_id'    => auth()->id(),
                'accion'     => 'ACTUALIZAR_USUARIO',
                'modulo'     => 'Usuarios',
                'detalles'   => "Perfil de {$user->email} actualizado por " . auth()->user()->name,
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('users.index')
            ->with('success', "✅ Usuario «{$user->name}» actualizado correctamente.");
    }

    /**
     * Elimina un usuario del sistema (con protección de autoeliminación y DB::transaction).
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', '⚠️ No puedes eliminar tu propio usuario mientras tienes la sesión activa.');
        }

        $emailEliminado = $user->email;
        $nombreEliminado = $user->name;

        DB::transaction(function () use ($user, $emailEliminado) {
            if ($user->foto_perfil) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            AuditLog::create([
                'user_id'    => auth()->id(),
                'accion'     => 'ELIMINAR_USUARIO',
                'modulo'     => 'Usuarios',
                'detalles'   => "Usuario {$emailEliminado} eliminado por " . auth()->user()->name,
                'ip_address' => request()->ip(),
            ]);

            $user->delete();
        });

        return redirect()->route('users.index')
            ->with('success', "🗑️ Usuario «{$nombreEliminado}» eliminado correctamente del sistema.");
    }
}
