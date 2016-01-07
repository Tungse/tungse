<?php

class model_comment extends model__common
{
	public $id;
    public $ip;
    public $content;
    public $_created;
    
    public function __construct()
    {
        return $this;
    }
    
    public static function get(model_comment $comment)
    {
    	$model = new model__common('model_comment');
		$query = array();

		foreach(get_object_vars($comment) as $name => $value) 
		{ 
			if(isset($value) || $value == 'NULL') $query[] = "`$name` = '$value'"; 
		}

		if(count($query) > 0)
        {
	   		$query = implode(" AND ", $query);
	    	return $model->_get("SELECT * FROM `comment` WHERE {$query}");
		}
    }
    
    public static function getByID($commentId)
    {
    	$model = new model__common('model_comment');
    	
    	return $model->_get("SELECT * FROM `comment` WHERE `id` = '{$commentId}'");
    }
    
    public static function gets(model_comment $comment)
    {
    	$model = new model__common('model_comment');
		$query = array();
		
		foreach(get_object_vars($comment) as $name => $value) 
		{ 
			if(isset($value) || $value == 'NULL') $query[] = "`$name` = '$value'";
		}
	
		if(count($query) > 0)
        {
	    	$query = implode(" AND ", $query);
	    	return $model->_gets("SELECT * FROM `comment` WHERE {$query} ORDER BY `_created` DESC");
		}
		else return $model->_gets("SELECT * FROM `comment` ORDER BY `_created` DESC");
    }
    
    public static function set(model_comment $comment)
    {	
    	$model = new model__common('model_comment');
		$query = array();
        $keys  = array('id');
        
		foreach(get_object_vars($comment) as $name => $value) 
		{ 
			if(!in_array($name, $keys) && (isset($value) || $value == 'NULL')) $query[] = "`$name` = '$value'";
		}
        
        if(count($query) > 0)
        {
            $query = implode(",", $query);
            return $model->_set("INSERT INTO `comment` SET `id` = '{$comment->id}', {$query} ON DUPLICATE KEY UPDATE {$query}");
        }
		else return $model->_set("INSERT INTO `comment` SET `id` = '{$comment->id}'");
    }
     
    public static function delete(model_comment $comment)
    {
    	$model = new model__common('model_comment');
    	$query = array();
    	
		foreach(get_object_vars($comment) as $name => $value) 
		{
			if(isset($value) || $value == 'NULL') $query[] = "`$name` = '$value'"; 
		}
	
		if(count($query) > 0)
        {
	    	$query = implode(" AND ", $query);
	    	return $model->_delete("DELETE FROM `comment` WHERE {$query}");
		}
    }
}

?>
