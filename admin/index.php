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

require_once('../lib/Auth.php');
require_once('../lib/View.php');
require_once('../lib/Router.php');

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
        break;
    case "profile":
        break;
    case "create":
        break;
    case "update":
        break;
    case "delete":
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
