<?php

class ColonizationController extends EnController
{
    protected $with = array('col_start_team_codes', 'objects');
    protected $preWinCode = 'letmeoutofhere!!!111';

    public function actionStart()
    {
        if ($this->team->checkForPreWin()) {
            $this->_sendResponse(array('status' => 'win', 'code' => $this->preWinCode));    
        }

        $codes = CodeHelper::filter(Yii::app()->request->getPost('sectors', array()));
        //$codes = CodeHelper::filter($_REQUEST['sectors']);
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

            $this->team->refresh();
        }

        if ($this->team->checkForPreWin()) {
            $this->_sendResponse(array('status' => 'win', 'code' => $this->preWinCode));    
        }
        
        $this->_sendResponse(array('status' => 'game', 'objects' => $this->team->objects, 'message' => $message));
    }

}