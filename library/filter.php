<?php

class library_filter 
{
    public function __construct() 
    {
        $this->tagBlacklist = array ('applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml');
        $this->attrBlacklist = array ('action', 'background', 'codebase', 'dynsrc', 'lowsrc');
        $this->tagsArray = array('br', 'b', 'i');
        $this->attrArray = array();
        $this->tagsMethod = 0;
        $this->attrMethod = 0;
        $this->xssAuto	= 1;    

        $_GET     = $this->run($_GET);
        $_POST    = $this->run($_POST);
        $_REQUEST = $this->run($_REQUEST);
        $_SESSION = $this->run($_SESSION);
        $_COOKIE  = $this->run($_COOKIE);
        $_FILES   = $this->run($_FILES);
    }

    public function run($input) 
    {
        if(is_array($input)) {

            foreach ($input as $key => $value) 
            {
                if(is_string($key)) 
                {
                    $key = self::remove($key);
                    $key = self::quoteSmart($key);
                    $key = self::decode($key);
                }

                if (is_string($value)) 
                {
                    $value = self::remove(nl2br($value));
                    $value = self::quoteSmart($value);
                    $value = self::decode($value);
                    $input[$key] = $value;
                }

            }

            return $input;

        } 
        elseif(is_string($input)) 
        {
            $input = self::remove(nl2br($input));
            $input = self::quoteSmart($input);
            $input = self::decode($input);

            return $input;
        } 
        else return nl2br($input); 

    }

    private function remove($input) 
    {
        while ($input != self::filterTags($input)) { $input = self::filterTags($input); }

        return $input;
    }

    private function filterTags($input) 
    {
        $preTag = NULL;
        $postTag = $input;

        $openTag = strpos($input, '<');

        while ($openTag !== false) 
        {
            $preTag .= substr($postTag, 0, $openTag);
            $postTag = substr($postTag, $openTag);
            $tagOpen = substr($postTag, 1);
            $tagEnd = strpos($tagOpen, '>');

            if ($tagEnd === false) 
            {
                $postTag = substr($postTag, $openTag +1);
                $openTag = strpos($postTag, '<');
                continue;
            }

            $nestedTagOpen = strpos($tagOpen, '<');
            $nestedTagEnd = strpos(substr($postTag, $tagEnd), '>');
            
            if (($nestedTagOpen !== false) && ($nestedTagOpen < $tagEnd)) 
            {
                    $preTag .= substr($postTag, 0, ($nestedTagOpen +1));
                    $postTag = substr($postTag, ($nestedTagOpen +1));
                    $openTag = strpos($postTag, '<');
                    continue;
            }

            $nestedTagOpen = (strpos($tagOpen, '<') + $openTag +1);
            $currentTag	= substr($tagOpen, 0, $tagEnd);
            $tagLength = strlen($currentTag);
            $tagLeft = $currentTag;
            $attrSet = array ();
            $currentSpace = strpos($tagLeft, ' ');

            if (substr($currentTag, 0, 1) == "/") 
            {
                $isCloseTag = true;
                list ($tagName)	= explode(' ', $currentTag);
                $tagName = substr($tagName, 1);    
            } 
            else 
            {
                $isCloseTag = false;
                list ($tagName)	= explode(' ', $currentTag);   
            }

            if ((!preg_match("/^[a-z][a-z0-9]*$/i", $tagName)) || (!$tagName) || ((in_array(strtolower($tagName), $this->tagBlacklist)) && ($this->xssAuto))) 
            {
                $postTag = substr($postTag, ($tagLength +2));
                $openTag = strpos($postTag, '<');
                continue;        
            }

            while ($currentSpace !== false) 
            {
                $fromSpace = substr($tagLeft, ($currentSpace +1));
                $nextSpace = strpos($fromSpace, ' ');
                $openQuotes = strpos($fromSpace, '"');
                $closeQuotes = strpos(substr($fromSpace, ($openQuotes +1)), '"') + $openQuotes +1;

                if (strpos($fromSpace, '=') !== false) 
                {
                    if (($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes +1)), '"') !== false)) { $attr = substr($fromSpace, 0, ($closeQuotes +1)); } 
                    else                                                                                          { $attr = substr($fromSpace, 0, $nextSpace); }

                } 
                else { $attr = substr($fromSpace, 0, $nextSpace); }

                if (!$attr) { $attr = $fromSpace; }

                $attrSet[] = $attr;

                $tagLeft = substr($fromSpace, strlen($attr));
                $currentSpace = strpos($tagLeft, ' ');

            }

            $tagFound = in_array(strtolower($tagName), $this->tagsArray);

            if ((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod)) 
            {
                if (!$isCloseTag) 
                {
                    $attrSet = $this->filterAttr($attrSet);
                    $attrSetLength = count($attrSet);
                    $preTag .= '<'.$tagName;
                    
                    for($i = 0; $i < $attrSetLength; $i ++) { $preTag .= ' '.$attrSet[$i]; }

                    if (strpos($tagOpen, "</".$tagName)) { $preTag .= '>'; } 
                    else                                 { $preTag .= ' />'; }

                } else { $preTag .= '</'.$tagName.'>'; }

            }

            $postTag = substr($postTag, ($tagLength +2));
            $openTag = strpos($postTag, '<');

        }

        if ($postTag != '<') { $preTag .= $postTag; }
        
        return $preTag;
        
    }

    private function filterAttr($attrSet) 
    {
        $newSet = array ();
        $attrSetLength = count($attrSet);
        for($i = 0; $i < $attrSetLength; $i ++) 
        {
            if (!$attrSet[$i]) { continue; }

            $attrSubSet = explode('=', trim($attrSet[$i]), 2);
            list ($attrSubSet[0]) = explode(' ', $attrSubSet[0]);

            if ((!preg_match("#^[a-z]*$#i", $attrSubSet[0])) || (($this->xssAuto) && ((in_array(strtolower($attrSubSet[0]), $this->attrBlacklist)) || (substr($attrSubSet[0], 0, 2) == 'on')))) { continue; }

            if ($attrSubSet[1]) 
            {
                $attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);
                $attrSubSet[1] = preg_replace('/\s+/', '', $attrSubSet[1]);
                $attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);
                
                if ((substr($attrSubSet[1], 0, 1) == "'") && (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'")) 
                {
                    $attrSubSet[1] = substr($attrSubSet[1], 1, (strlen($attrSubSet[1]) - 2));
                }

            }

            if (self::badAttributeValue($attrSubSet)) { continue; }

            $attrFound = in_array(strtolower($attrSubSet[0]), $this->attrArray);

            if ((!$attrFound && $this->attrMethod) || ($attrFound && !$this->attrMethod)) 
            {
                if($attrSubSet[1])            { $newSet[] = $attrSubSet[0].'="'.$attrSubSet[1].'"'; } 
                elseif($attrSubSet[1] == "0") { $newSet[] = $attrSubSet[0].'="0"'; } 
                else                          { $newSet[] = $attrSubSet[0].'="'.$attrSubSet[0].'"'; }       
            }
        }

        return $newSet;
    }

    private function badAttributeValue($attrSubSet) 
    {
        $attrSubSet[0] = strtolower($attrSubSet[0]);
        $attrSubSet[1] = strtolower($attrSubSet[1]);
        
        return (((strpos($attrSubSet[1], 'expression') !== false) && ($attrSubSet[0]) == 'style') || (strpos($attrSubSet[1], 'javascript:') !== false) || (strpos($attrSubSet[1], 'behaviour:') !== false) || (strpos($attrSubSet[1], 'vbscript:') !== false) || (strpos($attrSubSet[1], 'mocha:') !== false) || (strpos($attrSubSet[1], 'livescript:') !== false));
    }

    private function decode($input) 
    {
        $input = htmlentities($input, ENT_QUOTES, "UTF-8");
        $input = str_replace('&lt;br /&gt;', '<br />', $input);

        return $input;
    }

    private function quoteSmart($input) 
    {
        $input = mysql_real_escape_string(trim(stripslashes($input)));
        return $input;
    }
}

?>