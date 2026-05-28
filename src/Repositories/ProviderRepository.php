<?php

namespace App\Repositories;

use App\Entities\Provider;

class ProviderRepository extends Repository
{
    protected static string $tableName = "provider";

    public static function getByID(int $provider_id): Provider
    {
        $data = self::select(['id' => $provider_id])[0];
        return self::convertToProvider($data);
    }

    private static function convertToProvider(object $data): Provider
    {
        return new Provider(
            $data->id,
            $data->name,
        );
    }
}
