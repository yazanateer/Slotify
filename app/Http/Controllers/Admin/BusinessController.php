<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Businesses/Index', [
            'businesses' => Business::latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Businesses/Create');
    }

    public function store(Request $request)
    {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'slug' => ['nullable', 'string', 'max:255', 'unique:businesses,slug'],
        'phone' => ['nullable', 'string', 'max:255'],
        'email' => ['nullable', 'email', 'max:255'],
        'address' => ['nullable', 'string', 'max:255'],
        'timezone' => ['required', 'string', 'max:255'],
        'is_active' => ['boolean'],
    ]);

    $validated['slug'] = filled($validated['slug'] ?? null)
        ? Str::slug($validated['slug'])
        : Str::slug($validated['name']);

    Business::create($validated);

    return redirect()
        ->route('admin.businesses.index')
        ->with('success', 'Business created successfully.');
    }

    public function edit(Business $business)
    {
        return Inertia::render('Admin/Businesses/Edit', [
            'business' => $business,
        ]);
    }

    public function update(Request $request, Business $business)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:businesses,slug,' . $business->id],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],  
        ]);

        $business->update($validated);

        return redirect()
            ->route('admin.businesses.index')
            ->with('success', 'Business updated successfully.');
    }

    public function destroy(Business $business)
    {

         $business->delete();
         return redirect()
            ->route('admin.businesses.index')
            ->with('success', 'Business deleted successfully.');
    }


}
