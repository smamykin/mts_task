<?php declare(strict_types=1);

namespace App\Model;

use App\Exception\OutOfBoundsException;
use App\ValueObject\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @see ActionPermissionFactoryInterface
 */
class ActionPermissionFactory implements ActionPermissionFactoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function create(ActionType $type): ActionPermissionInterface
    {
        switch ((string)$type) {
            case ActionType::IN:
                return new InActionPermission($this->em, $this->logger);
            case ActionType::OUT:
                return new OutActionPermission($this->em, $this->logger);
            default:
                throw new OutOfBoundsException(
                    'В ' . __CLASS__ . ' не реализовано создание обработчика для типе ' . (string)$type
                );
        }
    }
}
