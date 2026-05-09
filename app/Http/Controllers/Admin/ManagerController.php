<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ManagerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Managers/Index', [
            'managers' => User::with('business')
                ->where('role', 'manager')
                ->latest()
                ->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Managers/Create', [
            'businesses' => Business::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request) 
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'business_id' => ['required', 'exists:businesses,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'manager',
            'business_id' => $validated['business_id']
        ]);

        return redirect()
            ->route('admin.managers.index')
            ->with('success', 'Manager created successfully.');
    }

    public function edit(User $manager)
    {
        abort_if($manager->role != 'manager', 404);
        return Inertia::render('Admin/Managers/Edit', [
            'manager' => $manager,
            'businesses' => Business::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
        ]);
    }

    public function update(Request $request, User $manager)
    {
        abort_if($manager->role !== 'manager', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $manager->id],
            'password' => ['nullable', 'string', 'min:8'],
            'business_id' => ['required', 'exists:businesses,id'],
        ]);

        $manager->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'business_id' => $validated['business_id'],
            'password' => filled($validated['password'] ?? null)
                ? Hash::make($validated['password'])
                : $manager->password,
        ]);

        return redirect()
            ->route('admin.managers.index')
            ->with('success', 'Manager updated successfully.');
    } 

    public function destroy(User $manager)
    {
        abort_if($manager->role !== 'manager', 404);

        $manager->delete();

        return redirect()
            ->route('admin.managers.index')
            ->with('success', 'Manager deleted successfully.');
    }
}
