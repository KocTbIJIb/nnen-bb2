<?php

class Total extends CActiveRecord
{
    const LABELS_NUM = 40;
    const TOTAL_HANDICAP = 1800;
    const MAX_CURRENT_LABELS = 3;
    const TIME_LIMIT = 300;

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

    public function init()
    {
        if (empty($this->team_id)) {
            $this->team_id = $this->getTeam()->id;
            $this->total = self::LABELS_NUM * self::TIME_LIMIT;
            $this->save();
            $this->getTeam()->refresh();
        }
    }

    public function finish()
    {
        if (empty($this->team_id)) {
            $this->init();
        }
        $this->finished = 1;
        $this->save();
        $this->getTeam()->refresh();
    }

    public function isEverybodyFinished() {
        $sql = 'SELECT COUNT(*)
                FROM cha_team_total
                WHERE finished = 0';
        return intval(Yii::app()->db->createCommand($sql)->queryScalar()) == 0;    
    }

    public function countHandicap() {
        $sql = 'SELECT MIN(total) AS min, MAX( total ) AS max
                FROM  cha_team_total
                WHERE 1';
        $handicap = Yii::app()->db->createCommand($sql)->queryRow();
        $this->handicap = intval(($this->total - $handicap['min']) / ($handicap['max'] - $handicap['min']) * Total::TOTAL_HANDICAP);
        $status = $this->save();
        if (!$status) {
            return false;
        }
        $this->getTeam()->refresh();
        return true;
    }

    public function getSecondsToCode() {
        $sql = 'SELECT TIMESTAMPDIFF(SECOND, handicapStart, NOW() )
                FROM cha_team_total
                WHERE team_id = ' . $this->team_id;
        $diff = intval(Yii::app()->db->createCommand($sql)->queryScalar());
        return $this->handicap - $diff;
    }

    public function countPlace() {
        $sql = 'SELECT MAX( place ) AS max
                FROM cha_team_total
                WHERE 1';
        return intval(Yii::app()->db->createCommand($sql)->queryScalar()) + 1;    
    }

    public function getPlace() {
        if ($this->finished) {
            throw new Exception('Нельзя определить место, пока игра не закончена');
        }
        if (!empty($this->place)) {
            return $this->place;
        }
        $this->place = $this->countPlace();
        $this->save();
        return $this->place;
    }

    public function getTeam()
    {
        return $this->team;
    }

    public function startHandicap()
    {
        $this->handicapStart = new CDbExpression('NOW()');
        $this->save();
        $this->getTeam()->refresh();
    }

}