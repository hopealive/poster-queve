<?php
/**
 * Users manager
 *
 * @author gregzorb
 */
class Users extends Crud
{
    public function getAll()
    {
        return $this->db->query("select * from users");
    }

    public function create()
    {
        $valid = $this->isValidUser();
        if ($valid['status'] !== 'ok') {
            $flashMessage = "Помилка. Спробуйте пізніше";
            if (isset($valid['message'])) {
                $flashMessage = $valid['message'];
            }
            echo "<script>alert('".$flashMessage."');</script>";
            return false;
        }

        $email = $_POST['email'];
        $pass  = md5($_POST['password']);

        $create = $this->db->query("INSERT INTO users VALUES (NULL , :email, :password)",
            array("email" => "$email", "password" => "$pass"));
        echo "<script>alert('Користувача створено');</script>";
        echo "<script>document.location.replace('?action=users');</script>";
    }

    public function edit()
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
                $update = $this->db->query("UPDATE users SET email = :email, password = :pass WHERE id = :id",
                    array("email" => "$email", "pass" => "$pass", "id" => "$id"));
                echo "<script>alert('Інформація оновлена');</script>";
                echo "<script>document.location.replace('?action=users');</script>";
            }
        }
    }

    public function delete()
    {
        if (isset($_POST['id'])) {
            $id     = $_POST['id'];

            $delete = $this->db->query("DELETE FROM users WHERE id = :id",
                array("id" => $id));
            echo "<script>alert('Користувача видалено');</script>";
            echo "<script>document.location.replace('?action=users');</script>";
        }
    }

    protected function isValidUser()
    {
        if (empty($_POST)) {
            return array('status' => 'error', 'message' => 'Введені пусті дані');
        }

        if (empty($_POST['email'])) {
            return array('status' => 'error', 'message' => 'Введено пустий email');
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            return array('status' => 'error', 'message' => 'Ім\'я користувача не є email"ом');
        }

        if (empty($_POST['password'])) {
            return array('status' => 'error', 'message' => 'Не введено пароль');
        }

        if (strlen($_POST['password']) < 8) {
            return array('status' => 'error', 'message' => 'Пароль має бути не менше 8 символів');
        }

        return array('status' => 'ok');
    }


}