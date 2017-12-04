<?php

/**
 * Created by PhpStorm.
 * User: belik
 * Date: 30.11.17
 * Time: 12:50
 */
class Crud
{
    protected $db = null,
        $log = null;

    public function currentDate()
    {
        date_default_timezone_set('Europe/Kiev');
        $dt = new DateTime();
        return $dt->format('Y-m-d H:i:s');
    }

    public function getSettings()
    {
        $this->db = new DB();
        return $this->db->query("select * from settings");
    }

    public function createSettings()
    {

        if (isset($_POST['save'])) {
            $alias        = $_POST['alias'];
            $value        = $_POST['value'];
            $current_date = $this->currentDate();

            $this->db = new DB();
            $create = $this->db->query("INSERT INTO settings VALUES (null, :alias , :value, :current_date)",
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


            $this->db = new DB();
            $update       = $this->db->query("UPDATE settings SET alias = :alias, value = :value, created_time = :current_date WHERE id = :id",
                array("alias" => "$alias", "value" => "$value", "current_date" => "$current_date",
                "id" => "$id"));
            echo "<script>alert('Информация обновлена');</script>";
            echo "<script>document.location.replace('?action=settings');</script>";
        }
    }

    public function getSettingById($id)
    {
        $this->db = new DB();
        $setting = $this->db->query("SELECT * FROM settings WHERE id = :id",
            array("id" => (int) $id));
        if ($setting) return $setting[0];
        return false;
    }

    public function deleteSettings($id)
    {
        $this->db = new DB();
        $delete = $this->db->query("DELETE FROM settings WHERE id = :id",
            array("id" => $id));
        echo "<script>alert('Запись удалена');</script>";
        echo "<script>document.location.replace('?action=settings');</script>";
    }

    public function getUsers()
    {
        $this->db = new DB();
        $users = $this->db->query("select * from users");
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

        $this->db = new DB();
        $create = $this->db->query("INSERT INTO users VALUES (NULL , :email, :password)",
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
                $this->db = new DB();
                $update = $this->db->query("UPDATE users SET email = :email, password = :pass WHERE id = :id",
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
            
            $this->db = new DB();
            $delete = $this->db->query("DELETE FROM users WHERE id = :id",
                array("id" => $id));
            echo "<script>alert('Пользователь удален');</script>";
            echo "<script>document.location.replace('?action=users');</script>";
        }
    }

    protected function isValidUser()
    {
        if (empty($_POST)) {
            return array('status' => 'error', 'message' => 'Введены пустые данные');
        }

        if (empty($_POST['email'])) {
            return array('status' => 'error', 'message' => 'Введен пустой email');
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            return array('status' => 'error', 'message' => 'Имя пользователя не является email"ом');
        }

        if (empty($_POST['password'])) {
            return array('status' => 'error', 'message' => 'Не введен пароль');
        }

        if (strlen($_POST['password']) < 8) {
            return array('status' => 'error', 'message' => 'Пароль должен быть не менее 8 символов');
        }

        return array('status' => 'ok');
    }
    ###
    #Slider block
    ###

    public function getSliderImages()
    {
        $this->db = new DB();
        return $this->db->query("select id, src from slider ORDER BY position ASC");
    }

    public function uploadSliderImage()
    {
        $this->log = new Log();
        
        $uploaddir = ROOT.DS.'images';
        foreach ($_FILES["multimedia-upload"]["error"] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES["multimedia-upload"]["tmp_name"][$key];
                $name     = basename($_FILES["multimedia-upload"]["name"][$key]);
                $src = $uploaddir.DS.$name;
                if (!move_uploaded_file($tmp_name, $src)){
                    $this->log->write('move_uploaded_file');
                    return false;
                }
                if (!$this->saveSliderImage($name)){
                    $this->log->write('saveSliderImage');
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
        $this->db = new DB();
        return $this->db->query("INSERT INTO slider VALUES (NULL , :position, :src)",
            array("position" => 1, "src" => "$name"));
    }

    public function deleteSliderImage($id)
    {
        $this->db = new DB();
         return $this->db->query("DELETE FROM slider WHERE id = :id", array("id" => $id));
    }
}