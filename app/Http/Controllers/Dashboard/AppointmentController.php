<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index()
    {
        $businessId = auth()->user()->business_id;

        return Inertia::render('Dashboard/Appointments/Index', [
            'appointments' => Appointment::with('service')
                ->where('business_id', $businessId)
                ->latest('appointment_date')
                ->get(),
        ]);
    }
}
