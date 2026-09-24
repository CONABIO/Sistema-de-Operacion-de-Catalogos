<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $users = User::with(['rol', 'rol.relmodulo'])->get();
        
        return Inertia::render('Users/Index', [
            'users' => $users,

        ]);
    }

    public function eliminaUsuario(Request $request)
    {
        log::info("Estoy llegando al controlador");
        log::info($request);

        $usuario = User::find($request['id']);
        $usuario->delete();

        $usuarios = User::all();

        return $usuarios;
    }

    public function cargaUsuarios(){
        $usuarios = User::all();

        return $usuarios;
    }

    public function cargaPerfiles(){
        $roles = Rol::all();

        return $roles;
    }
   
}
