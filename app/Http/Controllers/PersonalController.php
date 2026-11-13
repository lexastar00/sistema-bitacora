<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalController extends Controller
{
    public function index(): View
    {
        $personal = DB::table('personal')->orderBy('nombre_completo')->get();

        return view('personal.index', [
            'personal' => $personal,
            'rolesDocumento' => DB::table('roles_documento')->orderBy('nombre_visible')->get(),
            'asignaciones' => DB::table('personal_rol')->where('activo', true)->get()->groupBy('personal_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:200'],
            'puesto_actual' => ['nullable', 'string', 'max:200'],
        ]);

        $id = DB::table('personal')->insertGetId([
            'nombre_completo' => $datos['nombre_completo'],
            'puesto_actual' => $datos['puesto_actual'] ?? null,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('activity_log')->insert([
            'log_name' => 'catalogos',
            'description' => 'Personal agregado: '.$datos['nombre_completo'],
            'subject_type' => 'personal',
            'event' => 'created',
            'subject_id' => $id,
            'causer_type' => User::class,
            'causer_id' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('personal.index')->with('status', 'Personal agregado correctamente.');
    }

    public function roles(Request $request, int $personal): RedirectResponse
    {
        abort_unless(DB::table('personal')->where('id', $personal)->exists(), 404);
        $datos = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists('roles_documento', 'id')],
        ]);

        DB::transaction(function () use ($personal, $datos): void {
            DB::table('personal_rol')->where('personal_id', $personal)->update(['activo' => false]);
            foreach (array_unique($datos['roles'] ?? []) as $rolId) {
                DB::table('personal_rol')->updateOrInsert(
                    ['personal_id' => $personal, 'rol_id' => $rolId],
                    ['activo' => true]
                );
            }
        });
        DB::table('activity_log')->insert([
            'log_name' => 'catalogos',
            'description' => 'Cargos de firma de personal actualizados',
            'subject_type' => 'personal',
            'event' => 'updated',
            'subject_id' => $personal,
            'causer_type' => User::class,
            'causer_id' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('personal.index')->with('status', 'Cargos de firma actualizados.');
    }
}
