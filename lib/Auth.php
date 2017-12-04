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
    protected $db = null;


    public function getLogins()
    {
        $this->db = new DB();
        $logins = $this->db->query("select id, email From users WHERE 1");

        $result = array();
        if(empty($logins)){
            $result[] = self::ROOT_LOGIN;
        } else {
            foreach ( $logins as $l ){
                $result[ $l['id'] ] = $l['email'];
            }
        }
        return $result;
    }

    protected function getPassById($id)
    {
        $this->db = new DB();
        $passowrd = $this->db->query("select password From users WHERE id = $id limit 1;");
        if(!empty($passowrd)){
            return $passowrd[0]['password'];
        }
        return false;
    }


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
        if (!isset($request['email']) || !in_array($request['email'], $this->getLogins()) ){
            $this->logOut();
            return false;
        }
        
        if (!isset($request['password']) ){
            $this->logOut();
            return false;
        }

        if ( $request['email'] == self::ROOT_LOGIN AND
            md5($request['password']) == self::ROOT_PASSWORD ){
            $_SESSION['admin_id'] = 0;
            $_SESSION['authorized'] = 1;
            return true;
        }

        $id = (int)array_search($request['email'], $this->getLogins() );
        if ($id > 0 && md5($request['password']) == $this->getPassById($id) ){
            $_SESSION['admin_id'] = $id;
            $_SESSION['authorized'] = 1;
            return true;
        }

        $this->logOut();
        return false;
    }


}