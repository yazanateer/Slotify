<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use Inertia\Inertia;
use App\Models\Appointment;
use Carbon\Carbon;

class BookingController extends Controller
{
  public function show(Business $business)
    {
        return Inertia::render('Booking/Show', [
            'business' => $business,
            'services' => $business->services()
                ->where('is_active', true)
                ->get(),
        ]);
    }

    public function slots(Request $request, Business $business)
    {
    $validated = $request->validate([
        'service_id' => ['required', 'exists:services,id'],
        'date' => ['required', 'date'],
    ]);

    $service = $business->services()
        ->where('id', $validated['service_id'])
        ->where('is_active', true)
        ->firstOrFail();

    $date = Carbon::parse($validated['date']);
    $dayOfWeek = $date->dayOfWeek; // 0 Sunday - 6 Saturday

    $availability = $business->availabilities()
        ->where('day_of_week', $dayOfWeek)
        ->where('is_active', true)
        ->first();

    if (! $availability) {
        return response()->json([
            'slots' => [],
        ]);
    }

    $existingAppointments = Appointment::where('business_id', $business->id)
        ->where('appointment_date', $date->toDateString())
        ->whereIn('status', ['pending', 'confirmed'])
        ->get(['start_time', 'end_time']);

    $slots = [];

    $start = Carbon::parse($date->toDateString() . ' ' . $availability->start_time);
    $end = Carbon::parse($date->toDateString() . ' ' . $availability->end_time);

    while ($start->copy()->addMinutes($service->duration_minutes)->lte($end)) {
        $slotStart = $start->copy();
        $slotEnd = $start->copy()->addMinutes($service->duration_minutes);

        $hasConflict = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd, $date) {
            $appointmentStart = Carbon::parse($date->toDateString() . ' ' . $appointment->start_time);
            $appointmentEnd = Carbon::parse($date->toDateString() . ' ' . $appointment->end_time);

            return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
        });

        if (! $hasConflict) {
            $slots[] = [
                'start_time' => $slotStart->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'label' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
            ];
        }

        $start->addMinutes(15);
    }

    return response()->json([
        'slots' => $slots,
    ]);
    }
}
