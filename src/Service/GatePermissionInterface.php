<?php declare(strict_types=1);

namespace App\Service;

use App\ValueObject\ActionType;

interface GatePermissionInterface
{
    public function has(ActionType $type, string $vehicleNumber): bool;
}
