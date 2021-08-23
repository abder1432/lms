<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    public function formattedStartTime()
    {
        return str_pad($this->start_hour,2,'0', STR_PAD_LEFT)
            . ':'
            . str_pad($this->start_minute,2,'0', STR_PAD_LEFT);
    }

    public function formattedEndTime()
    {
        return str_pad($this->end_hour,2,'0', STR_PAD_LEFT)
            . ':'
            . str_pad($this->end_minute,2,'0', STR_PAD_LEFT);
    }
}
