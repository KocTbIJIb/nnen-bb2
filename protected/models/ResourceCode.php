<?php

class ResourceCode extends CActiveRecord
{

    function tableName() {
        return 'col_resources_codes';
    }

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function relations() {
        return array(
            'object' => array(self::BELONGS_TO, 'Object', 'object_id')
        );
    }

    public function rules()
    {
        return array(
        );
    }
}