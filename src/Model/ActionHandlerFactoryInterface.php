<?php declare(strict_types=1);

namespace App\Model;

use App\ValueObject\ActionType;

/**
 * Созжает "обработчик" для определенного действия - заезда или выезда.
 */
interface ActionHandlerFactoryInterface
{
    public function create(ActionType $type): ActionHandlerInterface;
}
