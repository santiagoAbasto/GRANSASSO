<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $principalId = User::oldest()->value('id');

        $users = User::latest()->get()->map(fn ($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'email'        => $u->email,
            'is_principal' => $u->id === $principalId,
            'created_at'   => $u->created_at->format('d/m/Y'),
        ]);

        return Inertia::render('Admin/Users/Index', [
            'users'       => $users,
            'principalId' => $principalId,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email|max:200',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $user->id . '|max:200',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $principalId = User::oldest()->value('id');
        if ($user->id === $principalId) {
            return back()->with('error', 'No se puede eliminar el usuario principal.');
        }
        $user->delete();
        return back()->with('success', 'Usuario eliminado.');
    }
}
