<?php declare(strict_types=1);

namespace App\Model;

interface ActionPermissionInterface
{
    public function has(string $vehicleNumber): bool;
}
