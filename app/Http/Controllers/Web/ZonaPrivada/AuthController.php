<?php

namespace App\Http\Controllers\Web\ZonaPrivada;

use App\Http\Controllers\Controller;
use App\Models\PrivateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /zona-privada/login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = PrivateUser::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('ui.zona_privada.login_invalid', [], app()->getLocale())
                    ?? 'Las credenciales no son correctas.'],
            ]);
        }

        if ($user->status === 'pending') {
            throw ValidationException::withMessages([
                'email' => [__('ui.zona_privada.pending_approval',  [], app()->getLocale())
                    ?? 'Tu cuenta está pendiente de aprobación.'],
            ]);
        }

        if ($user->status === 'rejected') {
            throw ValidationException::withMessages([
                'email' => [__('ui.zona_privada.rejected',  [], app()->getLocale())
                    ?? 'Tu cuenta fue rechazada. Contactá al administrador.'],
            ]);
        }

        Auth::guard('client')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('zona-privada.lista-precios');
    }

    // POST /zona-privada/logout
    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // GET /zona-privada/registrarse
    public function showRegister()
    {
        return view('web.zona-privada.auth.register');
    }

    // POST /zona-privada/registrarse
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'lastname'              => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'unique:private_users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        PrivateUser::create([
            'name'     => $data['name'],
            'lastname' => $data['lastname'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'status'   => 'pending',
        ]);

        return redirect()->route('zona-privada.register')->with('registered', true);
    }
}
