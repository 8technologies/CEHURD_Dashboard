<?php

namespace Encore\Admin\Controllers;

use App\Models\CaseModel;
use App\Models\Enterprise;
use App\Models\StudentHasClass;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Admin;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class Dashboard
{


    public static function new_cases()
    {
        $new_cases = CaseModel::where([])
            ->orderBy('created_at', 'Desc')->limit(10)->get();

        return view('dashboard.new_cases', [
            'items' => $new_cases
        ]);
    }

    public static function graph_months()
    {

        for ($i = 12; $i >= 0; $i--) {
            $min = new Carbon();
            $max = new Carbon();
            $max->subMonths($i);
            $min->subMonths(($i + 1));
            $created_at = CaseModel::whereBetween('created_at', [$min, $max])->count();
            $data['created_at'][] = $created_at;
            $data['labels'][] = Utils::month($max);
        }


        return view('dashboard.graph_months', $data);
    }
    public static function graph_statistics()
    {

        $data = [];

        $data['data']['Access to medicines/services'] = CaseModel::where('case_category', 'Access to medicines/services')->count();

        $data['data']['Access to information'] = CaseModel::where('case_category', 'Access to information')->count();

        $data['data']['Health Systems Strengthening'] = CaseModel::where('case_category', 'Health Systems Strengthening')->count();

        $data['data']['Health workers’ issues'] = CaseModel::where('case_category', 'Health workers’ issues')->count();

        $data['data']['HIV/AIDS'] = CaseModel::where('case_category', 'HIV/AIDS')->count();

        $data['data']['Maternal Health'] = CaseModel::where('case_category', 'Maternal Health')->count();

        $data['data']['Sex workers'] = CaseModel::where('case_category', 'Sex workers')->count();

        $data['data']['Sexual and Gender-Based Violence'] = CaseModel::where('case_category', 'Sexual and Gender-Based Violence')->count();

        $data['data']['Abortion cases'] = CaseModel::where('case_category', 'Abortion cases')->count();

        $data['data']['Other cases'] = CaseModel::where('case_category', 'Other cases')->count();

        return view('dashboard.graph_statistics', $data);
    }

    public static function graph_category()
    {

        $data = [];

        $data['data'][] = CaseModel::where('case_category', 'Access to medicines/services')->count();
        $data['labels'][] = 'Medicines';

        $data['data'][] = CaseModel::where('case_category', 'Access to information')->count();
        $data['labels'][] = 'Information';

        $data['data'][] = CaseModel::where('case_category', 'Health Systems Strengthening')->count();
        $data['labels'][] = 'Health Systems';


        $data['data'][] = CaseModel::where('case_category', 'Health workers’ issues')->count();
        $data['labels'][] = 'Health workers';

        $data['data'][] = CaseModel::where('case_category', 'HIV/AIDS')->count();
        $data['labels'][] = 'HIV/AIDS';

        $data['data'][] = CaseModel::where('case_category', 'Maternal Health')->count();
        $data['labels'][] = 'Maternal health';

        $data['data'][] = CaseModel::where('case_category', 'Sex workers')->count();
        $data['labels'][] = 'Sex workers';

        $data['data'][] = CaseModel::where('case_category', 'Sexual and Gender-Based Violence')->count();
        $data['labels'][] = 'Gender-Based Violence';

        $data['data'][] = CaseModel::where('case_category', 'Abortion cases')->count();
        $data['labels'][] = 'Abortion';

        $data['data'][] = CaseModel::where('case_category', 'Other cases')->count();
        $data['labels'][] = 'Other cases';

        return view('dashboard.graph_category', $data);
    }

    public static function graph_gender()
    {

        $data = [];
        $data['males'] = CaseModel::where('sex', 'Male')->count();
        $data['females'] = CaseModel::where('sex', 'Female')->count();
        $data['other'] = CaseModel::where('sex', 'Other')->count();
        $data['labels'][] = 'Male';
        $data['labels'][] = 'Female';
        $data['labels'][] = 'Other';
        
        return view('dashboard.graph_gender', $data);
    }


    public static function grahp_cases()
    {

        $data = [];
        for ($i = 12; $i >= 0; $i--) {
            $min = new Carbon();
            $max = new Carbon();
            $max->subDays($i);
            $min->subDays(($i + 1));
            $count = CaseModel::whereBetween('created_at', [$min, $max])->count();

            $Reported = CaseModel::whereBetween('created_at', [$min, $max])
                ->where([
                    'status' => 'Reported'
                ])
                ->count();

            $Active = CaseModel::whereBetween('created_at', [$min, $max])
                ->where([
                    'status' => 'Active'
                ])
                ->count();
            $Closed = CaseModel::whereBetween('created_at', [$min, $max])
                ->where([
                    'status' => 'Closed'
                ])
                ->count();
            $data['data'][] = $count;
            $data['Reported'][] = $Reported;
            $data['Active'][] = $Active;
            $data['Closed'][] = $Closed;
            $data['labels'][] = Utils::my_date($max);
        }

        return view('dashboard.grahp_cases', $data);
    }



    public static function help_videos()
    {
        return view('widgets.help-videos');
    }

    public static function all_users()
    {
        $u = Auth::user();
        $all_students = User::count();

        $male_students = User::where([
            'user_type' => 'Student',
            'sex' => 'Male',
        ])->count();
        $female_students = $all_students - $male_students;
        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'All system users',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('auth/users')
        ]);
    }
    public static function all_teachers()
    {
        $all_students = User::where([
            'user_type' => 'employee',
        ])->count();

        $male_students = User::where([
            'user_type' => 'employee',
            'sex' => 'Male',
        ])->count();


        $female_students = $all_students - $male_students;
        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'All admins',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('auth/users')
        ]);
    }


    public static function all_students()
    {
        $all_students = User::where([
            'user_type' => 'Student',
        ])->count();

        $male_students = User::where([
            'user_type' => 'Student',
            'sex' => 'Male',
        ])->count();

        $female_students = $all_students - $male_students;

        $sub_title = number_format($male_students) . ' Today, ';
        $sub_title .= number_format($female_students) . ' This week.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Transactions',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('auth/users')
        ]);
    }




    public static function students()
    {
        $u = Auth::user();
        $all_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'Student',
        ])->count();

        $male_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'Student',
            'sex' => 'Male',
        ])->count();

        $female_students = $all_students - $male_students;

        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Students',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('students')
        ]);
    }

    public static function teachers()
    {
        $u = Auth::user();
        $all_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
        ])->count();

        $male_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
            'sex' => 'Male',
        ])->count();


        $female_students = $all_students - $male_students;

        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Teachers',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('employees')
        ]);
    }


    public static function parents()
    {
        $u = Auth::user();
        $all_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
        ])->count();

        $male_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
            'sex' => 'Male',
        ])->count();

        $female_students = $all_students - $male_students;

        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Parents',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('employees')
        ]);
    }




    public static function enterprises()
    {
        $enterprises = Enterprise::count();

        return view('widgets.box-5', [
            'is_dark' => true,
            'title' => 'All Enterprises',
            'sub_title' => 'Lifetime',
            'number' => number_format($enterprises),
            'link' => admin_url('employees')
        ]);
    }




    public static function fees()
    {
        $ent = Utils::ent();

        $u = Auth::user();
        $all_students = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
        ])->where('academic_year_id', '!=', $ent->administrator_id)->sum('amount');

        $fees_to_be_collected = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
        ])
            ->where('amount', '<', 0)
            ->where('academic_year_id', '!=', $ent->administrator_id)->sum('amount');

        $fees_collected = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
        ])
            ->where('amount', '>', 0)
            ->where('academic_year_id', '!=', $ent->administrator_id)->sum('amount');
        //dd($all_students);

        $male_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
            'sex' => 'Male',
        ])->count();

        $female_students = $all_students - $male_students;

        $fees_to_be_collected = (-1) * ($fees_to_be_collected);
        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        $sub_title = number_format($fees_to_be_collected) . " School fees to be collected";
        return view('widgets.box-5', [
            'is_dark' => true,
            'title' => 'School fees',
            'sub_title' => $sub_title,
            'number' => number_format($fees_collected),
            'link' => admin_url('employees')
        ]);
    }



    public static function income_vs_expenses()
    {
        return view('admin.charts.bar', [
            'is_dark' => true
        ]);
    }

    public static function fees_collected()
    {
        return view('admin.charts.pie', [
            'is_dark' => true
        ]);
    }



    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function title()
    {
        return view('admin::dashboard.title');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function environment()
    {
        $envs = [
            ['name' => 'PHP version',       'value' => 'PHP/' . PHP_VERSION],
            ['name' => 'Laravel version',   'value' => app()->version()],
            ['name' => 'CGI',               'value' => php_sapi_name()],
            ['name' => 'Uname',             'value' => php_uname()],
            ['name' => 'Server',            'value' => Arr::get($_SERVER, 'SERVER_SOFTWARE')],

            ['name' => 'Cache driver',      'value' => config('cache.default')],
            ['name' => 'Session driver',    'value' => config('session.driver')],
            ['name' => 'Queue driver',      'value' => config('queue.default')],

            ['name' => 'Timezone',          'value' => config('app.timezone')],
            ['name' => 'Locale',            'value' => config('app.locale')],
            ['name' => 'Env',               'value' => config('app.env')],
            ['name' => 'URL',               'value' => config('app.url')],
        ];

        return view('admin::dashboard.environment', compact('envs'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function extensions()
    {
        $extensions = [
            'helpers' => [
                'name' => 'laravel-admin-ext/helpers',
                'link' => 'https://github.com/laravel-admin-extensions/helpers',
                'icon' => 'gears',
            ],
            'log-viewer' => [
                'name' => 'laravel-admin-ext/log-viewer',
                'link' => 'https://github.com/laravel-admin-extensions/log-viewer',
                'icon' => 'database',
            ],
            'backup' => [
                'name' => 'laravel-admin-ext/backup',
                'link' => 'https://github.com/laravel-admin-extensions/backup',
                'icon' => 'copy',
            ],
            'config' => [
                'name' => 'laravel-admin-ext/config',
                'link' => 'https://github.com/laravel-admin-extensions/config',
                'icon' => 'toggle-on',
            ],
            'api-tester' => [
                'name' => 'laravel-admin-ext/api-tester',
                'link' => 'https://github.com/laravel-admin-extensions/api-tester',
                'icon' => 'sliders',
            ],
            'media-manager' => [
                'name' => 'laravel-admin-ext/media-manager',
                'link' => 'https://github.com/laravel-admin-extensions/media-manager',
                'icon' => 'file',
            ],
            'scheduling' => [
                'name' => 'laravel-admin-ext/scheduling',
                'link' => 'https://github.com/laravel-admin-extensions/scheduling',
                'icon' => 'clock-o',
            ],
            'reporter' => [
                'name' => 'laravel-admin-ext/reporter',
                'link' => 'https://github.com/laravel-admin-extensions/reporter',
                'icon' => 'bug',
            ],
            'redis-manager' => [
                'name' => 'laravel-admin-ext/redis-manager',
                'link' => 'https://github.com/laravel-admin-extensions/redis-manager',
                'icon' => 'flask',
            ],
        ];

        foreach ($extensions as &$extension) {
            $name = explode('/', $extension['name']);
            $extension['installed'] = array_key_exists(end($name), Admin::$extensions);
        }

        return view('admin::dashboard.extensions', compact('extensions'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function dependencies()
    {
        $json = file_get_contents(base_path('composer.json'));

        $dependencies = json_decode($json, true)['require'];

        return Admin::component('admin::dashboard.dependencies', compact('dependencies'));
    }
}
