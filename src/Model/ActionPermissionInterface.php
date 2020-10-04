<?php declare(strict_types=1);

namespace App\Model;

/**
 * Реализующий класс должен уметь "решить" можно ли ТС совершить действие, за которое ответственен конкретно этот
 * экземпляр.
 */
interface ActionPermissionInterface
{
    public function has(string $vehicleNumber): bool;
}
