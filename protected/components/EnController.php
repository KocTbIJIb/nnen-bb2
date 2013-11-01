<?php

class EnController extends CController
{

    private $game_domain = '52.en.cx';
    protected $with = array();
    protected $team;

    public function init() {
        mb_internal_encoding("UTF-8");
        if (!function_exists('mb_ucfirst')) {
            function mb_ucfirst($str, $enc = 'utf-8') { 
                return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc).mb_substr($str, 1, mb_strlen($str, $enc), $enc); 
            }
        }
        $this->_checkAuth();
    }

    protected function _sendResponse($data)
    {
        $content_type = 'application/json';
        header('Access-Control-Allow-Origin: http://' . $this->game_domain);
        header('Content-type: ' . $content_type);
        echo CJSON::encode($data);
        Yii::app()->end();
    }

    private function _checkAuth()
    {
        if (empty($_REQUEST['team_hash'])) {
            $this->_sendResponse(array('status' => 'error', 'message' => 'Unauthorized'));
        }

        $team_hash = $_REQUEST['team_hash'];
        $criteria = new CDbCriteria;
        $criteria->with = $this->with;
        $team = Team::model()->findByAttributes(array('hash' => $team_hash), $criteria);
        if (empty($team)) {
            $this->_sendResponse(array('status' => 'error', 'message' => 'Unauthorized'));
        }
        $this->team = $team;
    }
}