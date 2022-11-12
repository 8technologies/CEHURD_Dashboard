<?php

namespace App\Admin\Controllers;

use App\Models\ActivityReport;
use App\Models\Location;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ActivityReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Activity Reports';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ActivityReport());
        $grid->disableBatchActions();
        $grid->model()
            ->orderBy('activity_date', 'Desc');


        $grid->filter(function ($f) {
            $f->disableIdFilter();
            $f->between('activity_date', 'Filter by activity date')->date();
            $f->like('facilitator_name', 'Filter by facilitator');


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

            $f->equal('status', 'Filter by case status?')->select(Utils::case_statuses());

            $users_ajax_url = url(
                '/api/ajax?'
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $f->equal('reported_by', 'Filter by reporter')->select(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
                ->ajax($users_ajax_url);
        });

        $grid->quickSearch('activity_title')->placeholder('Search by activity title...');

        $grid->column('id', __('ID'))->hide();
        $grid->column('activity_date', __('Date'))
            ->display(function ($f) {
                return Utils::my_date($f);
            })->sortable();
        $grid->column('activity_title', __('Title'));
        $grid->column('facilitator_name', __('Facilitator'));

        $grid->column('district', __('District'))
            ->display(function ($f) {
                return Utils::get(Location::class, $f)->name_text;
            })->sortable();

        $grid->column('sub_county', __('Sub-county'))
            ->display(function ($f) {
                return Utils::get(Location::class, $f)->name_text;
            })->sortable();

        $grid->column('number_of_attended', __('No. of attendants'));
        $grid->column('reported_by', __('Reported by'))
            ->display(function ($f) {
                return Utils::get(Administrator::class, $f)->name;
            })->sortable();

        $grid->column('status', __('Status'))
            ->label([
                null => 'default',
                'Pending' => 'default',
                'Active' => 'warning',
                'Solved' => 'success',
                'Closed' => 'danger',
            ], 'default')
            ->sortable();
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
        $show = new Show(ActivityReport::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('facilitator_name', __('Facilitator name'));
        $show->field('facilitator_title', __('Facilitator title'));
        $show->field('sub_county', __('Sub county'));
        $show->field('district', __('District'));
        $show->field('activity_date', __('Activity date'));
        $show->field('activity_venue', __('Activity venue'));
        $show->field('activity_description', __('Activity description'));
        $show->field('how_issues_will_be_followed_up', __('How issues will be followed up'));
        $show->field('recommendations', __('Recommendations'));
        $show->field('lessons_learned', __('Lessons learned'));
        $show->field('challanges_solutions', __('Challanges solutions'));
        $show->field('challanges_faced', __('Challanges faced'));
        $show->field('issues_raised', __('Issues raised'));
        $show->field('activity_duration', __('Activity duration'));
        $show->field('number_of_conducted', __('Number of conducted'));
        $show->field('number_of_attended', __('Number of attended'));
        $show->field('reported_by', __('Reported by'));
        $show->field('approved_by', __('Approved by'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ActivityReport());

        $form->tab('Activity information', function ($form) {

            if ($form->isCreating()) {
                $form->hidden('reported_by', __('reported_by'))->default(Admin::user()->id)->rules('required');
            };
            $form->date('activity_date', __("Activity date"))->rules(['required']);
            $form->text('activity_title', __("Activity title"))->rules(['required']);
            $form->select('sub_county', __('Sub county'))
                ->rules('int|required')
                ->help('Where this case took place')
                ->options(Location::get_sub_counties()->pluck('name_text', 'id'));
            $form->text('activity_venue', __("Activity venue"))->rules(['required']);
            $form->quill('activity_description', __("Activity description"))->rules(['required'])
                ->help('Description of the Activity (JAS programme objective under which the activity falls, Objective of the activity and why was the activity conducted, describe how the activity was done, How many people attended disaggregated by main characteristics)');
        });

        $form->tab('Activity facilitator', function ($form) {
            $form->text('facilitator_name', __("Facilitator name"))->rules(['required']);
            $form->text('facilitator_title', __("Facilitator title"))->rules(['required']);
            $form->text('number_of_conducted', __("Facilitator contact"))->rules(['required']);
        });
        $form->tab('Activity outcomes', function ($form) {
            $form->decimal('activity_duration', __("Activity duration"))->help('In hours');
            $form->decimal('number_of_attended', __("How many attended?"))->help('In hours');
            $form->quill('issues_raised', __("Issues raised"))
                ->help('Important Issues raised during implementation (include commitments made by decision makers and problems raised by participants)');

            $form->quill('how_issues_will_be_followed_up', __("How will you follow up on the issues identified during this meeting/activity mentioned above?"));

            $form->quill('challanges_faced', __("Challanges faced"))
                ->help('What challenges did you face during the implementation of this activity?');

            $form->quill('challanges_solutions', __("Challanges solutions"))
                ->help('How did you you address challenges you have just menstioned above?');

            $form->quill('lessons_learned', __("Lessons learned"))
                ->help('Lessons learnt while implementing the activity');
            $form->quill('recommendations', __("Recommendations"));
        });

        $form->tab('Reaction', function ($form) {
            $form->select('status', __("Case status"))
                ->default('Pending')
                ->help('Action made by CEHURD')
                ->options(Utils::case_statuses())
                ->rules(['required'])
                ->when('in', ['Solved', 'Closed'], function (Form $form) {
                    $form->quill('action_done', __('Action details'))->rules(['required']);
                });

            $ajax_url = url(
                '/api/ajax?'
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $form->select('approved_by', 'Filter by district')->options(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
                ->ajax($ajax_url);
        });
        return $form;
    }
}
