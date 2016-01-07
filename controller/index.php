<?php

class controller_index extends controller__common 
{
	private $fontFamily;
	private $fontSize;
	private $fontStyle;
	private $fontWeight;
	
    public function __construct() 
    {
        parent::__construct();	
		
        $this->fontFamily = array('Times New Roman', 'Georgia', 'sansSerif', 'Helvetica', 'Tahoma', 'Verdana', 'Lucida Sans Unicode', 'Trebuchet MS', 'Lucida Sans', 'monospace', 'Courier', 'Courier New', 'Lucida Console', 'fantasy', 'cursive');
        $this->fontSize   = array('small', 'medium', 'large', 'x-large', 'xx-large');
        $this->fontStyle  = array('italic', 'oblique', 'normal');
        $this->fontWeight = array('bold', 'bolder', 'lighter', 'normal');
        
        method_exists($this, $this->action) ? $this->{$this->action}() : $this->index();
    }
    
    private function index()
    {
    	$comment  = new model_comment();
    	$comments = model_comment::gets($comment);
    	
    	require_once('view/html/index/index.phtml');
    }
    
    private function setComment()
    {
    	$comment          = new model_comment();
    	$comment->ip      = $_SERVER['REMOTE_ADDR'];
    	$comment->content = parent::request('content');
    	
    	if(trim($comment->content) == '') return;
    	
    	$commentId = model_comment::set($comment);
    	
    	if(isset($commentId) && !empty($commentId))
    	{
    		$comment = model_comment::getByID($commentId);

    		require_once('view/html/index/index.comment.phtml');
    	}
    }
    
    private static function calculateDate($date) 
	{
	    if(empty($date)) return '';
	    
	    $periods   = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade');
	    $lengths   = array('60','60','24','7','4.35','12','10');
	    $now       = time();
	    $unix_date = strtotime($date);
	    
	    if(empty($unix_date)) return '';
	
	    if($now > $unix_date) 
	    {    
	        $difference = $now - $unix_date;
	        $tense      = 'ago';
	    } 
	    else 
	    {
	        $difference = $unix_date - $now;
	        $tense      = 'from now';
	    }
	    
	    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) $difference /= $lengths[$j];
	    
	    $difference = round($difference);
	    
	    if($difference != 1) $periods[$j].= 's';
	    
	    return "$difference $periods[$j] {$tense}";
	}
    
    private function readCSV()
    {
        $i          = 0;
        $prenzlauer = fopen('prenzlauer.csv', 'r');
        
        while($data = fgetcsv($prenzlauer, 1000, ','))
        {
            $i++;
            echo $data[1].','.$data[2].' Berlin Pankow,'.$data[3].'<br>';
            
            if($i === 100) { echo '<br>'; $i = 0; }
        }
    }
}

?>
