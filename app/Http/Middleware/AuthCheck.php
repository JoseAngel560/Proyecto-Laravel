<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Empleado;

class AuthCheck
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('auth') || !Session::get('auth')) {
            return redirect()->route('login');
        }

        $empleado = Empleado::find(Session::get('empleado_id'));
        $routeName = $request->route()->getName();

        if ($routeName === 'database.download' && (!$empleado || !$empleado->hasAccessTo('database-backup'))) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta funcionalidad.');
        }

        return $next($request);
    }
}