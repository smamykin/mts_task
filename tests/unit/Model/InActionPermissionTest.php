<?php declare(strict_types=1);

namespace App\Tests\unit\Model;

use App\Model\InActionPermission;
use App\Repository\VisitRepository;
use Psr\Log\NullLogger;

class InActionPermissionTest extends AbstractActionPermissionTestCase
{
    public function testVehicleHasNoVisit()
    {
        $vehicle = $this->createVehicle();

        $vehicleRepository = $this->getVehicleRepository($vehicle, $vehicle->getNumber());
        $visitRepository = $this->getVisitRepository($vehicle);
        $objectManager = $this->getObjectManager($vehicleRepository, $visitRepository);

        $obj = new InActionPermission($objectManager, new NullLogger());

        $this->assertTrue($obj->has($vehicle->getNumber()));
    }

    public function testVehicleHasClosedVisit()
    {
        $vehicle = $this->createVehicle();
        $visit = $this->createVisit($vehicle);

        $vehicleRepository = $this->getVehicleRepository($vehicle, $vehicle->getNumber());
        $visitRepository = $this->getVisitRepository($vehicle, $visit);
        $objectManager = $this->getObjectManager($vehicleRepository, $visitRepository);

        $obj = new InActionPermission($objectManager, new NullLogger());

        $this->assertTrue($obj->has($vehicle->getNumber()));
    }

    public function testVehicleHasOpenVisit()
    {
        $vehicle = $this->createVehicle();
        $visit = $this->createVisit($vehicle, false);

        $vehicleRepository = $this->getVehicleRepository($vehicle, $vehicle->getNumber());
        $visitRepository = $this->getVisitRepository($vehicle, $visit);
        $objectManager = $this->getObjectManager($vehicleRepository, $visitRepository);

        $obj = new InActionPermission($objectManager, new NullLogger());

        $this->assertFalse($obj->has($vehicle->getNumber()));
    }

    public function testNoVehicle()
    {
        $number = uniqid();
        $vehicleRepository = $this->getVehicleRepository(null, $number);
        $objectManager = $this->getObjectManager($vehicleRepository, $this->createMock(VisitRepository::class));

        $obj = new InActionPermission($objectManager, new NullLogger());

        $this->assertFalse($obj->has($number));
    }

    public function testVehicleNotActive()
    {
        $vehicle = $this->createVehicle();
        $vehicle->setIsActive(false);

        $vehicleRepository = $this->getVehicleRepository($vehicle, $vehicle->getNumber());
        $objectManager = $this->getObjectManager($vehicleRepository, $this->createMock(VisitRepository::class));

        $obj = new InActionPermission($objectManager, new NullLogger());

        $this->assertFalse($obj->has($vehicle->getNumber()));
    }
}
