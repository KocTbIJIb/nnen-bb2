<?php

class TeamObject extends CActiveRecord
{

    function tableName() {
        return 'col_team_objects';
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
        if ($this->isNewRecord) {
            $this->date = new CDbExpression('NOW()');
        }
        return parent::beforeSave();
    }
}