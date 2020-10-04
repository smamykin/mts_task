<?php declare(strict_types=1);

namespace App\Model;

/**
 * Реализующий класс должен уметь "зафиксировть" действие совершенное ТС. Например, нужно зафиксировать "посещение"
 * после заезда на територию паркинга.
 */
interface ActionHandlerInterface
{
    public function do(string $vehicleNumber): void;
}
