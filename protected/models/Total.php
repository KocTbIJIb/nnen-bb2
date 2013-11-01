<?php

class Total extends CActiveRecord
{

    function tableName() {
        return 'cha_team_total';
    }

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function relations() {
        return array(
            'team' => array(self::BELONGS_TO, 'Team', 'id')
        );
    }

    public function rules()
    {
        return array(
        );
    }
}