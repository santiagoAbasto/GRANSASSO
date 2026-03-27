<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PrivateUsersController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = PrivateUser::query()->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $users = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/PrivateZone/Users', [
            'users'  => $users,
            'status' => $status,
        ]);
    }

    public function approve(PrivateUser $privateUser)
    {
        $privateUser->update(['status' => 'approved']);

        return back()->with('success', 'Usuario aprobado.');
    }

    public function reject(PrivateUser $privateUser)
    {
        $privateUser->update(['status' => 'rejected']);

        return back()->with('success', 'Usuario rechazado.');
    }

    public function updateDiscount(Request $request, PrivateUser $privateUser)
    {
        $data = $request->validate([
            'discount_pct'       => ['required', 'integer', 'min:0', 'max:100'],
            'discount_extra_pct' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $privateUser->update($data);

        return back()->with('success', 'Descuento actualizado.');
    }

    public function resetPassword(Request $request, PrivateUser $privateUser)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $privateUser->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Contraseña actualizada.');
    }

    public function destroy(PrivateUser $privateUser)
    {
        $privateUser->delete();

        return back()->with('success', 'Usuario eliminado.');
    }
}
