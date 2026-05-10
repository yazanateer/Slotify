<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BusinessAvailability;

class AvailabilityController extends Controller
{
    public function index()
    {
        $businessId = auth()->user()->business_id;
        $existing = BusinessAvailability::where('business_id', $businessId)
            ->get()
            ->keyBy('day_of_week');
        
        $days = collect([
            ['day_of_week' => 0, 'label' => 'Sunday'],
            ['day_of_week' => 1, 'label' => 'Monday'],
            ['day_of_week' => 2, 'label' => 'Tuesday'],
            ['day_of_week' => 3, 'label' => 'Wednesday'],
            ['day_of_week' => 4, 'label' => 'Thursday'],
            ['day_of_week' => 5, 'label' => 'Friday'],
            ['day_of_week' => 6, 'label' => 'Saturday'],
        ])->map(function ($day) use ($existing) {
            $availability = $existing->get($day['day_of_week']);
            return [
                'day_of_week' => $day['day_of_week'],
                'label' => $day['label'],
                'is_active' => $availability?->is_active ?? false,
                'start_time' => $availability?->start_time ? substr($availability->start_time, 0, 5) : '09:00',
                'end_time' => $availability?->end_time ? substr($availability->end_time, 0, 5) : '17:00',
            ];
        });

        return Inertia::render('Dashboard/Availability/Index', [
            'days' => $days,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'days' => ['required', 'array', 'size:7'],
            'days.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'days.*.is_active' => ['required', 'boolean'],
            'days.*.start_time' => ['nullable', 'date_format:H:i'],
            'days.*.end_time' => ['nullable', 'date_format:H:i'],
        ]);

        $businessId = auth()->user()->business_id;
        foreach ($validated['days'] as $day) {
            BusinessAvailability::updateOrCreate(
                [
                    'business_id' => $businessId,
                    'day_of_week' => $day['day_of_week'],
                ],
                [
                    'is_active' => $day['is_active'],
                    'start_time' => $day['is_active'] ? $day['start_time'] : null,
                    'end_time' => $day['is_active'] ? $day['end_time'] : null,
                ]
            );
        }

        return redirect()
            ->route('dashboard.availability.index')
            ->with('success', 'Availability updated successfully.');
    }
}
