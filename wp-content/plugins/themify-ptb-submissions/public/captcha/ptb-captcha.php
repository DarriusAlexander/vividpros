<?php
$dir = dirname(dirname(__DIR__));
require_once($dir.'/includes/class-ptb-submissions-captcha.php');
if (isset($_GET['ptb_captcha']) && $_GET['ptb_captcha'] && isset($_GET['t']) && $_GET['t']) {
    PTB_Submission_Captcha::output($_GET['ptb_captcha']);
}