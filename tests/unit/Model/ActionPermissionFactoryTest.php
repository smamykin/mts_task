<?php declare(strict_types=1);

namespace App\Tests\unit\Model;

use App\Model\ActionPermissionFactory;
use App\Model\InActionPermission;
use App\Model\OutActionPermission;
use App\ValueObject\ActionType;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class ActionPermissionFactoryTest extends TestCase
{
    public function testCreateInActionPermission()
    {
        /** @var ObjectManager $em */
        $em = $this->createMock(ObjectManager::class);
        $obj = new ActionPermissionFactory($em);
        $this->assertInstanceOf(
            InActionPermission::class,
            $obj->create(new ActionType(ActionType::IN))
        );
    }
    public function testCreateOutActionPermission()
    {
        /** @var ObjectManager $em */
        $em = $this->createMock(ObjectManager::class);
        $obj = new ActionPermissionFactory($em);
        $this->assertInstanceOf(
            OutActionPermission::class,
            $obj->create(new ActionType(ActionType::OUT))
        );
    }
}
