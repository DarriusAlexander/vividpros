<?php

class WPDM_Messages {

    public $template = "blank";

    public static function fullPage($title, $msg, $type = 'error'){
        include wpdm_tpl_path("message.php");
    }

    public static function message($msg, $die = 0){
        if(is_array($msg))
            $message = "<div class='w3eden'><div class='alert alert-{$msg['type']}' data-title='{$msg['title']}'>{$msg['message']}</div></div>";
        else
            $message = $msg;
        if($die==-1) return $message;
        if($die==0)
        echo $message;
        if($die==1) {
            $content = "<div style='display: table;vertical-align: middle;height: 90%;position: absolute;width: 90%;margin-left: 5%;'>
                        <div style='text-align: center;height: 100%;display: table-cell;vertical-align: middle'>
                        <div style='max-width: 70% !important;display: inline-block;font-size: 13pt'>
                            $message
                        </div></div></div>";
            include wpdm_tpl_path("blank.php");
            die();
        }
        return true;
    }

    public static function error($msg, $die = 0){
        if(!is_array($msg)) {
            $message = $msg;
            $msg = array();
            $msg['message'] = $message;
        }
        if(!isset($msg['title'])) $msg['title'] = 'Error!';
        $msg['type'] = 'danger';
        $msg['icon'] = 'exclamation-triangle';
        return self::Message($msg, $die);
    }

    public static function warning($msg, $die = 0){
        if(!is_array($msg)) {
            $message = $msg;
            $msg = array();
            $msg['message'] = $message;
        }
        if(!isset($msg['title'])) $msg['title'] = 'Warning!';
        $msg['type'] = 'warning';
        $msg['icon'] = 'exclamation-circle';
        return self::Message($msg, $die);
    }

    public static function info($msg, $die = 0){
        if(!is_array($msg)) {
            $message = $msg;
            $msg = array();
            $msg['message'] = $message;
        }
        if(!isset($msg['title'])) $msg['title'] = 'Attention!';
        $msg['type'] = 'info';
        $msg['icon'] = 'info-circle';
        return self::Message($msg, $die);
    }

    public static function success($msg, $die = 0){
        if(!is_array($msg)) {
            $message = $msg;
            $msg = array();
            $msg['message'] = $message;
        }
        if(!isset($msg['title'])) $msg['title'] = 'Awesome!';
        $msg['type'] = 'success';
        $msg['icon'] = 'check-circle';
        return self::Message($msg, $die);
    }
} 