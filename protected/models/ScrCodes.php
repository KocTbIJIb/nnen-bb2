<?php

class ScrCodes extends CActiveRecord
{

    function tableName() {
        return 'scr_codes';
    }

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function relations() {
        return array(
        );
    }

    public function rules()
    {
        return array(
        );
    }
}