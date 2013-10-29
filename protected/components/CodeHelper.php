<?php
class CodeHelper extends CComponent
{

    public static function filter($codes) {
        $return = array();
        foreach ($codes as $code) {
            $return[] = trim(mb_strtolower($code, 'utf8'));
        }
        return array_unique(array_filter($return));
    }

}