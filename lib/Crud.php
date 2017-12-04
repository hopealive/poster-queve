<?php

/**
 * Created by PhpStorm.
 * User: belik
 * Date: 30.11.17
 * Time: 12:50
 */
class Crud
{

    public function currentDate()
    {
        date_default_timezone_set('Europe/Kiev');
        $current_date = (new DateTime())->format('Y-m-d H:i:s');
        return $current_date;
    }

    public function getSettings()
    {
        return (new Db())->query("select * from settings");
    }

    public function createSettings()
    {

        if (isset($_POST['save'])) {
            $alias        = $_POST['alias'];
            $value        = $_POST['value'];
            $current_date = $this->currentDate();
            $create       = (new Db())->query("INSERT INTO settings VALUES (null, :alias , :value, :current_date)",
                array("alias" => "$alias", "value" => "$value", "current_date" => "$current_date"));
            echo "<script>alert('Запись добавлена');</script>";
            echo "<script>document.location.replace('?action=settings');</script>";
        }
    }

    public function updateSettings()
    {
        if (isset($_POST['update'])) {
            $id           = $_REQUEST['id'];
            $alias        = $_POST['alias'];
            $value        = $_POST['value'];
            $current_date = $this->currentDate();
            $update       = (new Db())->query("UPDATE settings SET alias = :alias, value = :value, created_time = :current_date WHERE id = :id",
                array("alias" => "$alias", "value" => "$value", "current_date" => "$current_date",
                "id" => "$id"));
            echo "<script>alert('Информация обновлена');</script>";
            echo "<script>document.location.replace('?action=settings');</script>";
        }
    }

    public function getSettingById($id)
    {
        $setting = (new Db())->query("SELECT * FROM settings WHERE id = :id",
            array("id" => (int) $id));
        if ($setting) return $setting[0];
        return false;
    }

    public function deleteSettings($id)
    {
        $delete = (new Db())->query("DELETE FROM settings WHERE id = :id",
            array("id" => $id));
        echo "<script>alert('Запись удалена');</script>";
        echo "<script>document.location.replace('?action=settings');</script>";
    }

    public function getUsers()
    {
        $users = (new Db())->query("select * from users");
        return $users;
    }

    public function createUser()
    {
        $valid = $this->isValidUser();
        if ($valid['status'] !== 'ok') {
            $flashMessage = "Ошибка. Попробуйте позже";
            if (isset($valid['message'])) {
                $flashMessage = $valid['message'];
            }
            echo "<script>alert('".$flashMessage."');</script>";
            return false;
        }

        $email = $_POST['email'];
        $pass  = md5($_POST['password']);

        $create = (new Db)->query("INSERT INTO users VALUES (NULL , :email, :password)",
            array("email" => "$email", "password" => "$pass"));
        echo "<script>alert('Пользователь создан');</script>";
        echo "<script>document.location.replace('?action=users');</script>";
    }

    public function editUser()
    {
        if (!empty($_GET['id'])) {
            $id = $_REQUEST['id'];
        }

        if (!empty($_POST)) {

            $email = $_POST['email'];
            $pass  = md5($_POST['password']);
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
                $update = (new Db())->query("UPDATE users SET email = :email, password = :pass WHERE id = :id",
                    array("email" => "$email", "pass" => "$pass", "id" => "$id"));
                echo "<script>alert('Информация обновлена');</script>";
                echo "<script>document.location.replace('?action=users');</script>";
            }
        }
    }

    public function deleteUser()
    {
        if (isset($_POST['id'])) {
            $id     = $_POST['id'];
            $delete = (new Db())->query("DELETE FROM users WHERE id = :id",
                array("id" => $id));
            echo "<script>alert('Пользователь удален');</script>";
            echo "<script>document.location.replace('?action=users');</script>";
        }
    }

    protected function isValidUser()
    {
        if (empty($_POST)) {
            return ['status' => 'error', 'message' => 'Введены пустые данные'];
        }

        if (empty($_POST['email'])) {
            return ['status' => 'error', 'message' => 'Введен пустой email'];
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Имя пользователя не является email"ом'];
        }

        if (empty($_POST['password'])) {
            return ['status' => 'error', 'message' => 'Не введен пароль'];
        }

        if (strlen($_POST['password']) < 8) {
            return ['status' => 'error', 'message' => 'Пароль должен быть не менее 8 символов'];
        }

        return ['status' => 'ok'];
    }
    ###
    #Slider block
    ###

    public function getSliderImages()
    {
        return (new Db())->query("select id, src from slider ORDER BY position ASC");
    }

    public function uploadSliderImage()
    {
        $uploaddir = ROOT.DS.'images';
        foreach ($_FILES["multimedia-upload"]["error"] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES["multimedia-upload"]["tmp_name"][$key];
                $name     = basename($_FILES["multimedia-upload"]["name"][$key]);
                $src = $uploaddir.DS.$name;
                if (!move_uploaded_file($tmp_name, $src)){
                    (new Log)->write('move_uploaded_file');
                    return false;
                }
                if (!$this->saveSliderImage($name)){
                    (new Log)->write('saveSliderImage');
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    protected function saveSliderImage($name)
    {
        return (new Db)->query("INSERT INTO slider VALUES (NULL , :position, :src)",
            array("position" => 1, "src" => "$name"));
    }

    public function deleteSliderImage($id)
    {
         return (new Db())->query("DELETE FROM slider WHERE id = :id", array("id" => $id));
    }
}