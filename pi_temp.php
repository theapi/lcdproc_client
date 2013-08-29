<?php
use Theapi\Lcdproc\Client;

require 'client.php';

// get an initial temperature
$str = getTemp();
$read = time(); // remember when the temperature was read

$client = new Client();
$fp = $client->start('192.168.0.11', 13666);

$client->write('client_set name "pitemp"');
$client->write('screen_add pitemp_screen');
$client->write('screen_set pitemp_screen -priority "info"');
$client->write('widget_add pitemp_screen T1 string');
$client->write('widget_add pitemp_screen T2 string');
$client->write('widget_set pitemp_screen T1 1 1 { Pi temperature}');
$client->write('widget_set pitemp_screen T2 1 2 {' . $str . '}');

$client->read();

// send temperature when its our turn

$ignored = true;
while(!feof($fp)) {
    $line = $client->read();
    if ($line === "") {
       continue;
    } else {
        if (trim($line) == 'listen pitemp_screen') {
            $ignored = false;
        } else if (trim($line) == 'ignore pitemp_screen') {
            $ignored = true;
        }
    }

    if (!$ignored) {
      // update reading every 5 seconds
      $now = time();
      if ($now - $read > 5) {
        $read = $now;
        $str = getTemp();
        $client->write('widget_set pitemp_screen T2 1 2 {' . $str . '}');
      }
    }
    usleep(200000);
}

function getTemp() {
  $str = shell_exec('/opt/vc/bin/vcgencmd measure_temp');
  return str_replace('temp=', '     ', trim($str));
}
