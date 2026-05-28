<?php

namespace App\Repositories;

use App\Entities\User;
use Exception;
use PDO;

class UserRepository extends Repository
{
    protected static string $tableName = "users";

    private static function convertToUser(object $data): ?User
    {
        if (!$data) {
            return null;
        }
        return new User(
            $data->ID,
            $data->username,
            $data->Pwd,
            $data->role,
        );
    }

    public static function getUserByUsername(string $username): ?User
    {
        $rows = self::select(["username" => $username]);

        if (count($rows) == 0) {
            return null;
        }

        return self::convertToUser($rows[0]);
    }

    public static function getUserById(int $id): ?User
    {
        $result = self::findById($id);
        return self::convertToUser($result);
    }

    public static function createUser($username, $hashed_password)
    {
        $result = self::insert([
            'username' => $username,
            'Pwd' => $hashed_password,
            'role' => 'user'
        ]);

        if ($result) {
            return self::getUserByUsername($username);
        }

        return false;
    }

    /**
     * Updates a user's username and optionally their password.
     */
    public static function updateProfileDetails(int $userId, string $newUsername, string $newPassword = ''): bool
    {
        try {
            if (empty($newPassword)) {
                $sql = "UPDATE " . static::$tableName . " SET username = ? WHERE id = ?";
                $stmt = self::getConnection()->prepare($sql);
                return $stmt->execute([$newUsername, $userId]);
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $sql = "UPDATE " . static::$tableName . " SET username = ?, Pwd = ? WHERE id = ?";
            $stmt = self::getConnection()->prepare($sql);
            return $stmt->execute([$newUsername, $hashedPassword, $userId]);

        } catch (Exception $e) {
            error_log("Failed to update profile details in UserRepository: " . $e->getMessage());
            throw $e;
        }
    }
}