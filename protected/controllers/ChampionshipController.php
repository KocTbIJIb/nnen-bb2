<?php

class ChampionshipController extends EnController
{
    protected $with = array('cha_team_codes', 'total');
    protected $labelsNum = 40;
    protected $maxCurrentLabels = 3;
    protected $timeLimit = 300;
    protected $totalHandicap = 1800;

    protected $finalCode = 'whatadifferenceadaymakes';

    public function actionGame()
    {
        if (empty($this->team->total)) {
            $total = new Total;
            $total->team_id = $this->team->id;
            $total->total = $this->labelsNum * $this->timeLimit;
            $total->save();

            $this->team->refresh();
        }

        //Подчистить просроченные
        TeamLabel::model()->clearExpired($this->team->id, $this->timeLimit);

        $message = array();
        $codes = CodeHelper::filter(Yii::app()->request->getPost('sectors', array()));
        $codes = empty($_REQUEST['sectors']) ? array() : CodeHelper::filter($_REQUEST['sectors']);
        $newCodes = $this->team->getNewCodes($codes, 'cha_team_codes');

        foreach ($newCodes as $newCode) {

            $label = Label::model()->findByAttributes(array('code' => $newCode));
            if (empty($label)) {
                SmsHelper::send('Метка на найдена: ' . $newCode . ' (' . $this->team->name . ')');
                continue;
            }

            $team_code = new ChaTeamCode;
            $team_code->team_id = $this->team->id;
            $team_code->code = $newCode;
            $team_code->save();

            $teamLabel = TeamLabel::model()->findByAttributes(array('label_id' => $label->label_id));
            if (empty($teamLabel) || !empty($teamLabel->done)) {
                continue;
            }

            $teamLabel->close($this->timeLimit);
        }

        $this->_sendResponse(array(
            'table' => $this->team->getTeamsTotalList(),
            'current' => TeamLabel::model()->getCurrent($this->team->id, $this->timeLimit),
            'message' => $message,
            'team' => htmlspecialchars($this->team->name)
        ));
    }

    public function actionPick()
    {
        $currentLabels = TeamLabel::model()->getCurrent($this->team->id, $this->timeLimit);
        if (count($currentLabels) >= $this->maxCurrentLabels) {
            $this->_sendResponse(array(
                'status' => 'fail',
                'message' => 'У вас уже максимальное количество текущих меток!',
            ));
        }

        if (!TeamLabel::model()->pick($this->team->id)) {
            $this->_sendResponse(array(
                'status' => 'fail',
                'message' => 'Меток больше нет!',
            ));
        }

        $this->_sendResponse(array(
            'status' => 'ok',
            'current' => TeamLabel::model()->getCurrent($this->team->id, $this->timeLimit)
        ));
    }

    public function actionFinish()
    {
        if (empty($this->team->total->finished)) {
            $this->team->total->finished = 1;
            $this->team->total->save();
        }

        if (empty($this->team->total->handicap) && $this->team->total->isEverybodyFinished()) {
            $this->team->total->countHandicap($this->totalHandicap);
        }

        $this->_sendResponse(array(
            'status' => 'ok',
        ));
    }

    public function actionHandicap() {
        if (empty($this->team->total->handicapStart)) {
            $this->team->total->handicapStart = new CDbExpression('NOW()');
            $this->team->total->save();
            $this->team->refresh();
        }

        if (empty($this->team->total->handicap)) {
            $this->team->total->countHandicap($this->totalHandicap);
            $this->team->refresh();
        }

        $secs = $this->team->total->getSecondsToCode();

        if ($secs <= 0) {
            $this->_sendResponse(array(
                'status' => 'win',
                'code' => $this->finalCode
            ));
        } 
            
        $this->_sendResponse(array(
            'status' => 'wait',
            'secondsLeft' => $secs
        ));

    }

}