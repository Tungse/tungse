<?php

function __autoload($class)
{
    try
    {
        $file = str_replace('_', '/', $class).'.php';
        $file = str_replace('//', '/_', $file);

        if(file_exists($file)) { require_once($file); } 
        else                   { throw new Exception('File <font color="red">'.$file.'</font> not found.'); }

        if(!class_exists($class)) { throw new Exception('Class <font color="red">'.$class.'</font> not found.'); }

        return true;

    } 
    catch(Exception $e) { die($e->getMessage()); }
}

?>