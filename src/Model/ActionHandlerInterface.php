<?php declare(strict_types=1);

namespace App\Model;

interface ActionHandlerInterface
{
    public function do(string $vehicleNumber): void;
}
