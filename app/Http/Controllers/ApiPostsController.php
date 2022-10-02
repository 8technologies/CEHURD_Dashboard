<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiPostsController extends Controller
{

    use ApiResponser;
 
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function upload_media()
    {
        return $this->success('SUCCESS', $message = "uPLOAIFN MEDIAL", 200);
    }
   
}
