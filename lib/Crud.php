<?php
/**
 * Created by PhpStorm.
 * User: belik
 * Date: 30.11.17
 * Time: 12:50
 */
class Crud
{

    public function currentDate(){
        date_default_timezone_set('Europe/Kiev');
        $current_date = (new DateTime())->format('Y-m-d H:i:s');
        return $current_date;
    }

    public function getSettings()
    {
        $settings = (new Db())->query("select * from settings");
        return $settings;
    }

    public function createSettings()
    {

        if (isset($_POST['save'])) {
            $alias = $_POST['alias'];
            $value = $_POST['value'];
            $current_date = $this->currentDate();
            $create = (new Db())->query("INSERT INTO settings VALUES (null, :alias , :value, :current_date)", array("alias"=>"$alias","value"=>"$value", "current_date"=>"$current_date"));
            echo "<script>alert('Запись добавлена');</script>";
            echo "<script>document.location.replace('?action=settings');</script>";
        }
    }

    public function updateSettings(){
        if (isset($_POST['update'])) {
            $id = $_REQUEST['id'];
            $alias = $_POST['alias'];
            $value = $_POST['value'];
            $current_date = $this->currentDate();
            $update = (new Db())->query("UPDATE settings SET alias = :alias, value = :value, created_time = :current_date WHERE id = :id", array("alias"=>"$alias","value"=>"$value", "current_date"=>"$current_date", "id"=>"$id"));
            echo "<script>alert('Информация обновлена');</script>";
            echo "<script>document.location.replace('?action=settings');</script>";
        }
    }

    public function editSettings(){
        if (isset($_GET['?edit'])) {
            $id = $_GET['?edit'];

            $edit = (new Db())->query("SELECT * FROM settings WHERE id = :id", array("id"=>$id));

            if (count($edit) == 1) {
                return $edit;
            }
        }
    }

public function deleteSettings()
    {

    if (isset($_GET['?del'])) {
        $id = $_GET['?del'];
        $delete = (new Db())->query("DELETE FROM settings WHERE id = :id", array("id"=>$id));
        echo "<script>alert('Запись удалена');</script>";
        echo "<script>document.location.replace('?action=settings');</script>";
        }
    }

    public function getUsers()
    {
        $users = (new Db())->query("select * from users");
        return $users;
    }

    public function createUser(){


        if ( !empty($_POST)) {

            $email = $_POST['email'];
            $pass = md5($_POST['password']);
            $valid = true;

            if (empty($email)) {
              $valid = false;
          } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $valid = false;
          }

          if (empty($_POST['password'])) {
              $valid = false;
          } elseif (strlen($_POST['password']) < 6) {
              $valid = false;

          }

            if ($valid) {
                $create = (new Db)->query("INSERT INTO users VALUES (NULL , :email, :password)", array("email"=>"$email", "password"=>"$pass"));
                echo "<script>alert('Пользователь создан');</script>";
                echo "<script>document.location.replace('?action=users');</script>";
            }
        }

    }



    public function editUser(){

        if ( !empty($_GET['?id'])) {
            $id = $_REQUEST['?id'];
        }

        if ( !empty($_POST)) {

            $email = $_POST['email'];
            $pass = md5($_POST['password']);
            $valid = true;

            if (empty($email)) {
                $valid = false;
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valid = false;
            }

            if (empty($_POST['password'])) {
                $valid = false;
            } elseif (strlen($_POST['password']) < 6) {
                $valid = false;

            }

            if ($valid) {
                $update = (new Db())->query("UPDATE users SET email = :email, password = :pass WHERE id = :id", array("email"=>"$email", "pass"=>"$pass", "id"=>"$id"));
                echo "<script>alert('Информация обновлена');</script>";
                echo "<script>document.location.replace('?action=users');</script>";
            }
        }
    }

    public function deleteUser(){
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $delete = (new Db())->query("DELETE FROM users WHERE id = :id", array("id"=>$id));
            echo "<script>alert('Пользователь удален');</script>";
            echo "<script>document.location.replace('?action=users');</script>";

        }
    }
}