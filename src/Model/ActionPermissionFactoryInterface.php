<?php declare(strict_types=1);

namespace App\Model;

use App\ValueObject\ActionType;

/**
 * Создает экземпляр класса для определения "возможно ли совершить то или иное действие" ТС.
 */
interface ActionPermissionFactoryInterface
{
    public function create(ActionType $type): ActionPermissionInterface;
}
