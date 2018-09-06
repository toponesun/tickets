<?php
require_once "../base.php";
require_once "../app/Captcha.php";
$captcha = new Captcha();
$captcha->doimg();
$_SESSION['captcha'] = $captcha->getCode();