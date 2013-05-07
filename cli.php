<?php
use Theapi\Lcdproc\Client;

require 'client.php';


$client = new Client();
$fp = $client->start('192.168.0.145');

$client->write('client_set name "php"');
$client->read();
$client->write('screen_add php_screen');
$client->read();
$client->write('screen_set php_screen -priority "alert" -backlight "on"');
$client->read();

// more here...

while(!feof($fp)) {
    $line = $client->read();
    if ($line === "") {
       continue;
    }
    else {
        echo "> $line\n";
    }
}
