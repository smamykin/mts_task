<?php declare(strict_types=1);

namespace App\Tests\unit\Model;

use App\Entity\Vehicle;
use App\Entity\Visit;
use DateTimeImmutable;

trait EntityFixturesTrait
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
}
