<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DarkModeController extends Controller
{
    public function toggle(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'darkMode' => 'required|boolean',
        ]);
        
        // Guardar la preferencia en la sesión
        session(['darkMode' => $request->darkMode]);
        
        // Devolver una respuesta exitosa
        return response()->json(['success' => true]);
    }
}