<?php declare(strict_types=1);

namespace App\Tests\unit\Model;

use App\Model\ActionPermissionFactory;
use App\Model\InActionPermission;
use App\Model\OutActionPermission;
use App\ValueObject\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ActionPermissionFactoryTest extends TestCase
{
    public function testCreateInActionPermission()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $obj = new ActionPermissionFactory($em, new NullLogger());
        $this->assertInstanceOf(
            InActionPermission::class,
            $obj->create(new ActionType(ActionType::IN))
        );
    }
    public function testCreateOutActionPermission()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $obj = new ActionPermissionFactory($em, new NullLogger());
        $this->assertInstanceOf(
            OutActionPermission::class,
            $obj->create(new ActionType(ActionType::OUT))
        );
    }
}
