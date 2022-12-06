<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseComment extends Model
{
    use HasFactory;

    protected $fillable = ['body'];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }
}
