<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseModel extends Model
{
    use HasFactory;
    protected $table = 'cases';

    public function images(){
        return $this->hasMany(Image::class,'parent_id');
    }

}
