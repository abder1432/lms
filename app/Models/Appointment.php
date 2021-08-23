<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    const APPOINTMENT_STATUS_PENDING_PAYMENT = 'pending_payment';
    const APPOINTMENT_STATUS_COMPLETE = 'complete';

    protected $fillable = ['service_id', 'teacher_profile_id', 'user_id', 'note', 'status', 'notify_student', 'date'];

    protected $dates = ['date'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function teacherProfile()
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryAttribute()
    {
        return $this->service->serviceCategory;
    }

    public function getTitleAttribute()
    {
        return optional($this->service)->title;
    }

    public function getPriceAttribute()
    {
        return optional($this->service)->price;
    }

    public function getSlugAttribute()
    {
        return $this->id;
    }
}
