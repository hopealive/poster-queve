<?php

/**
 * Created by PhpStorm.
 * User: belik
 * Date: 30.11.17
 * Time: 12:50
 */

require_once(ROOT.DS.'lib/Managers/Users.php');
require_once(ROOT.DS.'lib/Managers/Settings.php');
require_once(ROOT.DS.'lib/Managers/Orders.php');
require_once(ROOT.DS.'lib/Managers/OrderHistory.php');

class Crud extends DB
{
    protected $db = null,
        $log = null;

    function __construct()
    {
        $this->db = new DB();
        parent::__construct();
    }

    public function currentDate()
    {
        date_default_timezone_set('Europe/Kiev');
        $dt = new DateTime();
        return $dt->format('Y-m-d H:i:s');
    }

    ###
    #Slider block
    ###

    public function getSliderImages()
    {
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