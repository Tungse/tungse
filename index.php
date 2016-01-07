<?php

error_reporting(E_ALL ^ E_DEPRECATED);
ini_set("display_errors", 1);
setlocale(LC_ALL, 'de_DE');
date_default_timezone_set('Europe/Berlin');
session_start();

require_once('setting/general.php');
require_once('library/autoload.php');

final class router extends controller__common
{
    public function __construct()
    {        
        $connection = new model__connection(DATABASE);
        new library_filter();

        $controller = parent::request('c', 'index');
        
        switch($controller)
       	{   
	    	case 'index':
            default     : new controller_index(); break;
        }

        unset($connection);
    }
}

new router();

?>
