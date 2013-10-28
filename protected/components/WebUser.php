<?php

class WebUser extends CWebUser {

    private $_model;

    public $homeUrl;
    public $returnUrl;

    protected function loadUser($id = null) 
    {
        if ($this->_model===null) {
            if ($id!==null)
                $this->_model=User::model()->findByPk($id);
        }
        return $this->_model;
    }

    public function setFlash($key,$value,$defaultValue=null)
    {
        //Глупый скорый костыль строками ниже
        if (in_array($key, array('success', 'error'))) {
            $key = $key . microtime();
        }
        parent::setFlash($key,$value,$defaultValue);
    }
}