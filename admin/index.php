<?php
error_reporting(E_ALL);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('ADMIN_PATH', dirname(dirname(__FILE__)).DS.'admin');
define('VIEWS_PATH', ROOT.DS.'view');

//require_once(ROOT.DS.'lib'.DS.'init.php');
session_start();
//App::run($_SERVER['REQUEST_URI']);

require_once('../lib/Db.php');
$Db = new Db();
$result = $Db->query('select * from messages');
var_dump ( $result ); die;









require_once('../lib/Auth.php');
require_once('../lib/View.php');

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
            header("Location: /admin?action=index");
        }
        break;
    case "slider":
        break;
    case "setings":
        break;
    case "profile":
        break;
    case "logout":
        $Auth->logout();
        header("Location: /admin?action=login");
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
