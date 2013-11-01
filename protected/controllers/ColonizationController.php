<?php

class ColonizationController extends EnController
{
    protected $with = array('col_start_team_codes', 'col_team_codes', 'objects', 'balance');
    protected $preWinCode = 'letmeoutofhere!!!111';
    protected $winCode = 'JustaperfectdayDrinksangriainthepark';

    public function actionStart()
    {
        if ($this->team->checkForColPreWin()) {
            $this->_sendResponse(array('status' => 'win', 'code' => $this->preWinCode));    
        }

        $codes = CodeHelper::filter(Yii::app()->request->getPost('sectors', array()));
        $newCodes = $this->team->getNewCodes($codes, 'col_start_team_codes');

        $message = array();
        foreach ($newCodes as $newCode) {
            $criteria = new CDbCriteria;
            $criteria->with = array('neighbors');
            $object = Object::model()->findByAttributes(array('initial_code' => $newCode));
            if (empty($object)) {
                SmsHelper::send('Код не найден: ' . $newCode);
                continue;
            }
            $team_code = new ColStartTeamCode;
            $team_code->team_id = $this->team->id;
            $team_code->code = $newCode;
            $team_code->save();

            $ok = false;
            //Проверим можно ли построить выбраный объект
            if (empty($this->team->objects) && $object->type == 'town') {
                //Если это первый город, проверять нечего, просто строим
                $ok = true;
            } else if ($object->type == 'town') {
                if ($this->team->countObjectsNum('town') >= 2) {
                    $message[] = 'На этом этапе вы можете построить не более двух городов!';
                } else {
                    $ok = true;
                    //Переберём все уже созданые города потому, что...
                    foreach ($this->team->objects as $team_object) {
                        if ($team_object->type != 'town') {
                            continue;
                        }
                        $criteria = new CDbCriteria;
                        $criteria->with = array('neighbors');
                        $fullObject = Object::model()->findByPk($team_object->id, $criteria);
                        //..если есть общий сосед и этот сосед дорога, то строить такой город нельзя
                        foreach ($fullObject->neighbors as $currentNeighbor) {
                            if (in_array($currentNeighbor->id, CHtml::listData($object->neighbors, 'id', 'id')) && $currentNeighbor->type == 'road') {
                                $message[] = 'На этом этапе растояние между городами не должно быть менее двух дорог!';
                                $ok = false;
                                break;
                            }
                        }
                        if (!$ok) {
                            break;
                        }
                    }
                }
            } else if ($object->type == 'road') {
                foreach ($this->team->objects as $team_object) {
                    if ($team_object->type != 'town') {
                        continue;
                    }
                    $criteria = new CDbCriteria;
                    $criteria->with = array('neighbors');
                    $fullObject = Object::model()->findByPk($team_object->id, $criteria);
                    foreach ($fullObject->neighbors as $currentNeighbor) {
                        if ($object->id == $currentNeighbor->id) { //Рядом есть построеный город...
                            $allObjectsIds = CHtml::listData($this->team->objects, 'id', 'id');
                            $townNeghborsIds = CHtml::listData($fullObject->neighbors, 'id', 'id');
                            if (count(array_intersect($allObjectsIds, $townNeghborsIds))) {
                                $message[] = 'На этом этапе нельзя построить более одной дороги у каждого города!';
                                break;
                            }
                            $ok = true;
                            break;
                        }
                    }
                    if ($ok) {
                        break;
                    }
                }
                if (!$ok && empty($message)) {
                    $message[] = 'Дорога должна примыкать к построеному городу!';
                }
            }

            if ($ok) {
                $to = new TeamObject;
                $to->team_id = $this->team->id;
                $to->object_id = $object->id;
                $to->save();
            }

        }

        $this->team->refresh();

        if ($this->team->checkForColPreWin()) {
            $this->_sendResponse(array('status' => 'win', 'code' => $this->preWinCode));    
        }
        
        $this->_sendResponse(array(
            'status' => 'game', 
            'objects' => $this->team->objects, 
            'message' => $message,
            'team' => htmlspecialchars($this->team->name)
        ));
    }

    public function actionGame()
    {
        $message = array();

        if ($this->team->checkForColWin()) {
            $this->_sendResponse(array('status' => 'win', 'code' => $this->winCode));    
        }

        $codes = CodeHelper::filter(Yii::app()->request->getPost('sectors', array()));
        $resources = CodeHelper::filter(Yii::app()->request->getPost('resources', array()));
        $resources = empty($_REQUEST['resources']) ? array() : $_REQUEST['resources'];

        $newCodes = $this->team->getNewCodes($codes, 'col_team_codes');
        $newResources = $this->team->getNewCodes($resources, 'col_team_codes');


        
        foreach ($newResources as $resourceCode) {
            $criteria = new CDbCriteria;
            $criteria->with = array('object');
            $resource = ResourceCode::model()->findByAttributes(array('code' => $resourceCode));
            if (empty($resource) || ($resource->object->type != 'resource' && $resource->object->type != 'desert') || empty($resource->object->resource_type)) {
                SmsHelper::send('Код не найден или код не ресурс: ' . $resourceCode);
                continue;
            }

            if (!$this->team->checkResourceAccessibility($resource)) {
                $message[] = $resource->object->name . ' пока не доступен вашей команде!';
            } else {
                if (!$this->team->updateBalance($resource)) {
                    continue;
                }
            }

            $team_code = new ColTeamCode;
            $team_code->team_id = $this->team->id;
            $team_code->code = $resourceCode;
            $team_code->save();
        }

        $this->team->refresh();

        if ($this->team->checkForColWin()) {
            $this->_sendResponse(array('status' => 'win', 'code' => $this->winCode));
        }
        
        $this->_sendResponse(array(
            'status' => 'game', 
            'objects' => $this->team->objects, 
            'message' => $message,
            'team' => htmlspecialchars($this->team->name),
            'balance' => $this->team->balance
        ));
    }

}