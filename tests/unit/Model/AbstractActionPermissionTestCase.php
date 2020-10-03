<?php declare(strict_types=1);

namespace App\Tests\unit\Model;

use App\Entity\Vehicle;
use App\Entity\Visit;
use App\Repository\VehicleRepository;
use App\Repository\VisitRepository;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractActionPermissionTestCase extends TestCase
{
    /**
     * @return Vehicle
     */
    protected function createVehicle(): Vehicle
    {
        $number = uniqid();
        $vehicle = new Vehicle();
        $vehicle
            ->setIsActive(true)
            ->setNumber($number);
        return $vehicle;
    }

    /**
     * @param Vehicle|null $vehicle
     * @param string $number
     * @return MockObject
     */
    protected function getVehicleRepository(?Vehicle $vehicle, string $number): MockObject
    {
        $vehicleRepository = $this->createMock(VehicleRepository::class);
        $vehicleRepository
            ->expects($this->once())
            ->method('__call')
        ->with('findOneByNumber', [$number])
        ->willReturn($vehicle);
        return $vehicleRepository;
    }

    /**
     * @param Vehicle $vehicle
     * @param bool|null $isClosed
     * @return Visit
     */
    protected function createVisit(Vehicle $vehicle, ?bool $isClosed = true): Visit
    {
        $visit = new Visit();
        $visit
            ->setClosedAt($isClosed ? new DateTimeImmutable() : null)
            ->setCreatedAt(new DateTimeImmutable())
            ->setVehicle($vehicle);
        return $visit;
    }

    /**
     * @param Vehicle $vehicle
     * @param Visit|null $visit
     * @return MockObject
     */
    protected function getVisitRepository(Vehicle $vehicle, ?Visit $visit = null): MockObject
    {
        $visitRepository = $this->createMock(VisitRepository::class);
        $visitRepository
            ->expects($this->once())
            ->method('findLastOfVehicle')
        ->with($vehicle)
        ->willReturn($visit);
        return $visitRepository;
    }

    /**
     * @param MockObject $vehicleRepository
     * @param MockObject $visitRepository
     * @return ObjectManager|MockObject
     */
    protected function getObjectManager(MockObject $vehicleRepository, MockObject $visitRepository): MockObject
    {
        /** @var ObjectManager|MockObject $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager
            ->expects($this->any())
            ->method('getRepository')
        ->withConsecutive([Vehicle::class], [Visit::class])
        ->willReturnOnConsecutiveCalls($vehicleRepository, $visitRepository);
        return $objectManager;
    }
}
