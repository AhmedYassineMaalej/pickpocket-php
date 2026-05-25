<?php

use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\LogoutController;
use App\Controllers\Auth\SignUpController;
use App\Controllers\BookmarksController;
use App\Controllers\CatalogController;
use App\Controllers\HomeController;
use App\Controllers\MySpaceController;
use App\Helpers\Env;
use App\Router;

include_once '../src/autoloader.php';

Env::load_variables();

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router = new Router();

define_user_endpoints($router);
define_apis($router);

function define_apis(Router $router)
{
    $router->add('GET', '/catalog/getProductAjax', CatalogController::getProductAjax(...));
    $router->add('GET', '/bookmarks/items', BookmarksController::getBookmarksJson(...));
    $router->add('POST', '/bookmarks/add', BookmarksController::addBookmark(...));
    $router->add('POST', '/bookmarks/remove', BookmarksController::removeBookmark(...));
}

function define_user_endpoints(Router $router)
{
    $router->add('GET', '/', HomeController::index(...));
    $router->add('ANY', '/catalog', CatalogController::index(...));
    $router->add('GET', '/myspace', MySpaceController::index(...));
<<<<<<< HEAD
    
    // Added to catch the form submission
    $router->add('POST', '/myspace/update', MySpaceController::updateProfile(...));
    
    $router->add('GET', '/navbar', NavbarController::index(...));
    $router->add('ANY','/login', LoginController::index(...));
    $router->add('ANY','/logout', LogoutController::index(...));
    $router->add('ANY','/signup', SignUpController::index(...));
=======
    $router->add('GET', '/bookmarks', BookmarksController::index(...));
    $router->add('ANY', '/login', LoginController::index(...));
    $router->add('ANY', '/logout', LogoutController::index(...));
    $router->add('ANY', '/signup', SignUpController::index(...));
>>>>>>> AYM
}

$router->dispatch($uri, $method);
