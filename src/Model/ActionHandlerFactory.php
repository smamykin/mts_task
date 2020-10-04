<?php declare(strict_types=1);

namespace App\Model;

use App\Exception\OutOfBoundsException;
use App\ValueObject\ActionType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @see ActionHandlerFactoryInterface
 */
class ActionHandlerFactory implements ActionHandlerFactoryInterface
{
    /**
     * @var ActionPermissionFactoryInterface
     */
    private $permissionFactory;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(ActionPermissionFactoryInterface $permissionFactory, EntityManagerInterface $em)
    {
        $this->permissionFactory = $permissionFactory;
        $this->em = $em;
    }

    /**
     * @param ActionType $type
     * @return ActionHandlerInterface
     */
    public function create(ActionType $type): ActionHandlerInterface
    {
        switch ((string)$type) {
            case ActionType::IN:
                return new InActionHandler($this->permissionFactory, $this->em);
            case ActionType::OUT:
                return new OutActionHandler($this->permissionFactory, $this->em);
            default:
                throw new OutOfBoundsException(
                    'В ' . __CLASS__ . ' не реализовано создание обработчика для типе ' . (string)$type
                );
        }
    }
}
