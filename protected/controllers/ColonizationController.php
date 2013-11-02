<?php

class ColonizationController extends EnController
{
    protected $with = array('col_start_team_codes', 'col_team_codes', 'objects', 'balance');
    protected $preWinCode = 'letmeoutofhere!!!111';
    protected $winCode = 'justaperfectdaydrinksangriainthepark';

    protected $roadCost = array('wood' => 1, 'stone' => 1);
    protected $colonyCost = array('wood' => 1, 'stone' => 1, 'flax' => 1);
    protected $townCost = array('water' => 3, 'flax' => 2);

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
                SmsHelper::send('Код не найден: ' . $newCode . ' (' . $this->team->name . ')');
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

            $this->team->refresh();
        }

        if ($this->team->checkForColPreWin()) {
            $this->_sendResponse(array('status' => 'win', 'code' => $this->preWinCode));    
        }
        
        $this->_sendResponse(array(
            'status' => 'game', 
            'objects' => TeamObject::model()->getTeamObjects($this->team->id),
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

        $newCodes = $this->team->getNewCodes($codes, 'col_team_codes');
        $newResources = $this->team->getNewCodes($resources, 'col_team_codes');

        //Постройка дорог и городов
        foreach ($newCodes as $newCode) {
            $codeParts = explode('_', $newCode);
            if (count($codeParts) != 3) {
                SmsHelper::send('Код постройки неверен: ' . $newCode . ' (' . $this->team->name . ')');
                continue;
            }
            if ($codeParts[0] == 'дорога') { //Cтроим дорогу
                $criteria = new CDbCriteria;
                $criteria->with = array('neighbors');
                $object = Object::model()->findByAttributes(array('name' => 'Дорога ' . intval($codeParts[1])));
                if (empty($object) || $object->type != 'road') {
                    SmsHelper::send('Объект не найден: Дорога ' . $codeParts[1] . ' (' . $this->team->name . ')');
                    continue;
                }
                if (!$this->team->isEnoughMoney($this->roadCost)) {
                    $message[] = 'Недостаточно средств для постройки дороги!';
                    $this->team->logCode('ColTeamCode', $newCode);
                    continue;
                }
                if (!$this->team->checkObjectAccessibility($object)) {
                    $message[] = 'Дорога должна примыкать к уже построеным вами объектам!';
                    $this->team->logCode('ColTeamCode', $newCode);
                    continue;
                }
                if ($this->team->alreadyHaveObject($object) > 0) {
                    $message[] = 'Вы уже построили эту дорогу ранее!';
                    $this->team->logCode('ColTeamCode', $newCode);
                    continue;
                }
                if (!$this->team->buildObject($object, $this->roadCost)) {
                    SmsHelper::send('Дорога ' . $codeParts[1] . ' почему то не построилась :( (' . $this->team->name . ')');
                    continue;
                }
            } else { //Строим город либо поселение
                $criteria = new CDbCriteria;
                $criteria->with = array('neighbors');
                $object = Object::model()->findByAttributes(array('name' => mb_ucfirst($codeParts[0])));
                if (empty($object) || $object->type != 'town') {
                    SmsHelper::send('Объект не найден: ' . $codeParts[0] . ' (' . $this->team->name . ')');
                    continue;
                }


                $townsHere = $this->team->alreadyHaveObject($object);
                if ($codeParts[1] == 'поселение') {
                    if (!$this->team->checkObjectAccessibility($object, true)) {
                        $message[] = 'Поселение должно примыкать к уже построеным вами объектам!';
                        $this->team->logCode('ColTeamCode', $newCode);
                        continue;
                    }
                    if ($townsHere != 0) {
                        $message[] = 'Город либо поселение уже построены на этом месте!';
                        $this->team->logCode('ColTeamCode', $newCode);
                        continue;
                    }
                    if (!$this->team->isEnoughMoney($this->colonyCost)) {
                        $message[] = 'Недостаточно средств для постройки поселения!';
                        $this->team->logCode('ColTeamCode', $newCode);
                        continue;
                    }
                    if (!$this->team->buildObject($object, $this->colonyCost)) {
                        SmsHelper::send('Поселение ' . $codeParts[1] . ' почему то не построилась :( (' . $this->team->name . ')');
                        continue;
                    }
                } else if ($codeParts[1] == 'город') {
                    if ($townsHere != 1) {
                        $message[] = 'Город либо уже построен, либо поселение ещё не построено на этом месте!';
                        $this->team->logCode('ColTeamCode', $newCode);
                        continue;
                    }
                    if (!$this->team->isEnoughMoney($this->townCost)) {
                        $message[] = 'Недостаточно средств для постройки города!';
                        $this->team->logCode('ColTeamCode', $newCode);
                        continue;
                    }
                    if (!$this->team->buildObject($object, $this->townCost)) {
                        SmsHelper::send('Город ' . $codeParts[1] . ' почему то не построилась :( (' . $this->team->name . ')');
                        continue;
                    }
                }
            }

            $this->team->refresh();
        }
        
        //Зачисление ресурсов
        foreach ($newResources as $resourceCode) {
            $criteria = new CDbCriteria;
            $criteria->with = array('object');
            $resource = ResourceCode::model()->findByAttributes(array('code' => $resourceCode));
            if (empty($resource) || ($resource->object->type != 'resource' && $resource->object->type != 'desert') || empty($resource->object->resource_type)) {
                SmsHelper::send('Код не найден или код не ресурс: ' . $resourceCode . ' (' . $this->team->name . ')');
                continue;
            }

            if (!$this->team->checkObjectAccessibility($resource->object)) {
                $message[] = $resource->object->name . ' пока не доступен вашей команде!';
            } else {
                if (!$this->team->updateBalanceByResource($resource->object)) {
                    continue;
                }
            }

            $this->team->logCode('ColTeamCode', $resourceCode);
            $this->team->refresh();
        }

        if ($this->team->checkForColWin()) {
            $this->_sendResponse(array('status' => 'win', 'code' => $this->winCode));
        }
        

        $this->_sendResponse(array(
            'status' => 'game', 
            'objects' => TeamObject::model()->getTeamObjects($this->team->id),
            'message' => $message,
            'team' => htmlspecialchars($this->team->name),
            'balance' => $this->team->balance
        ));
    }

    public function actionExchange()
    {
        $resources_types = array('wood','stone','flax','water');
        $num = intval(Yii::app()->request->getPost('num', 0));
        if (!$num || $num % 4) {
            $this->_sendResponse(array('status' => 'error', 'error' => 'Обмениваемая сумма должна быть кратна четырем'));
        }
        $from = Yii::app()->request->getPost('from', '');
        $to = Yii::app()->request->getPost('to', '');
        if (!in_array($from, $resources_types) || !in_array($to, $resources_types) || $from == $to) {
            $this->_sendResponse(array('status' => 'error', 'error' => 'Типы ресурсов указаны неверно'));
        }

        if ($this->team->balance->{$from} < $num) {
            $this->_sendResponse(array('status' => 'error', 'error' => 'Недостаточно ресурсов'));
        }

        $this->team->balance->{$from}-= $num;
        $this->team->balance->{$to}+= intval($num / 4);
        $this->team->balance->save();
        $this->_sendResponse(array('status' => 'ok'));
    }

}