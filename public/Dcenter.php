<?php
$f = $_REQUEST["f"];
$str = str_replace('MP_verify_','',$f);
echo $str;