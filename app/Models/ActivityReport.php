<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityReport extends Model
{
    use HasFactory; 


    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {


            $m->number_of_conducted = Utils::prepare_phone_number($m->number_of_conducted);

            $m->district = 1;
            if ($m->sub_county != null) {
                $sub = Location::find($m->sub_county);
                if ($sub != null) {
                    $m->district = $sub->parent;
                }
            }
        });

        self::created(function ($model) {
            //created
        });

        self::updating(function ($m) {
            $m->district = 1;
            if ($m->sub_county != null) {
                $sub = Location::find($m->sub_county);
                if ($sub != null) {
                    $m->district = $sub->parent;
                }
            }
            return $m;
        });

        self::updated(function ($model) {
            // ... code here
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }

}
