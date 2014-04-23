<?php

if (strpos($_SERVER['REMOTE_ADDR'], '192.168.') === 0
    || in_array(@$_SERVER['REMOTE_ADDR'], array(
    '127.0.0.1',
    '::1',
    '10.59.5.49',
))) {

    ob_start();
    phpinfo();
    $info = ob_get_clean();
    echo('<h2><center>(this request took ~'.round((microtime(true)-$_SERVER['REQUEST_TIME_FLOAT'])* 1000).' ms)</center></h2>');
    echo(str_replace('width="600"', 'width="90%"', $info));
}
