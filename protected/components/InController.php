<?php

class InController extends CController
{
    public $layout='//layouts/nocolumn';
    public $menu=array();
    public $breadcrumbs=array();

    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array(
                'deny',
                'users'=>array('?'),
            ),
            array(
                'allow',
                'users'=>array('@'),
            )
        );
    }
}