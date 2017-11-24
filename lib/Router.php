<?php
/**
 * Description of Router
 *
 * @author gregzorb
 */
class Router
{

    

    public static function redirect($location){
        header("Location: $location");
    }

}