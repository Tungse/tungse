<?php

abstract class controller__common
{
    protected $action;
    protected $controller;
    
    public function __construct() 
    {
        $this->controller = self::request('c', 'index');
        $this->action     = self::request('a', 'index');
    }  
    
    protected function request($key, $default = NULL, $scope = 'REQUEST')
    {
        $scope = strtoupper($scope);

        switch($scope) 
        {
            case 'POST'   : $value = isset($_POST[$key])    ? $_POST[$key]    : $default; break;
            case 'GET'    : $value = isset($_GET[$key])     ? $_GET[$key]     : $default; break;
            case 'SESSION': $value = isset($_SESSION[$key]) ? $_SESSION[$key] : $default; break;
            case 'FILES'  : $value = isset($_FILES[$key])   ? $_FILES[$key]   : $default; break;
            default       : $value = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default; break;
        }

        return $value;
    }
    
    protected function view($body)
    {
    	if(file_exists('view/default/html/_common.phtml'))
    	{
	    	ob_start();
    		
			include('view/default/html/_common.phtml');
    		$html = ob_get_contents();
    			
	   		ob_end_clean(); 
    		
	    	echo $html;
    	}
    }
    
	protected function error($message)
	{
		header('Content-Type: Application/json');
		
		$response          = new stdClass();
		$response->error   = true;
		$response->message = $message;
		
		print json_encode($response);
		exit();
	}

	protected function setJson($data)
	{
		header('Content-Type: Application/json');
		print json_encode($data);
		exit();
	}
	
	protected function getJson($url)
	{
		$data = @file_get_contents($url);
		$data = ($data != false) ? json_decode($data) : NULL;
	
		return $data;
	}
	
	protected function redirect($url)
	{
		header('location:'.$url);
		exit();
	}
}

?>
