<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Zebra_Image;

class Utils  extends Model
{


    public static function tell_suspect_status_color($status)
    {
        switch ($status) {
            case 'Pending':
                return 'default';
                break;
            case 'Active':
                return 'warning';
                break;
            case 'Solved':
                return 'success';
                break;
            case 'Closed':
                return 'danger';
                break;
            default:
                return 'default';
                break;
        }
    }
    public static function docs_root($params = array())
    {
        $r = $_SERVER['DOCUMENT_ROOT'] . "";
        $r = str_replace('/public', "", $r);
        $r = $r . "/public";
        return $r;
    }


    public static function get($class, $id)
    {
        $data = $class::find($id);
        if ($data != null) {
            return $data;
        }
        return new $class();
    }

    public static function create_thumbail($params = array())
    {

        ini_set('memory_limit', '-1');

        if (
            !isset($params['source']) ||
            !isset($params['target'])
        ) {
            return [];
        }

        $image = new Zebra_Image();

        $image->auto_handle_exif_orientation = false;
        $image->source_path = "" . $params['source'];
        $image->target_path = "" . $params['target'];


        if (isset($params['quality'])) {
            $image->jpeg_quality = $params['quality'];
        }

        $image->preserve_aspect_ratio = true;
        $image->enlarge_smaller_images = true;
        $image->preserve_time = true;
        $image->handle_exif_orientation_tag = true;

        $img_size = getimagesize($image->source_path); // returns an array that is filled with info

        $width = 300;
        $heigt = 300;

        if (isset($img_size[0]) && isset($img_size[1])) {
            $width = $img_size[0];
            $heigt = $img_size[1];
        }
        //dd("W: $width \n H: $heigt");

        if ($width < $heigt) {
            $heigt = $width;
        } else {
            $width = $heigt;
        }

        if (isset($params['width'])) {
            $width = $params['width'];
        }

        if (isset($params['heigt'])) {
            $width = $params['heigt'];
        }

        $image->jpeg_quality = 50;
        $image->jpeg_quality = Utils::get_jpeg_quality(filesize($image->source_path));
        if (!$image->resize($width, $heigt, ZEBRA_IMAGE_CROP_CENTER)) {
            return $image->source_path;
        } else {
            return $image->target_path;
        }
    }

    public static function get_jpeg_quality($_size)
    {
        $size = ($_size / 1000000);

        $qt = 50;
        if ($size > 5) {
            $qt = 10;
        } else if ($size > 4) {
            $qt = 13;
        } else if ($size > 2) {
            $qt = 15;
        } else if ($size > 1) {
            $qt = 17;
        } else if ($size > 0.8) {
            $qt = 50;
        } else if ($size > .5) {
            $qt = 80;
        } else {
            $qt = 90;
        }

        return $qt;
    }

    public static function process_images_in_backround()
    {
        $url = url('api/process-pending-images');
        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        try {
            $data =  file_get_contents($url, null, $ctx);
            return $data;
        } catch (Exception $x) {
            return "Failed $url";
        }
    }

    public static function process_images_in_foreround()
    {
        $imgs = Image::where([
            'thumbnail' => null
        ])->get();

        foreach ($imgs as $img) {
            $thumb = Utils::create_thumbail([
                'source' => Utils::docs_root() . '/storage/images/' . $img->src,
                'target' => Utils::docs_root() . '/storage/images/thumb_' . $img->src,
            ]);
            if ($thumb != null) {
                if (strlen($thumb) > 4) {
                    $img->thumbnail = $thumb;
                    $img->save();
                }
            }
        }
    }



    public static function upload_images_1($files, $is_single_file = false)
    {

        ini_set('memory_limit', '-1');
        if ($files == null || empty($files)) {
            return $is_single_file ? "" : [];
        }
        $uploaded_images = array();
        foreach ($files as $file) {

            if (
                isset($file['name']) &&
                isset($file['type']) &&
                isset($file['tmp_name']) &&
                isset($file['error']) &&
                isset($file['size'])
            ) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name = time() . "-" . rand(100000, 1000000) . "." . $ext;
                $destination = Utils::docs_root() . '/storage/images/' . $file_name;

                $res = move_uploaded_file($file['tmp_name'], $destination);
                if (!$res) {
                    continue;
                }
                //$uploaded_images[] = $destination;
                $uploaded_images[] = $file_name;
            }
        }

        $single_file = "";
        if (isset($uploaded_images[0])) {
            $single_file = $uploaded_images[0];
        }


        return $is_single_file ? $single_file : $uploaded_images;
    }




    public static function phone_number_is_valid($phone_number)
    {
        if (substr($phone_number, 0, 4) != "+256") {
            return false;
        }

        if (strlen($phone_number) != 13) {
            return false;
        }

        return true;
    }
    public static function prepare_phone_number($phone_number)
    {

        if (strlen($phone_number) == 14) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = str_replace("256", "", $phone_number);
        }


        if (strlen($phone_number) > 11) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = substr($phone_number, 3, strlen($phone_number));
        } else {
            if (strlen($phone_number) == 10) {
                $phone_number = substr($phone_number, 1, strlen($phone_number));
            }
        }


        if (strlen($phone_number) != 9) {
            return $phone_number;
        }

        $phone_number = "+256" . $phone_number;
        return $phone_number;
    }


    public static function my_date($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y');
    }



    public static function ent()
    {



        $ent_id  = 1;
        $u = Auth::user();
        if ($u != null) {
            $ent_id = ((int)($u->enterprise_id));
        }
        $ent = Enterprise::find($ent_id);

        if ($ent == null) {
            $subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];
            $ent = Enterprise::where([
                'subdomain' => $subdomain
            ])->first();
        }


        if ($ent == null) {
            $ent = Enterprise::find(1);
        }

        return $ent;
    }

    public static function case_statuses()
    {
        return [
            'Pending' => 'Pending',
            'Active' => 'Active',
            'Solved' => 'Solved',
            'Closed' => 'Closed',
        ];
    }
    public static function case_categpries()
    {
        return [
            'Access to medicines/services' => 'Access to medicines/services',
            'Access to information' => 'Access to information',
            'General information inquiry' => 'General information inquiry',
            'Health Systems Strengthening' => 'Health Systems Strengthening',
            'Health workers’ issues' => 'Health workers’ issues',
            'HIV/AIDS' => 'HIV/AIDS',
            'Maternal Health' => 'Maternal Health',
            'Mental Health' => 'Mental Health',
            'Sex workers' => 'Sex workers',
            'Sexual and Gender-Based Violence' => 'Sexual and Gender-Based Violence',
            'Abortion cases' => 'Abortion cases',
            'Other cases' => 'Other cases',
        ];
    }


    public static function month($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('M - Y');
    }


    public static function get_category_pic($cat)
    {
        if ($cat == 'General information inquiry') {
            return url('assets/img/info.png');
        } else if ($cat == 'Access to medicines/services') {
            return url('assets/img/medicine.png');
        } else if ($cat == 'Access to information') {
            return url('assets/img/info-access.png');
        } else if ($cat == 'Health Systems Strengthening') {
            return url('assets/img/health-plus.png');
        } else if ($cat == 'Health workers’ issues') {
            return url('assets/img/health-worker.png');
        } else if ($cat == 'HIV/AIDS') {
            return url('assets/img/aids.png');
        } else if ($cat == 'Maternal Health') {
            return url('assets/img/maternal-health.png');
        } else if ($cat == 'Sex workers') {
            return url('assets/img/sex-workers.png');
        } else if ($cat == 'Sexual and Gender-Based Violence') {
            return url('assets/img/gender-based.png');
        } else if ($cat == 'Abortion cases') {
            return url('assets/img/abortion.png');
        } else if ($cat == 'Mental Health') {
            return url('assets/img/mental-health.png');
        }

        return url('assets/img/abortion.png');
    }
}

/* $conn = new mysqli(
    env('DB_HOST'),
    env('DB_USERNAME'),
    env('DB_PASSWORD'),
    env('DB_DATABASE'),
);
die(env('DB_DATABASE')); */
