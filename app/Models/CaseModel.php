<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseModel extends Model
{
    use HasFactory;
    protected $table = 'cases';
    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        //return url('assets/img/health-plus.png');
        return Utils::get_category_pic($this->case_category);
    }
    public function images()
    {
        return $this->hasMany(Image::class, 'parent_id');
    }


    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {


            $m->phone_number_1 = Utils::prepare_phone_number($m->phone_number_1);
            $m->phone_number_2 = Utils::prepare_phone_number($m->phone_number_2);

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
