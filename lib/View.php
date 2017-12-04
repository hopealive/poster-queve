<?php

/**
 * Description of View
 *
 * @author gregzorb
 */
class View
{
    protected $data;
    protected $path;

    public function __construct($path = null, $data = array())
    {
        $this->path = VIEWS_PATH.DS.$path;
        $this->data = $data;
    }

    public function render($path = "")
    {
        $data = $this->data;
        ob_start();
        if ( !empty($path)){
            $this->path = VIEWS_PATH.DS.$path;
        }
        
        include($this->path);
        $content = ob_get_clean();
        return $content;
    }

}