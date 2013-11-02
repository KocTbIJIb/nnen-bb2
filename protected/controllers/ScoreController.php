<?php

class ScoreController extends EnController
{
    protected $with = array('scr_team_codes', 'score');

    protected $scoreShowInterval = 180;
    protected $aimScore = 20;

    protected $finalCode = 'nextlevelplease';

    public function actionGame()
    {
        if (empty($this->team->score)) {
            $score = new Score;
            $score->team_id = $this->team->id;
            $score->save();
            $this->team->refresh();
        } else if ($this->team->score->score >= $this->aimScore) {
            $this->_sendResponse(array(
                'status' => 'win', 
                'code' => $this->finalCode, 
                'team' => htmlspecialchars($this->team->name)
            ));    
        }

        $codes = CodeHelper::filter(Yii::app()->request->getPost('sectors', array()));
        $newCodes = $this->team->getNewCodes($codes, 'scr_team_codes');

        foreach ($newCodes as $newCode) {
            $code = ScrCodes::model()->findByAttributes(array('code' => $newCode));
            if (empty($code)) {
                SmsHelper::send('Код не найден: ' . $newCode . ' (' . $this->team->name . ')');
                continue;
            }
            $team_code = new ScrTeamCode;
            $team_code->team_id = $this->team->id;
            $team_code->code = $newCode;
            $team_code->save();

            $this->team->score->score+= $code->value;
            $this->team->score->save();

            $this->team->refresh();
        }

        if ($this->team->score->score >= $this->aimScore) {
            $this->_sendResponse(array(
                'status' => 'win', 
                'code' => $this->finalCode, 
                'team' => htmlspecialchars($this->team->name)
            ));
        }

        $this->_sendResponse(array('status' => 'game', 'team' => htmlspecialchars($this->team->name)));
    }

    public function actionCheck()
    {
        if (empty($this->team->score)) {
            $score = new Score;
            $score->team_id = $this->team->id;
            $score->lastShow = new CDbExpression('NOW()');
            $score->save();
            $this->_sendResponse(array('status' => 'score', 'score' => 0));
        }

        $secs = $this->team->score->isScoreVisible($this->scoreShowInterval);
        if ($secs <= 0 || empty($this->team->score->lastShow)) {
            $this->team->score->lastShow = new CDbExpression('NOW()');
            $this->team->score->save();
            $this->_sendResponse(array('status' => 'score', 'score' => $this->team->score->score));
        }
        $this->_sendResponse(array('status' => 'wait', 'seconds' => $secs));
    }
}