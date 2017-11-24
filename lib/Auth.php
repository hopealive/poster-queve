<?php

/**
 * Description of Auth
 *
 * @author gregzorb
 */
class Auth
{

    const ROOT_LOGIN = 'root@gmail.com';
    const ROOT_PASSWORD = 'fe01ce2a7fbac8fafaed7c982a04e229';

    public function loggedIn()
    {
        if ( !isset($_SESSION['authorized']) ){
            return false;
        }

        if ($_SESSION['authorized']===1) {
            return true;
        }
        return false;
    }

    public function logOut()
    {
        if (isset($_SESSION['authorized'])) unset($_SESSION['authorized']);
    }

    public function authenticate($request)
    {
        if (!isset($request['email']) || $request['email'] != self::ROOT_LOGIN){
            $this->logOut();
            return false;
        }
        if (!isset($request['password']) || md5($request['password']) != self::ROOT_PASSWORD ){
            $this->logOut();
            return false;
        }

        if ( $request['email'] == self::ROOT_LOGIN AND
         md5($request['password']) == self::ROOT_PASSWORD ){
            $_SESSION['authorized'] = 1;
            return true;
        }

        $this->logOut();
        return false;
    }


}