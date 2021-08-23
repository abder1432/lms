<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function bookAppointment()
    {
        return view('frontend.appointments.booking');
    }
}
