<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function __invoke(): View
    {
        $eventos = DB::table('activity_log')
            ->leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
            ->select('activity_log.*', 'users.name as usuario')
            ->latest('activity_log.created_at')
            ->paginate(50);

        return view('auditoria.index', compact('eventos'));
    }
}
