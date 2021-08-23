<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        Static::deleted(function($category){
            if($category->services->count()){
                $category->services()->delete();
            }
        });
    }


}
