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
            'col_team_codes' => array(self::HAS_MANY, 'ColTeamCode', 'team_id'),
            'objects' => array(self::MANY_MANY, 'Object', 'col_team_objects(team_id, object_id)'),
            'balance' => array(self::HAS_ONE, 'Balance', 'team_id'),
        );
    }

    public function rules()
    {
        return array(
        );
    }

    public function checkForColPreWin() {
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

    public function checkForColWin() {
        return !empty($this->balance) && $this->balance->total >= 10;
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

    public function updateBalance($resource) {
        if (empty($this->balance)) {
            $balance = new Balance;
            $balance->team_id = $this->id;
        } else {
            $balance = Balance::model()->findByPk($this->id);
        }
        $balance->{$resource->object->resource_type}+= 1;
        return $balance->save();
    }

    public function checkResourceAccessibility($resource) {
        foreach ($this->objects as $object) {
            if ($object->type != 'town') {
                continue;
            }
            $criteria = new CDbCriteria;
            $criteria->with = array('neighbors');
            $fullObject = Object::model()->findByPk($object->id, $criteria);
            foreach ($fullObject->neighbors as $town_neighbor) {
                if ($town_neighbor->id == $resource->object->id) {
                    return true;
                }
            }
        }
        return false;
    }
}