<?php declare(strict_types=1);

namespace App\Tests\unit\Model;

use App\Model\OutActionPermission;
use Psr\Log\NullLogger;

class OutActionPermissionTest extends AbstractActionPermissionTestCase
{
    public function testNoVehicle()
    {
        $number = uniqid();
        $vehicleRepository = $this->getVehicleRepository(null, $number);
        $objectManager = $this->getObjectManager($vehicleRepository);

        $obj = new OutActionPermission($objectManager, new NullLogger());

        $this->assertTrue($obj->has($number));
    }

    public function testVehicleNotActive()
    {
        $vehicle = $this->createVehicle();
        $vehicle->setIsActive(false);

        $vehicleRepository = $this->getVehicleRepository($vehicle, $vehicle->getNumber());
        $objectManager = $this->getObjectManager($vehicleRepository);

        $obj = new OutActionPermission($objectManager, new NullLogger());

        $this->assertTrue($obj->has($vehicle->getNumber()));
    }

    public function testNoVisit()
    {
        $vehicle = $this->createVehicle();

        $vehicleRepository = $this->getVehicleRepository($vehicle, $vehicle->getNumber());
        $visitRepository = $this->getVisitRepository($vehicle);
        $objectManager = $this->getObjectManager($vehicleRepository, $visitRepository);

        $obj = new OutActionPermission($objectManager, new NullLogger());

        $this->assertFalse($obj->has($vehicle->getNumber()));
    }

    public function testHasClosedLastVisit()
    {
        $vehicle = $this->createVehicle();
        $visit = $this->createVisit($vehicle);

        $vehicleRepository = $this->getVehicleRepository($vehicle, $vehicle->getNumber());
        $visitRepository = $this->getVisitRepository($vehicle, $visit);
        $objectManager = $this->getObjectManager($vehicleRepository, $visitRepository);

        $obj = new OutActionPermission($objectManager, new NullLogger());

        $this->assertFalse($obj->has($vehicle->getNumber()));
    }

    public function testHasOpenedLastVisit()
    {
        $vehicle = $this->createVehicle();
        $visit = $this->createVisit($vehicle, false);

        $vehicleRepository = $this->getVehicleRepository($vehicle, $vehicle->getNumber());
        $visitRepository = $this->getVisitRepository($vehicle, $visit);
        $objectManager = $this->getObjectManager($vehicleRepository, $visitRepository);

        $obj = new OutActionPermission($objectManager, new NullLogger());

        $this->assertTrue($obj->has($vehicle->getNumber()));
    }
}
