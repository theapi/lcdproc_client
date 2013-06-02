<?php
use Theapi\Lcdproc\Client;

require 'client.php';


$client = new Client();
$fp = $client->start('127.0.0.1', 13666);

$client->write('client_set name "php"');
$client->write('screen_add php_screen');
$client->write('screen_set php_screen -priority "info"');
$client->write('widget_add php_screen T1 string');
$client->write('widget_add php_screen T2 string');
$client->write('widget_set php_screen T1 1 1 {PHP client}');
$client->write('widget_set php_screen T2 1 2 {line 2}');

$client->read();

// send microtime when its our turn

$ignored = true;
while(!feof($fp)) {
    $line = $client->read();
    if ($line === "") {
       continue;
    } else {
        if (trim($line) == 'listen php_screen') {
            $ignored = false;
        } else if (trim($line) == 'ignore php_screen') {
            $ignored = true;
        }
        echo "> $line\n";
    }

    if (!$ignored) {
      $str = microtime(true);
      //$client->write('widget_set php_screen T 1 1 {PHP client}');
      $client->write("widget_set php_screen T2 1 2 {$str}");
    }
    usleep(200000);
}
