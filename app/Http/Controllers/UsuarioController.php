<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index(): View
    {
        return view('usuarios.index', [
            'usuarios' => User::with('roles')->orderBy('name')->paginate(20),
            'roles' => Role::query()->orderBy('name')->get(),
            'personal' => DB::table('personal')->where('activo', true)->orderBy('nombre_completo')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'personal_id' => ['nullable', 'integer', Rule::exists('personal', 'id')->where('activo', true), Rule::unique('users', 'personal_id')],
        ]);

        $usuario = DB::transaction(function () use ($datos): User {
            $usuario = User::create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'password' => Hash::make($datos['password']),
                'personal_id' => $datos['personal_id'] ?? null,
                'activo' => true,
            ]);
            $usuario->assignRole($datos['role']);
            $this->auditar('Usuario creado', $usuario->id, auth()->id());

            return $usuario;
        });

        return redirect()->route('usuarios.index')->with('status', "Cuenta {$usuario->email} creada.");
    }

    public function update(Request $request, int $usuario): RedirectResponse
    {
        $cuenta = User::with('roles')->findOrFail($usuario);
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($cuenta->id)],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'personal_id' => ['nullable', 'integer', Rule::exists('personal', 'id')->where('activo', true), Rule::unique('users', 'personal_id')->ignore($cuenta->id)],
            'activo' => ['required', 'boolean'],
        ]);
        if ($cuenta->is(auth()->user()) && ! $datos['activo']) {
            return back()->withErrors(['activo' => 'No puedes desactivar tu propia cuenta.']);
        }

        DB::transaction(function () use ($cuenta, $datos): void {
            $cuenta->fill([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'personal_id' => $datos['personal_id'] ?? null,
                'activo' => $datos['activo'],
            ]);
            if (! empty($datos['password'])) {
                $cuenta->password = Hash::make($datos['password']);
            }
            $cuenta->save();
            $cuenta->syncRoles($datos['role']);
            $this->auditar('Usuario actualizado', $cuenta->id, auth()->id());
        });

        return redirect()->route('usuarios.index')->with('status', 'Cuenta actualizada.');
    }

    private function auditar(string $descripcion, int $id, int $actor): void
    {
        DB::table('activity_log')->insert([
            'log_name' => 'seguridad',
            'description' => $descripcion,
            'subject_type' => User::class,
            'event' => 'updated',
            'subject_id' => $id,
            'causer_type' => User::class,
            'causer_id' => $actor,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
