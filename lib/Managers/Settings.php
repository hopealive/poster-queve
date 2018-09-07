<?php

/**
 * Settings manager block
 *
 * @author gregzorb
 */
class Settings extends Crud
{
    public function getSettings()
    {
        return $this->db->query("select * from settings");
    }

    public function create()
    {

        if (isset($_POST['save'])) {
            $alias        = $_POST['alias'];
            $value        = $_POST['value'];
            $current_date = $this->currentDate();

            $create = $this->db->query("INSERT INTO settings VALUES (null, :alias , :value, :current_date)",
                array("alias" => "$alias", "value" => "$value", "current_date" => "$current_date"));
            echo "<script>alert('Запис додано');</script>";
            echo "<script>document.location.replace('?action=settings');</script>";
        }
    }

    public function update()
    {
        if (isset($_POST['update'])) {
            $id           = $_REQUEST['id'];
            $alias        = $_POST['alias'];
            $value        = $_POST['value'];
            $current_date = $this->currentDate();


            $update       = $this->db->query("UPDATE settings SET alias = :alias, value = :value, created_time = :current_date WHERE id = :id",
                array("alias" => "$alias", "value" => "$value", "current_date" => "$current_date",
                "id" => "$id"));
            echo "<script>alert('Інформація оновлена');</script>";
            echo "<script>document.location.replace('?action=settings');</script>";
        }
    }

    public function getSettingById($id)
    {
        $setting = $this->db->query("SELECT * FROM settings WHERE id = :id",
            array("id" => (int) $id));
        if ($setting) return $setting[0];
        return false;
    }

    public function delete($id)
    {
        $delete = $this->db->query("DELETE FROM settings WHERE id = :id",
            array("id" => $id));
        echo "<script>alert('Запис видалено');</script>";
        echo "<script>document.location.replace('?action=settings');</script>";
    }

}