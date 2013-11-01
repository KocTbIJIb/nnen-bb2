<?php
class CodeHelper extends CComponent
{

    public static function filter($codes) {
        $return = array();
        foreach ($codes as $code) {
            $code = mb_convert_encoding($code, 'UTF-8');
            $code = strip_tags($code, 'UTF-8');
            $code = mb_strtolower($code);
            $code = trim($code);
            $return[] = $code;
        }
        return array_unique(array_filter($return));
    }

}