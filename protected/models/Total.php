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

    public function isEverybodyFinished() {
        $sql = 'SELECT COUNT(*)
                FROM cha_team_total
                WHERE finished = 0';
        return intval(Yii::app()->db->createCommand($sql)->queryScalar()) == 0;    
    }

    public function countHandicap($totalHandicap) {
        $sql = 'SELECT MIN(total) AS min, MAX( total ) AS max
                FROM  cha_team_total
                WHERE 1';
        $handicap = Yii::app()->db->createCommand($sql)->queryRow();
        $this->handicap = intval(($this->total - $handicap['min']) / ($handicap['max'] - $handicap['min']) * $totalHandicap);
        return $this->save();
    }

}