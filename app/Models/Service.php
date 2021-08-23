<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = ['is_online' => 'boolean'];

    protected $guarded = [];

    public function teachers()
    {
        return $this->belongsToMany(TeacherProfile::class);
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function availabilities()
    {
        return $this->belongsToMany(Availability::class);
    }

    public function availabilitiesForUser($user_id)
    {
        return $this->availabilities()->where('user_id','=', $user_id);
    }

}
