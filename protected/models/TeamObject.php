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

    public function getTeamObjects($team_id) {
        $sql = 'SELECT o.id, o.name, o.type, o.resource_type, to.count 
                FROM `col_team_objects` AS `to` 
                LEFT JOIN `col_objects` AS o ON to.object_id = o.id 
                WHERE to.team_id = ' . intval($team_id);
        return Yii::app()->db->createCommand($sql)->queryAll();
    }
}