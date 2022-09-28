<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiEnterprisesController extends Controller
{

    use ApiResponser;


    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        return $this->success(Enterprise::all(), $message = "Enterprise", 200);
    }

    public function create(Request $r)
    {

        $u = auth('api')->user();

        if ($r->phone_number == null) {
            return $this->error('Phone number is required.');
        }

        if ($r->name == null) {
            return $this->error('Name is required.');
        }
        $phone_number = Utils::prepare_phone_number($r->phone_number);
        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number.');
        }

        $x = Enterprise::where('name', trim($r->name))->first();
        if ($x != null) {
            return $this->error('Group with same name (' . $x->name . ') already exist. ');
        }

        $y = Enterprise::where('phone_number', $r->phone_number)->first();
        if ($y != null) {
            return $this->error('Group with same phone number already exist.');
        }

        $logo = Utils::upload_images_1($_FILES, true);

        $e = new Enterprise();
        $e->name = trim($r->name);
        $e->phone_number = trim($r->phone_number);
        $e->email = trim($r->email);
        $e->details = trim($r->details);
        $e->address = trim($r->address);
        $e->welcome_message = trim($r->welcome_message);
        $e->administrator_id = $u->id;
        $e->type = 'Pending';
        $e->logo = $logo;
        if (!$e->save()) {
            return $this->error('Failed to save group. Please try again.');
        }

        $admin = Administrator::find($u->id);
        $admin->enterprise_id = $e->id;
        $admin->save();


        return $this->success($e, "Group created successfully", 200);
    }

    public function select(Request $r)
    { 
        $u = auth('api')->user();

        if ($r->id == null) {
            return $this->error('Group is missing.');
        }

        $ent = Enterprise::find($r->id);

        if ($ent == null) {
            return $this->error('Group not found.');
        }

        $admin = Administrator::find($u->id);
        $admin->enterprise_id = $ent->id;
        $admin->save();


        return $this->success(null, "Group selected successfully", 200);
    }
}
