<?php

class Team extends CActiveRecord
{

    function tableName() {
        return 'teams';
    }

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function relations() {
        return array(
            'col_start_team_codes' => array(self::HAS_MANY, 'ColStartTeamCode', 'team_id'),
            'objects' => array(self::MANY_MANY, 'Object', 'col_team_objects(team_id, object_id)'),
        );
    }

    public function rules()
    {
        return array(
        );
    }

    public function checkForPreWin() {
        $towns = 0;
        $roads = 0;
        foreach ($this->objects as $object) {
            if ($object->type == 'town') {
                $towns++;
            } else if ($object->type == 'road') {
                $roads++;
            }
        }
        return $towns == 2 && $roads == 2;
    }

    public function countObjectsNum($type = 'town') {
        $num = 0;
        foreach ($this->objects as $object) {
            if ($object->type == $type) {
                $num++;
            }
        }
        return $num;
    }

    public function getNewCodes($codes, $alias) {
        $return = array();
        foreach ($codes as $newCode) {
            foreach ($this->{$alias} as $oldCode) {
                if ($newCode == $oldCode->code) {
                    $return[] = $newCode;
                    break;
                }
            }
        }
        return array_diff($codes, $return);
    }
}