<?php

class Score extends CActiveRecord
{

    function tableName() {
        return 'scr_team_score';
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

    public function isScoreVisible($interval) {
        $sql = 'SELECT TIMESTAMPDIFF(SECOND, lastShow, NOW() )
                FROM scr_team_score
                WHERE team_id = ' . $this->team_id;
        $diff = intval(Yii::app()->db->createCommand($sql)->queryScalar());
        return $interval - $diff;
    }
}