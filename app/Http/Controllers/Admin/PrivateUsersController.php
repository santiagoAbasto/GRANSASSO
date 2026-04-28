<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PrivateUsersController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('q', ''));

        $baseQuery = PrivateUser::query()
            ->withMax('loginEvents', 'logged_in_at')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $term = '%' . $search . '%';
                    $inner->where('name', 'like', $term)
                        ->orWhere('lastname', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            });

        $stats = [
            'all'      => (clone $baseQuery)->count(),
            'pending'  => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $query = (clone $baseQuery)->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $users = $query
            ->paginate(20)
            ->withQueryString()
            ->through(function (PrivateUser $user) {
                $lastLoginAt = $user->login_events_max_logged_in_at
                    ? Carbon::parse($user->login_events_max_logged_in_at)
                    : null;

                return [
                    'id'                 => $user->id,
                    'name'               => $user->name,
                    'lastname'           => $user->lastname,
                    'full_name'          => trim($user->name . ' ' . $user->lastname),
                    'email'              => $user->email,
                    'status'             => $user->status,
                    'discount_pct'       => (int) $user->discount_pct,
                    'discount_extra_pct' => (int) $user->discount_extra_pct,
                    'created_at'         => $user->created_at?->toISOString(),
                    'created_at_label'   => $user->created_at?->format('d/m/Y'),
                    'last_login_at'      => $lastLoginAt?->toISOString(),
                    'last_login_label'   => $lastLoginAt?->format('d/m/Y H:i'),
                ];
            });

        return Inertia::render('Admin/PrivateZone/Users', [
            'users'  => $users,
            'status' => $status,
            'q'      => $search,
            'stats'  => $stats,
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
