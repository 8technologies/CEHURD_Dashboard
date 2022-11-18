<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityReport;
use App\Models\CaseModel;
use App\Models\Enterprise;
use App\Models\Image;
use App\Models\Location;
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


    public function index(Request $r)
    {
        $data =  CaseModel::where([])->with('images')->get();
        return $this->success($data, 'Case submitted successfully.');
    }
    public function create_post(Request $r)
    {
        $u = auth('api')->user();
        $administrator_id = $u->id;
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if ($r->title == null) {
            return $this->error('Title is required.');
        }

        if ($r->category == null) {
            return $this->error('Category is required.');
        }

        if ($r->details == null) {
            return $this->error('Description is required.');
        }

        $c = new CaseModel();
        $c->administrator_id  = $administrator_id;
        $c->title  = $r->title;
        $c->latitude  = $r->latitude;
        $c->longitude  = $r->longitude;
        $c->description  = $r->details;
        $c->district  = 1;
        $c->status  = 0;
        $c->sub_county  = 1;
        $c->case_category_id  = 1;
        $c->response  = null;

        if ($c->save()) {

            $imgs =  Image::where([
                'administrator_id' => $administrator_id,
                'parent_id' => null
            ])->get();

            foreach ($imgs as $key => $img) {
                $img->parent_id = $c->id;
                $img->save();
            }
            return $this->success([], 'Case submitted successfully.');
        } else {
            return $this->error('Filed to submit the case.');
        }

        die($u->name);
    }



    public function create_activity(Request $r)
    {
        $u = auth('api')->user();
        $administrator_id = $u->id;
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if ($r->facilitator_title == null) {
            return $this->error('Facilitator title is required.');
        }

        $sub_county = Location::find($r->sub_county);
        if ($sub_county == null) {
            return $this->error('Sub county is required.');
        }

        //reported_by        
        //district_id        
        //online_id        
        //==>offence_category_id        
        //==>sub_county_text   

        $activity = new ActivityReport();
        $activity->facilitator_title = $r->facilitator_title;
        $activity->facilitator_name = $r->facilitator_name;
        $activity->sub_county = $sub_county->id;
        $activity->district = $sub_county->parent;
        $activity->activity_date = $r->activity_date;
        $activity->activity_venue = $r->activity_venue;
        $activity->activity_description = $r->activity_description;
        $activity->how_issues_will_be_followed_up = $r->how_issues_will_be_followed_up;
        $activity->recommendations = $r->recommendations;
        $activity->lessons_learned = $r->lessons_learned;
        $activity->challanges_solutions = $r->challanges_solutions;
        $activity->challanges_faced = $r->challanges_faced;
        $activity->issues_raised = $r->issues_raised;
        $activity->activity_duration = $r->activity_duration;
        $activity->number_of_conducted = $r->number_of_conducted;
        $activity->number_of_attended = $r->number_of_attended;
        $activity->reported_by = $u->id;
        $activity->approved_by = $u->id;
        $activity->activity_title = $u->activity_title;
        $activity->status = 'Pending';
        $activity->action_done = '';
        if ($activity->save()) {
            /*  $imgs =  Image::where([
                'administrator_id' => $administrator_id,
                'parent_id' => null
            ])->get();

            foreach ($imgs as $key => $img) {
                $img->parent_id = $c->id;
                $img->save();
            } */
            return $this->success([], 'Acivity report submitted successfully.');
        } else {
            return $this->error('Filed to submit the Acivity report.');
        }

        die($u->name);
    }



    public function upload_media(Request $request)
    {

        $u = auth('api')->user();
        $administrator_id = $u->id;
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        $images = Utils::upload_images_1($_FILES, false);
        $_images = [];
        foreach ($images as $src) {
            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  null;
            $img->parent_id =  null;
            $img->size = filesize(Utils::docs_root() . '/storage/images/' . $img->src);
            $img->save();
            $_images[] = $img;
        }
        Utils::process_images_in_backround();
        return $this->success($_images, 'File uploaded successfully.');
    }

    public function process_pending_images()
    {
        Utils::process_images_in_foreround();
        return 1;
    }
}
