<?php

/**
 * Description of Auth
 *
 * @author gregzorb
 */

include ('Db.php');
class Auth
{

    const ROOT_LOGIN = 'root@gmail.com';
    const ROOT_PASSWORD = 'fe01ce2a7fbac8fafaed7c982a04e229';

    public function login()
    {
        $request = $_REQUEST['email'];
        $db = (new DB())->query("select email From users WHERE email = '$request' limit 1;");
        if(!empty($db)){

            return $db[0]['email'];
        }
        return false;
    }


    public function pass()
    {
        $email = $_REQUEST['email'];
        $pass = md5($_REQUEST['password']);
        $db = (new DB())->query("select password From users WHERE password = '$pass' and email = '$email' limit 1;");
        if(!empty($db)){
            return $db[0]['password'];
        }
        return false;
    }


    public function loggedIn()
    {
        if ( !isset($_SESSION['authorized']) ){
           // var_dump($this->login()); var_dump($this->pass()); die;
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
        if (!isset($request['email']) || $request['email'] !=  ($this->login() || self::ROOT_LOGIN )){
            $this->logOut();

            return false;
        }
        if (!isset($request['password']) || md5($request['password']) != ($this->pass() || self::ROOT_PASSWORD )){
            $this->logOut();

            return false;


        }

        if ( $request['email'] == self::ROOT_LOGIN AND
            md5($request['password']) == self::ROOT_PASSWORD ){
            $_SESSION['authorized'] = 1;
            return true;
        }

        if ( $request['email'] == $this->login()  AND
         md5($request['password']) == $this->pass()) {
            $_SESSION['authorized'] = 1;
            return true;
        }




        $this->logOut();
        return false;

    }


}