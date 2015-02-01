<?php

class Label extends CActiveRecord
{

    function tableName() {
        return 'cha_labels';
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

    public static function findByCode($code)
    {
        return self::model()->findByAttributes(array('code' => $code));
    }
}