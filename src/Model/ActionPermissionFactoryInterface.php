<?php declare(strict_types=1);

namespace App\Model;

use App\ValueObject\ActionType;

interface ActionPermissionFactoryInterface
{
    public function create(ActionType $type): ActionPermissionInterface;
}
