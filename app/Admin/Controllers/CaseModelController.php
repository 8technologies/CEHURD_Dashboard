<?php

namespace App\Admin\Controllers;

use App\Models\CaseModel;
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

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('case_category_id', __('Case category id'));
        $grid->column('district', __('District'));
        $grid->column('sub_county', __('Sub county'));
        $grid->column('title', __('Title'));
        $grid->column('description', __('Description'));
        $grid->column('response', __('Response'));
        $grid->column('status', __('Status'));
        $grid->column('latitude', __('Latitude'));
        $grid->column('longitude', __('Longitude'));

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
        $show = new Show(CaseModel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('case_category_id', __('Case category id'));
        $show->field('district', __('District'));
        $show->field('sub_county', __('Sub county'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('response', __('Response'));
        $show->field('status', __('Status'));
        $show->field('latitude', __('Latitude'));
        $show->field('longitude', __('Longitude'));

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


        if ($form->isCreating()) {
            $form->hidden('administrator_id', __('Enterprise id'))->default(Admin::user()->id)->rules('required');
        };

        $form->select('case_category_id', __('Case category'))->options([
            1 => 'Rape',
            2 => 'Child abuse',
            3 => 'Other',
        ])
            ->creationRules(['required']);
        $form->select('sub_county', __('Sub county'))->options([
            1 => 'Sub county 1',
            2 => 'Sub county 2',
            3 => 'Sub county 3',
        ])
            ->creationRules(['required']);

        $form->text('title', __('Case Title'));
        $form->textarea('description', __('Case Description'));
        $form->textarea('response', __('Response'));

        $form->text('latitude', __('Latitude'));
        $form->text('longitude', __('Longitude'));
        $form->divider();
        $form->select('status', __('Case Status'))->options([
            1 => 'Status 1',
            2 => 'Status 2',
            3 => 'Status 3',
        ]);

        return $form;
    }
}
