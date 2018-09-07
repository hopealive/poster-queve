<?php
error_reporting(E_ALL);
session_start();
date_default_timezone_set('Europe/Kiev');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('ADMIN_PATH', dirname(dirname(__FILE__)).DS.'admin');
define('VIEWS_PATH', ROOT.DS.'view');

require_once(ROOT.DS.'lib'.DS.'App.php');
App::run($_SERVER['REQUEST_URI']);


$crud = new Crud;
$Auth       = new Auth;
$isLoggedIn = $Auth->loggedIn();

//routing
if ($isLoggedIn) {
    $action = 'index';
    if (isset($_GET['action'])) {
        $action = trim($_GET['action']);
    }
} else {
    $action = 'login';
}

switch ($action) {
    case "login":
        $result = $Auth->authenticate($_POST);
        if ( $result ){
            Router::redirect('/admin?action=index');
        }
        break;
    case "slider":
        break;
    case "slider-upload":
        $result = $crud->uploadSliderImage();
        if ( $result ){
            $flashMessage = "Завантажено";
        } else {
            $flashMessage = "Помилка завантаження";
        }
        echo "<script>alert('".$flashMessage."');</script>";
        echo "<script>document.location.replace('?action=slider');</script>";
        return; 
        break;
    case "slider-delete":
        $result = $crud->deleteSliderImage( (int) $_POST['key']);
        $flashMessage = "Помилка видалення";
        if ( $result ){
            $flashMessage = "Файл видалено";
        }
        echo "<script>alert('".$flashMessage."');</script>";
        echo "<script>document.location.replace('?action=slider');</script>";
        return;
        break;
    case "settings":
        if(isset($_POST['save'])) {
            $result = (new Settings)->create();
        } elseif (isset($_POST['update'])){
            $result = (new Settings)->update();
        } elseif (isset($_GET['del'])){
            $result = (new Settings)->delete( (int)$_GET['id'] );
        }
        break;
    case "profile":
        break;
    case "users":
        break;
    case "create":
        if(!empty($_POST)){
            $result = (new Users())->create();
        }
        break;
    case "update":
        if(isset($_GET['id'])){
            $result = (new Users())->edit();
        }
        break;
    case "delete":
        if(isset($_POST['id'])){
            $result = (new Users())->delete();
        }
        break;
    case "logout":
        $Auth->logout();
        Router::redirect('/admin?action=login');
        break;
}

$view = new View(
    'admin/layouts/default.html',
    compact('isLoggedIn', 'action')
);

echo $view->render();
?>

