<?php

class TeamLabel extends CActiveRecord
{

    function tableName() {
        return 'cha_team_labels';
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
            $this->taken = new CDbExpression('NOW()');
        }
        return parent::beforeSave();
    }

    public static function getCurrent($team_id) {
        $sql = 'SELECT tl.*, ' . Total::TIME_LIMIT . ' - TIMESTAMPDIFF(SECOND, tl.taken, NOW() ) AS secondsLeft
                FROM `cha_team_labels` AS `tl` 
                WHERE tl.done IS NULL AND tl.team_id = ' . intval($team_id);
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    public function clearExpired($team_id, $timeLimit) {
        $sql = 'UPDATE `cha_team_labels` 
                SET done = TIMESTAMPADD(SECOND, ' . intval($timeLimit) . ', taken) 
                WHERE TIMESTAMPADD(SECOND, ' . intval($timeLimit) . ', taken) < NOW() AND team_id = ' . intval($team_id);
        return Yii::app()->db->createCommand($sql)->query();
    }

    public static function pick($team_id) {
        $sql = 'SELECT l.*
                FROM `cha_labels` AS `l` 
                LEFT JOIN `cha_team_labels` AS tl ON tl.label_id = l.label_id AND tl.team_id = ' . intval($team_id) . '
                WHERE tl.taken IS NULL';
        $unpickedlabels = Yii::app()->db->createCommand($sql)->queryAll();
        if (empty($unpickedlabels)) {
            return false;
        }
        $nextLabel = $unpickedlabels[rand(0, count($unpickedlabels) - 1)];

        $tl = new self;
        $tl->team_id = $team_id;
        $tl->label_id = $nextLabel['label_id'];
        $tl->save();
        return true;
    }

    public function close() {
        $sql = 'SELECT TIMESTAMPDIFF(SECOND, taken, NOW() )
                FROM cha_team_labels
                WHERE id = ' . $this->id;
        $diff = intval(Yii::app()->db->createCommand($sql)->queryScalar());
        if ($diff > Total::TIME_LIMIT) {
            $this->done = new CDbExpression('TIMESTAMPADD(SECOND, ' . Total::TIME_LIMIT . ', taken)');
            $this->save();
        } else {
            $this->done = new CDbExpression('NOW()');
            $this->save();

            $total = Total::model()->findByPk($this->team_id);
            $total->total-= Total::TIME_LIMIT - $diff;
            $total->save();
        }
    }

    public static function findByLabelId($team_id, $label_id)
    {
        return self::model()->findByAttributes(array('label_id' => $label_id, 'team_id' => $team_id));
    }

}