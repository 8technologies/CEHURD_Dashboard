<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityReport extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();

        self::updating(function ($m) {
            if (isset($m->activity_start_date) && isset($m->activity_start_end)) {
                $start = Carbon::parse($m->activity_start_date);
                $end = Carbon::parse($m->activity_start_end);
                $m->activity_duration = $start->diff($end)->format('%H:%I:%S');
            }
        });
        self::creating(function ($m) {


            if (isset($m->activity_start_date) && isset($m->activity_start_end)) {
                $start = Carbon::parse($m->activity_start_date);
                $end = Carbon::parse($m->activity_start_end);
                $m->activity_duration = $start->diff($end)->format('%H:%I:%S');
            }


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

    public function images()
    {
        return $this->hasMany(Image::class, 'parent_id');
    }
}
