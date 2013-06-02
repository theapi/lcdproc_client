<?php
namespace Theapi\Lcdproc;

class Client
{

    protected $debug = 1;
    protected $server;
    protected $port;

    protected $fp;

    public function start($server, $port = 13666)
    {

        $this->connect($server, $port);
        $this->write('hello');
        $line = $this->read();

        $serverInfo = substr($line, 8);
        $this->serverInfo = $serverInfo;
    		if($this->debug > 0) echo ">< Server connected, info: $serverInfo.\n";

    		// connect LCDproc 0.5dev protocol 0.3 lcd wid 16 hgt 2 cellwid 5 cellhgt 8
    		if(preg_match('/wid (\d+)/', $serverInfo, $matches)) {
    		    $this->width = $matches[1];

    			if ($this->debug > 0) echo ">< Display width: ".$this->width."\n";
    		}
    		else	//No match??
    		{
    			$this->width = 16;	//Default to 16
    			if ($this->debug > 0) echo ">< Display width set to 16 (default)\n";
    		}

    		$matches = null;
    		//Extract the height for future use "hgt 4"
    		if(preg_match('/hgt (\d+)/', $serverInfo, $matches)) {
    			$this->height = $matches[1];
    			if ($this->debug > 0) echo ">< Display height: ".$this->height."\n";
    		} else	//No match??
    		{
    			$this->height = 2;	//Default to 2
    			if ($this->debug > 0) echo ">< Display height set to 2 (default)\n";
    		}

        return $this->fp;
    }

    public function connect($server, $port = 13666)
    {
        $this->server = $server;
        $this->port = $port;
        if ($this->debug > 0) {
            echo '>< Connecting to tcp://' . $this->server . ':' . $this->port . "\n";
        }

        $this->fp = stream_socket_client('tcp://' . $this->server . ':' . $this->port, $errno, $errstr, 30);

        if (!$this->fp) {
            throw new \Exception('Unable to connect to ' . $this->server . ':' . $this->port, $errno);
        }
        stream_set_timeout($this->fp, 2);

        if ($this->debug > 0) {
            echo ">< Connected!\n";
        }
    }

    public function read()
    {
        if (!$this->fp) {
            throw new \Exception('No connection to ' . $this->server . ':' . $this->port);
        }

        $line = fgets($this->fp);

        if ($this->debug > 2) {
            $info = stream_get_meta_data($link);
            echo " < $line".($info['timed_out'] ? " read timed out" : "")."\n";
        }

        if ($this->debug > 1) {
            echo " < $line\n";
        }
        return $line;
    }

    public function write($buf)
    {
        if (!$this->fp) {
            throw new \Exception('No connection to ' . $this->server . ':' . $this->port);
        }

        if ($this->debug > 1) {
            foreach(explode("\n", $buf) as $line) echo " > $line\n";
        }
		    fwrite($this->fp, "$buf\n");
    }

    public function disconnect()
    {
        if ($this->debug > 1) {
            echo ">< Disconnecting from LCDd\n";
        }

        $this->write('bye');
        fclose($this->fp);

        if ($this->debug > 1) {
            echo ">< Disconnected!\n";
        }
    }

}