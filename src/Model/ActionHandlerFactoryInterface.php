<?php declare(strict_types=1);

namespace App\Model;

use App\ValueObject\ActionType;

interface ActionHandlerFactoryInterface
{
    public function create(ActionType $type): ActionHandlerInterface;
}
