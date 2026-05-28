<?php

namespace App\Controllers;

use App\Helpers\JWT;
use App\Repositories\RecommendationRepository;
use App\Repositories\UserRepository;
use App\Repositories\BookmarkRepository;

class MySpaceController
{
    public static function index(): void
    {
        if (!JWT::isLoggedIn()) {
            $_SESSION['error'] = "You're not logged in";
            header("Location: /login");
            exit;
        }

        $payload = JWT::decode_jwt($_COOKIE['JWT'], $_ENV['JWT_SECRET']);
        $username = $payload['user'];
        $userId = $payload['user_id'];

        $activeTab = $_GET['tab'] ?? 'dashboard';
        $recommendedProducts = RecommendationRepository::getRecommendationsForUser($userId, 6);
        $bookmarks = BookmarkRepository::getUserBookmarks($userId);


        require __DIR__ . '/../../views/pages/myspace.php';
    }

    /**
     * Handles POST /myspace/update
     */
    public static function updateProfile(): void
    {
        if (!JWT::isLoggedIn()) {
            header("Location: /");
            exit;
        }

        $payload = JWT::decode_jwt($_COOKIE['JWT'], $_ENV['JWT_SECRET']);
        $userId = $payload['user_id'];

        $newUsername = trim($_POST['username'] ?? '');
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if (empty($newUsername)) {
            $_SESSION['error'] = "Username cannot be empty";
            header("Location: /myspace?tab=settings");
            exit;
        }

        if (empty($oldPassword) || empty($newPassword)) {
            $_SESSION['error'] = "Both current and new password fields are required to update settings.";
            header("Location: /myspace?tab=settings");
            exit;
        }

        try {
            $userObj = UserRepository::getUserById($userId);
            if (!$userObj) {
                throw new \Exception("User profile record not found.");
            }

            if (!password_verify($oldPassword, $userObj->getPassword())) {
                $_SESSION['error'] = "Incorrect current password.";
                header("Location: /myspace?tab=settings");
                exit;
            }

            UserRepository::updateProfileDetails($userId, $newUsername, $newPassword);
            $_SESSION['success'] = "Profile updated successfully!";

        } catch (\Exception $e) {
            $_SESSION['error'] = "Failed to update profile: " . $e->getMessage();
        }

        header("Location: /myspace?tab=settings");
        exit;
    }
}
