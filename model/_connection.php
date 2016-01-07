<?php

class model__connection
{
    private $connection;
    private $server   = 'sql234.your-server.de';
    private $user     = 'tungse';
    private $password = 'M6hf2e6a';
	
    public function __construct($database)
    {
        $this->connection = mysql_connect($this->server, $this->user, $this->password) or die("No connection: ".mysql_error());
        mysql_select_db($database) or die("No connection to database");
    }

    public function __destruct()
    {   		
		mysql_close($this->connection);
    }
}

?>