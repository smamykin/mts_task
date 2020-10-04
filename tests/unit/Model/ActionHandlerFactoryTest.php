<?php declare(strict_types=1);

namespace App\Tests\unit\Model;

use App\Model\ActionHandlerFactory;
use App\Model\ActionPermissionFactoryInterface;
use App\Model\InActionHandler;
use App\Model\OutActionHandler;
use App\ValueObject\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ActionHandlerFactoryTest extends TestCase
{
    public function testCreateInActionHandler()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $permissionFactory = $this->createMock(ActionPermissionFactoryInterface::class);
        $obj = new ActionHandlerFactory($permissionFactory, $em);
        $this->assertInstanceOf(
            InActionHandler::class,
            $obj->create(new ActionType(ActionType::IN))
        );
    }
    public function testCreateOutActionHandler()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $permissionFactory = $this->createMock(ActionPermissionFactoryInterface::class);
        $obj = new ActionHandlerFactory($permissionFactory, $em);
        $this->assertInstanceOf(
            OutActionHandler::class,
            $obj->create(new ActionType(ActionType::OUT))
        );
    }
}
