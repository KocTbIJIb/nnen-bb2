<?php

class Balance extends CActiveRecord
{

    function tableName() {
        return 'col_team_balance';
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
    
    public function beforeSave() {
        $this->lastUpdate = new CDbExpression('NOW()');
        return parent::beforeSave();
    }
}