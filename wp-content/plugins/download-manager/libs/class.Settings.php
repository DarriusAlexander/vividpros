<?php


namespace WPDM;

class Settings
{

    function get($name, $default = ''){
        $value = get_option($name);
        $value = htmlspecialchars_decode($value);
        $value = stripslashes_deep($value);
        $value = wpdm_escs($value);
        return $value;
    }

    function __get($name)
    {
        $name = "__wpdm_".$name;
        $value = get_option($name);
        $value = htmlspecialchars_decode($value);
        $value = stripslashes_deep($value);
        $value = wpdm_escs($value);
        return $value;
    }

    function __call($name, $args = null)
    {
        $name = "__wpdm_".$name;
        $value = get_option($name);
        $value = htmlspecialchars_decode($value);
        $value = stripslashes_deep($value);
        $value = wpdm_escs($value);
        return $value;
    }

    static function __callStatic($name, $args = null)
    {
        $name = "__wpdm_".$name;
        $value = get_option($name);
        $value = htmlspecialchars_decode($value);
        $value = stripslashes_deep($value);
        $value = wpdm_escs($value);
        return $value;
    }


}