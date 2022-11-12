<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Model;
use Faker\Factory as Faker;

class MyFaker  extends Model
{

    public static function make_cases($max = 20)
    {

        ini_set('memory_limit', '-1');
        set_time_limit('-1');

        $admins = [];
        $f = Faker::create();
        $statuses = Utils::case_statuses();
       
        foreach (Administrator::all() as $key => $u) {
            $admins[] = $u->id;
        }
        $sub_counties = [];

        foreach (Location::get_sub_counties() as $v) {
            $sub_counties[] = $v->id;
        }


        $cats = Utils::case_categpries();
        $statuses = Utils::case_statuses();
        $bools = [0, 1];
        $sex = ['Male', 'Female'];
        for ($i = 0; $i < $max; $i++) {
            $c = new CaseModel();
            shuffle($admins);
            shuffle($cats);
            shuffle($sub_counties);
            shuffle($statuses);
            shuffle($bools);
            shuffle($sex);
            shuffle($bools);

            $c->created_at = $f->dateTimeBetween('-1 year');
            $c->administrator_id = $admins[3];
            $c->case_category = $cats[2];
            $c->sub_county = $sub_counties[4];
            $c->status = $statuses[2];
            $c->title = $f->sentence();
            $c->description = $f->sentence(500);
            $c->response = $f->sentence(300);
            $c->phone_number_1 = $f->phoneNumber();
            $c->phone_number_2 = $f->phoneNumber();
            $c->village = $f->word(); 
            $c->request = $f->sentence(200);
            $c->address = $f->sentence(45);
            $c->applicant_name = $f->name();
            $c->latitude = '0.174917';
            $c->longitude = '30.077517';
            $c->is_court = $bools[1];
            $c->sex = $sex[1];
            $c->is_authority = $bools[1];
            $c->save(); 
        }

        dd(":=-=:");
    }
    public static function make_users($max = 20)
    {

        $f = Faker::create();

        /*         foreach (Administrator::all() as $key => $u) {
            $u->phone_number_1 =   $f->phoneNumber(2);
            $u->phone_number_2 =   $f->phoneNumber(3);
            $u->avatar = rand(1, 30) . ".jpg";
            $u->save();
        }
        dd("done"); */

        $sex = ['Male', 'Female'];
        $sub_counties = [];
        foreach (Location::get_sub_counties() as $v) {
            $sub_counties[] = $v->id;
        }


        for ($i = 0; $i < $max; $i++) {
            $u = new Administrator();
            $u->email = $f->email();
            $u->username = $u->email;
            $u->password = password_hash('4321', PASSWORD_DEFAULT);
            $u->avatar = rand(1, 20) . ".jpg";
            $u->created_at = $f->dateTimeBetween('-8 year');
            $u->date_of_birth = $f->dateTimeBetween('-35 year', '-18 year',);
            $u->first_name =   $f->firstName(5);
            $u->middle_name =   $f->firstName(6);
            $u->last_name =   $f->firstName(2);
            shuffle($sex);
            $u->sex =   $sex[0];
            $u->phone_number_1 =   $f->phoneNumber(2);
            $u->phone_number_2 =   $f->phoneNumber(3);
            shuffle($sub_counties);
            $u->sub_county_id =   $sub_counties[0];
            $u->address =   $f->address(3);
            $u->save();
        }
    }
}
