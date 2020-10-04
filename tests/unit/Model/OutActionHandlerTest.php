<?php declare(strict_types=1);

namespace App\Tests\unit\Model;

use App\Entity\Visit;
use App\Exception\PermissionException;
use App\Model\ActionPermissionFactoryInterface;
use App\Model\ActionPermissionInterface;
use App\Model\OutActionHandler;
use DateTimeInterface;
use Doctrine\Persistence\ObjectManager;
use LogicException;
use PHPUnit\Framework\TestCase;

class OutActionHandlerTest extends TestCase
{
    use MockRepositoriesTrait;
    use EntityFixturesTrait;

    public function testHasNoPermission(): void
    {
        $vehicleNumber = uniqid();

        $permission = $this->createMock(ActionPermissionInterface::class);
        $permission->method('has')->willReturn(false);

        $permissionFactory = $this->createMock(ActionPermissionFactoryInterface::class);
        $permissionFactory->method('create')->willReturn($permission);

        $objectManager = $this->createMock(ObjectManager::class);
        $obj = new OutActionHandler($permissionFactory, $objectManager);

        $this->expectException(PermissionException::class);
        $obj->do($vehicleNumber);
    }

    public function testNoVehicle(): void
    {
        $vehicleNumber = uniqid();

        $permission = $this->createMock(ActionPermissionInterface::class);
        $permission->method('has')->willReturn(true);

        $permissionFactory = $this->createMock(ActionPermissionFactoryInterface::class);
        $permissionFactory->method('create')->willReturn($permission);

        $vehicleRep = $this->getVehicleRepository(null, $vehicleNumber);
        $objectManager = $this->getObjectManager($vehicleRep);

        $obj = new OutActionHandler($permissionFactory, $objectManager);

        $this->expectException(LogicException::class);
        $obj->do($vehicleNumber);
    }

    public function testDoVisitNotExists()
    {
        $vehicleNumber = uniqid();

        $permission = $this->createMock(ActionPermissionInterface::class);
        $permission->method('has')->willReturn(true);

        $permissionFactory = $this->createMock(ActionPermissionFactoryInterface::class);
        $permissionFactory->method('create')->willReturn($permission);

        $vehicle = $this->createVehicle();
        $vehicleRep = $this->getVehicleRepository($vehicle, $vehicleNumber);
        $visitRep = $this->getVisitRepository($vehicle, null);
        $objectManager = $this->getObjectManager($vehicleRep, $visitRep);
        $objectManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($value) use ($vehicle) {
                return $value instanceof Visit
                    && $value->getVehicle() === $vehicle
                    && $value->getClosedAt() instanceof DateTimeInterface;
            }));
        $objectManager
            ->expects($this->once())
            ->method('flush');

        $obj = new OutActionHandler($permissionFactory, $objectManager);

        $obj->do($vehicleNumber);
    }

    public function testDoVisitExists(): void
    {
        $vehicleNumber = uniqid();

        $permission = $this->createMock(ActionPermissionInterface::class);
        $permission->method('has')->willReturn(true);

        $permissionFactory = $this->createMock(ActionPermissionFactoryInterface::class);
        $permissionFactory->method('create')->willReturn($permission);

        $vehicle = $this->createVehicle();
        $vehicleRep = $this->getVehicleRepository($vehicle, $vehicleNumber);
        $visit = $this->createVisit($vehicle);
        $visitRep = $this->getVisitRepository($vehicle, $visit);
        $objectManager = $this->getObjectManager($vehicleRep, $visitRep);
        $objectManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($value) use ($vehicle, $visit) {
                return $value === $visit
                    && $value->getVehicle() === $vehicle
                    && $value->getClosedAt() instanceof DateTimeInterface;
            }));
        $objectManager
            ->expects($this->once())
            ->method('flush');

        $obj = new OutActionHandler($permissionFactory, $objectManager);

        $obj->do($vehicleNumber);
    }
}
