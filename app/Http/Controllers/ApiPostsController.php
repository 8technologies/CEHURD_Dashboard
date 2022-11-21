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


    public function categories(Request $r)
    {
        $cats = [];
        $id = 0;
        foreach (Utils::case_categpries() as $key => $v) {
            $id++;
            $d['id'] = $id;
            $d['name'] = $v;
            $d['short'] = $v;
            $d['url'] = Utils::get_category_pic($v);
            $d['count'] = CaseModel::where('case_category', $v)->count();


            $cats[] = $d;
        }
        return $this->success($cats, 'Case submitted successfully.');
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

        if ($r->case_category == null) {
            return $this->error('Category is required.');
        }

        if ($r->description == null) {
            return $this->error('Description is required.');
        }
        if ($r->sub_county == null) {
            return $this->error('Sub-county is required.');
        }
        if ($r->title == null) {
            return $this->error('Title is required.');
        }

        $c = new CaseModel();
        $c->sub_county  = $r->sub_county;
        $c->administrator_id  = $administrator_id;
        $c->title  = $r->title;
        $c->description  = $r->description;
        $c->response  = $r->response;
        $c->latitude  = $r->latitude;
        $c->longitude  = $r->longitude;
        $c->is_court  = $r->is_court;
        $c->phone_number_2  = $r->phone_number_2;
        $c->phone_number_1  = $r->phone_number_1;
        $c->is_authority  = ($r->is_authority == 'Yes') ? 1 : 0;
        $c->address  = $r->address;
        $c->village  = $r->village;
        $c->sex  = $r->sex;
        $c->applicant_name  = $r->applicant_name;
        $c->complaint_method  = $r->complaint_method;
        $c->case_category  = $r->case_category;
        $c->request  = $r->request_data;
        $c->save();
        return $this->success($c, 'Case submitted successfully.');


 
/* 
sub_county_text:Adjumani, Adropi
:Romina K.



=============================================================================

id	
created_at	
updated_at	
administrator_id	
	
district	
sub_county	
title	
description	
response	
status	
latitude	
longitude	
is_court	
is_authority	
request	
phone_number_2	
phone_number_1	
address	
village	
sex	
applicant_name	
complaint_method	
	
*/
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
