<?php

namespace App\Controllers\Auth;

use App\Repositories\UserRepository;
use App\Helpers\JWT;
use App\Helpers\CSRF;

class LoginController
{
    public static function index()
    {
        if (JWT::isLoggedIn()) {
            $_SESSION['error'] = "You're already logged in !";
            header('Location: /myspace');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return self::authenticate();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return self::show_login_form();
        } else {
            header('HTTP/1.1 405 Method Not Allowed');
            echo "Method Not Allowed";
            exit;
        }

    }

    public static function authenticate()
    {

        // validate that CSRF Token if it exists ofc
        $csrf_token = $_POST['csrf'] ?? '';
        if (! CSRF::validate_token($csrf_token ?? '')) {
            $_SESSION['error'] = 'Invalid security token. Please try again.';
            header('Location: /login');
            exit;
        }
        // get username and password from POST data

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Please fill out all the fields';
            header('Location: /login');
            exit;
        }
        $user = UserRepository::getUserByUsername($username);
        error_log(print_r($user->getUsername(), true));
        error_log(print_r($user->getPassword(), true));

        if ($user && password_verify($password, $user->getPassword())) {
            $jwt_cookie = JWT::issue_jwt($username, $user->getId());
            setcookie("JWT", $jwt_cookie, time() + 3600, "/", "", false, true);
            error_log("JWT issued: " . $jwt_cookie);
            header('Location: /myspace');
            exit;
        } else {
            $_SESSION['error'] = 'Invalid credentials';
            header('Location: /login');
            exit;
        }
    }

    public static function show_login_form()
    {
        $csrf_token = CSRF::generate_token();
        $_SESSION['csrf_token'] = $csrf_token;
        require __DIR__ . '/../../../views/pages/login.php';
    }
}
