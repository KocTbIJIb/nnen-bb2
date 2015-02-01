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
            'cha_team_codes' => array(self::HAS_MANY, 'ChaTeamCode', 'team_id'),
            'total' => array(self::HAS_ONE, 'Total', 'team_id'),
            'team_labels' => array(self::HAS_MANY, 'TeamLabel', 'team_id'),
            'scr_team_codes' => array(self::HAS_MANY, 'ScrTeamCode', 'team_id'),
            'score' => array(self::HAS_ONE, 'Score', 'team_id'),
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

    public function updateBalanceByResource($resource) {
        if (empty($this->balance)) {
            $balance = new Balance;
            $balance->team_id = $this->id;
        } else {
            $balance = Balance::model()->findByPk($this->id);
        }
        $balance->{$resource->resource_type}+= 1;
        return $balance->save();
    }

    public function logCode($model, $code) {
        $team_code = new $model;
        $team_code->team_id = $this->id;
        $team_code->code = $code;
        return $team_code->save();
    }

    public function checkObjectAccessibility($objectToCheck, $checkForNearRoad = false) {
        foreach ($this->objects as $object) {
            if ($object->type != 'town' && !$checkForNearRoad) {
                continue;
            } else if ($object->type != 'road' && $checkForNearRoad) {
                continue;
            }
            $criteria = new CDbCriteria;
            $criteria->with = array('neighbors');
            $fullObject = Object::model()->findByPk($object->id, $criteria);
            foreach ($fullObject->neighbors as $town_neighbor) {
                if ($town_neighbor->id == $objectToCheck->id) {
                    return true;
                }
            }
        }
        return false;
    }

    public function alreadyHaveObject($objectToCheck) {
        $to = TeamObject::model()->findByAttributes(
            array(
                'team_id' => $this->id, 
                'object_id' => $objectToCheck->id
            )
        );
        return empty($to) ? 0 : $to->count;
    }

    public function isEnoughMoney($money) {
        if (empty($this->balance)) {
            return false;
        }
        foreach ($money as $key => $value) {
            if (intval($this->balance->{$key}) < $value) {
                return false;
            }
        }
        return true;
    }

    public function buildObject($object, $cost) {
        if (!$this->charge($cost)) {
            return false;
        }
        if ($object->type == 'town') {
            $this->updateBalance(array('total' => 1));
        }

        $to = TeamObject::model()->findByAttributes(array('team_id' => $this->id, 'object_id' => $object->id));
        if (empty($to)) {
            $to = new TeamObject;
            $to->team_id = $this->id;
            $to->object_id = $object->id;
        } else {
            $to->count+= 1;
        }

        return $to->save();
    }

    public function charge($money) {
        $balance = Balance::model()->findByPk($this->id);
        if (empty($balance)) {
            return false;
        }

        foreach ($money as $key => $value) {
            if (intval($balance->{$key}) < $value) {
                return false;
            } else {
                $balance->{$key}-= $value;
            }
        }
        return $balance->save();
    }

    public function updateBalance($money) {
        $balance = Balance::model()->findByPk($this->id);

        foreach ($money as $key => $value) {
            $balance->{$key}+= $value;
        }
        return $balance->save();
    }

    public function getTeamsTotalList() {
        $sql = 'SELECT t.id, t.name, tot.total
                FROM `teams` AS `t` 
                LEFT JOIN `cha_team_total` AS tot ON t.id = tot.team_id 
                WHERE tot.total IS NOT NULL
                ORDER BY tot.total ASC';
        $teams = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($teams as $key => $team) {
            $teams[$key]['name'] = htmlspecialchars($team['name']);
        }
        return $teams;
    }

    public static function findByHash($hash, $with)
    {
        $criteria = new CDbCriteria;
        $criteria->with = $with;
        return self::model()->findByAttributes(array('hash' => $hash), $criteria);
    }

    public function getTotal()
    {
        return empty($this->total) ? new Total : $this->total;
    }

    public function clearExpiredLabels()
    {
        /** @var $model TeamLabel */
        $model = TeamLabel::model();
        $model->clearExpired($this->id, Total::TIME_LIMIT);
    }
}