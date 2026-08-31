<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of users and role assignments.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $role = $request->input('role');

        $users = User::query()
            ->with(['integrityPact', 'dataFinalization'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $totalUsers = User::count();
        $totalSuperAdmins = User::where('role', 'super_admin')->count();
        $totalAnggota = User::where('role', 'anggota')->count();

        return Inertia::render('UserManagement/Index', [
            'users' => $users,
            'statistics' => [
                'total_users' => $totalUsers,
                'total_super_admins' => $totalSuperAdmins,
                'total_anggota' => $totalAnggota,
            ],
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'in:super_admin,anggota'],
            'nip' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'role.required' => 'Role pengguna wajib dipilih.',
        ]);

        try {
            $validated['password'] = Hash::make($validated['password']);
            $user = User::create($validated);

            return redirect()->back()->with('success', "Pengguna '{$user->name}' dengan role {$user->role} berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan pengguna. Terjadi kesalahan server.');
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', 'in:super_admin,anggota'],
            'nip' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'role.required' => 'Role pengguna wajib dipilih.',
        ]);

        try {
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            return redirect()->back()->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data pengguna.');
        }
    }

    /**
     * Assign / change user role directly.
     */
    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:super_admin,anggota'],
        ]);

        try {
            // Prevent removing own super_admin role if you are the current user
            if ($user->id === auth()->id() && $validated['role'] !== 'super_admin') {
                return redirect()->back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri menjadi bukan Super Admin.');
            }

            $user->update(['role' => $validated['role']]);

            return redirect()->back()->with('success', "Role pengguna '{$user->name}' berhasil diubah menjadi {$validated['role']}.");
        } catch (\Exception $e) {
            Log::error('Error assigning role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengubah role pengguna.');
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        try {
            $name = $user->name;
            $user->delete();
            return redirect()->back()->with('success', "Pengguna '{$name}' berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus pengguna.');
        }
    }
}
