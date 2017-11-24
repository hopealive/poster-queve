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

    protected static function getDefaultViewPath()
    {
        $router = App::getRouter();
        if (!$router) {
            return false;
        }

        $role_dir = $router->getRolePrefix();
        $template_name  = $router->getMethodPrefix().$router->getAction().'.html';
        return VIEWS_PATH.DS.$role_dir.DS.$template_name;
    }

    public function __construct($data = array(), $path = null)
    {
        if (!$path) {
//            $path = self::getDefaultViewPath(); //TODO
        }
        if (!file_exists($path)) {
            throw new Exception('Template file is not found in path: '.$path);
        }
        $this->path = $path;
        $this->data = $data;
    }

    public function render()
    {
        $data    = $this->data;
        ob_start();
        include($this->path);
        $content = ob_get_clean();
        return $content;
    }

}