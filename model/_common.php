<?php

class model__common
{
    private $class;
    
    public function __construct($class)
    {
		$this->class = $class;
    }
    
    protected function _get($query)
    {
        $result = $this->query($query);
        $row    = mysql_fetch_assoc($result);

		mysql_free_result($result);
	
		return ($row === false) ? new stdClass() : $this->setObject($row);
    }
    
    protected function _gets($query)
    {
        $objects = array();
        $result  = $this->query($query);

        if(count($result) == 0) return $objects;

        while($row = mysql_fetch_assoc($result)) $objects[] = $this->setObject($row);

		mysql_free_result($result);
	
        return $objects;
    }
    
    protected function _set($query)
    {
        $this->query($query);
        
		return mysql_insert_id();
    }
    
    protected function _delete($query)
    {
        $this->query($query);
    }
    
    protected function query($query)
    {
        $result = mysql_query($query) or $this->showError($query, mysql_errno(), mysql_error());
        
		return $result;
    }
    
    private function setObject($row)
    {	
		$object = new $this->class();
	
        foreach(get_object_vars($object) as $key => $value) $object->$key = isset($row[$key]) ? $row[$key] : NULL;

        return $object;
    }
    
    private function showError($query, $errno, $error)
    {
        die('<font color="#FF0000"><b>'.$errno.'</font> - '.$error.'</b><br><br>'.$query);
    }
}

?>
