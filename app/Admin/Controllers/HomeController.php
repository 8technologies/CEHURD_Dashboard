<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\MyFaker;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Content $content)
    {

        //MyFaker::make_reports(2200);
        //MyFaker::make_cases(10000);
        //MyFaker::make_users(1000);
        // dd("done");

        $content
            ->title('CEHURD - Dashboard')
            ->description('Hello ' . Auth::user()->name . "!");



        $content->row(function (Row $row) {
            $row->column(4, function (Column $column) {
                $column->append(Dashboard::grahp_cases());
            });

            $row->column(2, function (Column $column) {
                $column->append(Dashboard::graph_gender());
            });
            $row->column(2, function (Column $column) {
                $column->append(Dashboard::graph_category());
            });
        });

        return $content;
    }



    public function stats(Content $content)
    {

        Admin::style('.content-header {display: none;}');
        $ent = Utils::ent();
        Utils::reconcile_in_background(Admin::user()->enterprise_id);

        return $content
            ->title($ent->name)
            ->description('Dashboard')
            ->row(function (Row $row) {
                $u = Admin::user();



                if (
                    $u->isRole('super-admin')
                ) {
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::all_users());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::all_teachers());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::all_students());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::enterprises());
                    });
                }

                if (
                    $u->isRole('admin') ||
                    $u->isRole('bursar') ||
                    $u->isRole('dos')
                ) {
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::students());
                    });

                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::teachers());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::parents());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::fees());
                    });
                }
            })
            ->row(function (Row $row) {

                $u = Admin::user();
                if (
                    $u->isRole('admin') ||
                    $u->isRole('bursar')
                ) {
                    $row->column(6, function (Column $column) {
                        $column->append(Dashboard::income_vs_expenses());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::fees_collected());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::help_videos());
                    });
                }
            });
    }
}
