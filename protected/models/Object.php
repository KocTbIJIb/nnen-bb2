<?php

class Object extends CActiveRecord
{

    function tableName() {
        return 'col_objects';
    }

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function relations() {
        return array(
            'team' => array(self::MANY_MANY, 'Team', 'col_team_objects(team_id, object_id)'),
            'neighbors' => array(self::MANY_MANY, 'Object', 'col_object_neighbors(object_id, neighbor_id)')
        );
    }

    public function rules()
    {
        return array(
        );
    }
}