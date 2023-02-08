<?php

namespace App\Admin\Controllers;

use App\Models\CaseModel;
use App\Models\Location;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CaseModelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Cases';

    /*
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new CaseModel());

        $grid->export(function ($export) {

            $export->filename('Cases-report.csv');

            //$export->except(['column1', 'column2' ...]);

            // $export->only(['column3', 'column4' ...]);

            $export->originalValue(['status']);

            $export->column('is_authority', function ($value, $original) {
                if (((int)($original)) == 1) {
                    return ' In court';
                } else {
                    return 'Other legal proceedings';
                }
            });
            $export->column('is_court', function ($value, $original) {
                if (((int)($original)) == 1) {
                    return ' Yes';
                } else {
                    return ' No';
                }
            });
        });

        if (
            (!Admin::user()->isRole('admin')) ||
            (!Admin::user()->isRole('manager'))
        ) {
            $grid->model()->where([
                'administrator_id' =>  Admin::user()->id
            ]);
        }

        $grid->model()
            ->orderBy('created_at', 'Desc');

        $grid->filter(function ($f) {
            $f->disableIdFilter();
            $f->between('created_at', 'Filter by report date')->date();

            $f->equal('case_category', 'Filter by case category')->select(
                Utils::case_categpries()
            );
            $f->like('applicant_name', 'Filter by complainant name');
            $f->like('survivor_name', 'Filter by survivor name');
            $f->equal('sex', 'Filter by complainant\'s sex')->select([
                'Male' => 'Male',
                'Female' => 'Female',
            ]);



            $district_ajax_url = url(
                '/api/ajax?'
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&query_parent=0"
                    . "&model=Location"
            );
            $f->equal('district', 'Filter by district')->select(function ($id) {
                $a = Location::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
                ->ajax($district_ajax_url);
            $f->equal('complaint_method', 'Filter by Complaint Method')->select([
                'Individual' => 'Individual',
                'Group' => 'Group',
            ]);


            $f->equal('is_authority', 'Has the matter been submitted to or handled by any authority?')->select([
                1 => 'YES',
                0 => 'NO',
            ]);

            $f->equal('is_court', 'Is Court action or other legal proceedings pending?')->select([
                1 => 'YES',
                0 => 'NO',
            ]);

            $f->equal('status', 'Filter by case status?')->select(Utils::case_statuses());

            $users_ajax_url = url(
                '/api/ajax?'
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $f->equal('administrator_id', 'Filter by reporter')->select(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
                ->ajax($users_ajax_url);
        });

        $grid->disableBatchActions();

        $grid->quickSearch('title')->placeholder("Search by title...");
        $grid->column('id', __('ID'))->sortable()->hide();
        $grid->column('created_at', __('Reported'))
            ->display(function ($f) {
                return Utils::my_date($f);
            })->sortable();
        $grid->column('title', __('Title'));
        $grid->column('case_category', __('Category'))->sortable();
        $grid->column('applicant_name', __('Complainant'))->sortable();
        $grid->column('survivor_name', __('Survivor name'));
        $grid->column('sex', __('Sex'))->sortable();
        $grid->column('survivor_age', __('Survivor age'));
        $grid->column('district', __('District'))
            ->display(function ($f) {
                return Utils::get(Location::class, $f)->name_text;
            })->sortable();

        $grid->column('sub_county', __('Sub-county'))
            ->display(function ($f) {
                return Utils::get(Location::class, $f)->name_text;
            })->sortable();

        $grid->column('is_authority', __('Authority'))
            ->using([
                null => 'Not in Authority',
                0 => 'Not in Authority',
                1 => 'In Authority',
            ])
            ->dot([
                null => 'danger',
                0 => 'danger',
                1 => 'success',
            ], 'danger')
            ->sortable();

        $grid->column('is_court', __('Legal status'))
            ->using([
                null => 'Not in court',
                0 => 'Other legal proceedings',
                1 => 'In court',
            ])
            ->dot([
                null => 'danger',
                0 => 'danger',
                1 => 'success',
            ], 'danger')
            ->sortable();

        $grid->column('status', __('Status'))
            ->label([
                null => 'default',
                'Reported' => 'danger',
                'Active' => 'success',
                'Closed' => 'default',
            ], 'default')
            ->sortable();

        $grid->column('administrator_id', __('Reported by'))
            ->display(function ($f) {
                return Utils::get(Administrator::class, $f)->name;
            })->sortable();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $m = CaseModel::findOrFail($id);
        $show = new Show($m);


        $show->field('created_at', __('Reported'))
            ->as(function ($created_at) {
                return Utils::my_date($created_at);
            });

        $show->field('title', __('Title'));
        $show->field('case_category', __('Category'));
        $show->field('description', __('Description'))->unescape();
        $show->field('request', __('Request'))->unescape();
        $show->field('response', __('Response'))->unescape();

        $show->field('applicant_name', __('Complainant\'s name'));
        $show->field('complaint_method', __('Complaina method'));
        $show->field('phone_number_1', __('Complainant\'s phone number'))->unescape();
        $show->field('phone_number_2', __('Complainant\'s alternative phone number'))->unescape();


        $show->field('sub_county', __('Sub-county'))->as(function ($f) {
            return Utils::get(Location::class, $f)->name_text;
        });

        $show->field('address', __('Hoome address'))->unescape();
        $show->field('village', __('Village'))->unescape();

        $show->field('survivor_name', __('Survivor\'s name'));

        $show->field('sex', __('Survivor\'s Gender'));
        $show->field('survivor_age', __('Survivor\'s age'));



        $show->field('status', __('Status'));


        $show->field('is_court', __('Is Court action or other legal proceedings pending?'))
            ->as(function ($is_court) {
                if (((int)($is_court)) == 1) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            });

        $show->field('is_authority', __('Has the matter been submitted to or handled by any authority?'))
            ->as(function ($is_court) {
                if (((int)($is_court)) == 1) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            });

        $show->file('G.P.S Location')->unescape()->as(function () {

            $link = "https://www.google.com/maps/search/?api=1&query={$this->latitude},{$this->longitude}";
            return '<a  href="' . $link . '" target="_blank" >VIEW ON MAP</a>';
        });


        $show->field('administrator_id', __('Reported by'))
            ->as(function ($administrator_id) {
                return Utils::get(Administrator::class, $administrator_id)->name;
            });



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CaseModel());




        $form->tab('Case information', function ($form) {

            if ($form->isCreating()) {
                $form->hidden('administrator_id', __('Enterprise id'))->default(Admin::user()->id)->rules('required');
            };


            $form->date('created_at', __("Date"))
                ->help("When this case happened.")
                ->rules(['required']);

            $form->text('applicant_name', __("Complainant's name"))->rules(['required']);
            $form->text('survivor_name', __("Survivor's name"))
                ->help("Optional");
            $form->radio('sex', __("Survivor's sex"))
                ->options([
                    'Male' => 'Male',
                    'Female' => 'Female',
                    'Other' => 'Other'
                ])
                ->rules(['required']);

            $form->radio('survivor_age', __("Survivor's age"))
                ->options(Utils::age_brackets());

            $form->text('phone_number_1', __("Phone number"))->rules(['required']);
            $form->text('phone_number_2', __("Alternative phone number"));

            $form->divider();
            $form->select('case_category', __('Case category'))->options(Utils::case_categpries())
                ->rules(['required']);

            $form->radio('complaint_method', __("Complaint Method"))
                ->options([
                    'Individual' => 'Individual',
                    'Group' => 'Group'
                ])
                ->rules(['required']);

            $form->text('title', __('Case Title'))->rules(['required']);
            $form->quill('description', __('Case Description'));
            $form->textarea('request', __('What remedy/remedies is the complainant seeking for?'))->rules(['required']);

            $form->divider();

            $form->select('sub_county', __('Sub county'))
                ->help('Where this case took place')
                ->options(Location::get_sub_counties()->pluck('name_text', 'id'))
                ->rules(['required']);

            $form->text('village', __('Village'))->rules(['required']);
            $form->text('address', __('Home Address'))->rules(['required']);
            /* $form->latlong('latitude', 'longitude', 'Case location on map')->height(500)->rules('required'); */
        });

        $form->tab('Action', function ($form) {

            $form->radio('is_authority', __("Has the matter been submitted to or handled by any authority?"))
                ->options([
                    1 => 'Yes',
                    0 => 'No'
                ])
                ->rules(['required']);

            $form->radio('is_court', __("Legal status"))
                ->options([
                    1 => 'In court',
                    0 => 'Other legal proceedings',
                ])
                ->rules(['required']);

            $form->divider();

            $form->select('status', __("Case status"))
                ->help('Action made by CEHURD')
                ->options(Utils::case_statuses())
                ->rules(['required'])
                ->when('in', ['Solved', 'Closed'], function (Form $form) {
                    $form->quill('response', __('Action details'))->rules(['required']);
                });
        });


        $form->tab('More infomation', function (Form $form) {
            $form->morphMany('case_comments', 'Click new to add more information', function (Form\NestedForm $form) {
                $form->quill('body', __('More information'))->rules(['required']);
            });
        });


        return $form;
    }
}
