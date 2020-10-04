<?php declare(strict_types=1);

namespace App\Model;

use App\Exception\PermissionException;
use App\ValueObject\ActionType;
use Doctrine\Persistence\ObjectManager;

/**
 * Абстрактный класс позволяет сократить дублирование при проверки прав на совершение ТС "действие"
 */
abstract class AbstractActionHandler implements ActionHandlerInterface
{
    /**
     * @var ActionPermissionFactoryInterface
     */
    private $permissionFactory;
    /**
     * @var ObjectManager
     */
    private $em;

    final public function __construct(
        ActionPermissionFactoryInterface $permissionFactory,
        ObjectManager $em
    )
    {
        $this->permissionFactory = $permissionFactory;
        $this->em = $em;
    }

    final public function do(string $vehicleNumber): void
    {
        $result = $this->permissionFactory
            ->create($this->getActionType())
            ->has($vehicleNumber);

        if (!$result) {
            throw new PermissionException('Нет прав на совершение действия.');
        }

        $this->doAction($vehicleNumber);
    }

    /**
     * @param string $vehicleNumber
     */
    protected abstract function doAction(string $vehicleNumber): void;

    /**
     * @return ActionType
     */
    protected abstract function getActionType(): ActionType;

    /**
     * @return ObjectManager
     */
    protected function getEm(): ObjectManager
    {
        return $this->em;
    }
}
