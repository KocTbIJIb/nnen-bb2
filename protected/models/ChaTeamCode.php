<?php

class ChaTeamCode extends CActiveRecord
{

    function tableName() {
        return 'cha_team_codes';
    }

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function relations() {
        return array(
            'team' => array(self::BELONGS_TO, 'Team', 'team_id')
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

    public static function logCode($team_id, $code) {
        $team_code = new self;
        $team_code->team_id = $team_id;
        $team_code->code = $code;
        $team_code->save();
    }
}