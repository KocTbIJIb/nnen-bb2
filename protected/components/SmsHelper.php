<?php
class SmsHelper extends CComponent
{

    public static function send($text, $number = null) {
        $number = empty($number) ? Yii::app()->params['myNumber'] : $number;
        $request = array(
            'api_id' => Yii::app()->params['smsApiKey'],
            'to' => $number,
            'text' => $text
        );
        $response = file_get_contents(Yii::app()->params['smsApiUrl'] . '?' . http_build_query($request));
        switch ($response) {
            case '206':
            case '201':
                self::send('Бабки или лимит кончились!!!');
                return false;
                break;
            
            default:
                return true;
                break;
        }
    }

}