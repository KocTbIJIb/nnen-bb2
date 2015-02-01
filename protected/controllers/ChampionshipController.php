<?php

/**
 * @todo Отрефакторить init
 * @todo Отрефакторить используемые модели
 * @todo Проверить работоспособность
 */

class ChampionshipController extends EnController
{
    protected $with = array('cha_team_codes', 'total');

    protected $finalCodes = array(
        1 => 'voluptatem',
        2 => 'doorem',
        3 => 'consectur',
        4 => 'accudsamus',
        5 => 'asperioresd',
        6 => 'eum',
        7 => 'weaknesss',
        8 => 'eery',
        9 => 'maxime',
        10 => 'whatadifferenceadaymakes'
    );

    public function actionGame()
    {
        $team = $this->getTeam();

        $team->getTotal()->init();
        $team->clearExpiredLabels();

        $message = array();
        $codes = CodeHelper::filter(Yii::app()->request->getPost('sectors', array()));
        $newCodes = $team->getNewCodes($codes, 'cha_team_codes');

        foreach ($newCodes as $newCode)
        {
            $label = Label::findByCode($newCode);
            if (empty($label)) {
                SmsHelper::send('Метка на найдена: ' . $newCode . ' (' . $team->name . ')');
                continue;
            }

            ChaTeamCode::logCode($team->id, $newCode);

            $teamLabel = TeamLabel::findByLabelId($team->id, $label->label_id);
            if (empty($teamLabel) || !empty($teamLabel->done)) {
                continue;
            }

            $teamLabel->close();
        }

        $this->_sendResponse(array(
            'table' => $team->getTeamsTotalList(),
            'current' => TeamLabel::getCurrent($team->id),
            'message' => $message,
            'team' => htmlspecialchars($team->name)
        ));
    }

    public function actionPick()
    {
        $team = $this->getTeam();

        $currentLabels = TeamLabel::getCurrent($team->id);
        if (count($currentLabels) >= Total::MAX_CURRENT_LABELS) {
            $this->_sendResponse(array(
                'status' => 'fail',
                'message' => 'У вас уже максимальное количество текущих меток!',
            ));
        }

        if (!TeamLabel::pick($team->id)) {
            $this->_sendResponse(array(
                'status' => 'fail',
                'message' => 'Меток больше нет!',
            ));
        }

        $this->_sendResponse(array(
            'status' => 'ok',
            'current' => TeamLabel::getCurrent($team->id)
        ));
    }

    public function actionFinish()
    {
        $total = $this->getTeam()->getTotal();

        $total->finish();

        if (empty($total->handicap) && $total->isEverybodyFinished()) {
            $total->countHandicap();
        }

        $this->_sendResponse(array(
            'status' => 'ok',
        ));
    }

    public function actionHandicap()
    {
        $total = $this->getTeam()->getTotal();
        $total->init();

        if (empty($total->handicapStart)) {
            $total->startHandicap();
        }

        if (empty($total->handicap)) {
            $total->countHandicap();
        }

        $secs = $total->getSecondsToCode();

        if ($secs <= 0) {
            $place = $total->getPlace();

            $this->_sendResponse(array(
                'status' => 'win',
                'code' => $this->finalCodes[($place >= 10 ? 10 : $place)]
            ));
        } 
            
        $this->_sendResponse(array(
            'status' => 'wait',
            'secondsLeft' => $secs
        ));

    }

}
