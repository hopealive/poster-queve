<?php
error_reporting(E_ALL);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('ADMIN_PATH', dirname(dirname(__FILE__)).DS.'admin');
define('VIEWS_PATH', ROOT.DS.'view');

session_start();


//TODO:
//require_once(ROOT.DS.'lib'.DS.'init.php');
//App::run($_SERVER['REQUEST_URI']);

require_once('../lib/Log.php');
require_once('../lib/Db.php');
require_once('../lib/Auth.php');
require_once ('../lib/Crud.php');
require_once('../lib/View.php');
require_once('../lib/Router.php');
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
    case "settings":
        if(isset($_POST['save'])) {
            $result = $crud->createSettings();
        } elseif (isset($_POST['update'])){
            $result = $crud->updateSettings();
        } elseif (isset($_GET['?edit'])){
            $result = $crud->editSettings();
        } elseif (isset($_GET['?del'])){
            $result = $crud->deleteSettings();
        }
        break;
    case "profile":
        break;
    case "users":
        break;
    case "create":
        if(!empty($_POST)){
            $result = $crud->createUser();
        }
        break;
    case "update":
        if(isset($_GET['?id'])){
            $result = $crud->editUser();
        }
        break;
    case "delete":
        if(isset($_POST['id'])){
            $result = $crud->deleteUser();
        }
        break;
    case "logout":
        $Auth->logout();
        Router::redirect('/admin?action=login');
        break;
}

?>

<!doctype html>
<html lang="en">
    <?php echo (new View([],VIEWS_PATH.DS.'admin/layouts/head.html'))->render(); ?>
    <body>
        <?php if (!$isLoggedIn) echo (new View([],VIEWS_PATH.DS.'admin/login.html'))->render();  ?>

        <?php if ($isLoggedIn) { ?>
            <?php echo (new View([],VIEWS_PATH.DS.'admin/layouts/header-nav.html'))->render(); ?>

            <div class="container-fluid">
                <div class="row">
                    <?php echo (new View([],VIEWS_PATH.DS.'admin/layouts/left-menu.html'))->render(); ?>
                    <main role="main" class="col-sm-9 ml-sm-auto col-md-10 pt-3">
                        <?php echo (new View([],VIEWS_PATH.DS.'admin/'.$action.'.html'))->render(); ?>
                    </main>
                </div>
            </div>
        <?php } ?>

    </body>
</html>
