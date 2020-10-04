<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Vehicle;
use App\Entity\Visit;
use App\ValueObject\ActionType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VehicleFixture extends Fixture
{
    private const FOR_PERMISSION_NUMBER_CAN_IN = 'y000yy777';
    private const FOR_PERMISSION_NUMBER_CANNOT_IN = 'y001yy777';
    private const FOR_PERMISSION_NUMBER_CAN_OUT = 'y002yy777';
    private const FOR_PERMISSION_NUMBER_CANNOT_OUT = 'y003yy777';
    private const FOR_VEHICLE_VISITS = 'y004yy777';
    private const VEHICLE_PREFIX_FOR_LIST = 'y005yy77';
    private const VEHICLE_COUNT_FOR_LIST = 10;
    private const FOR_ACTION_IN = 'a001yy77';
    private const FOR_ACTION_OUT = 'a002yy77';

    /**
     * @return string
     */
    public static function getFixtureIdForGetVehicleVisits(): string
    {
        return self::FOR_VEHICLE_VISITS;
    }

    /**
     * @return string[]
     */
    public static function getVehicleNumbers(): array
    {
        $result = [];
        for ($i = self::VEHICLE_COUNT_FOR_LIST; $i--;) {
            $result[] = self::VEHICLE_PREFIX_FOR_LIST . $i;
        }

        return $result;
    }

    /**
     * @param ActionType $param
     * @param bool $hasPermission
     * @return string
     */
    public static function getVehicleNumbersByPermission(ActionType $param, bool $hasPermission): string
    {
        $set = [
            ActionType::IN => [
                //order is important
                self::FOR_PERMISSION_NUMBER_CAN_OUT,
                self::FOR_PERMISSION_NUMBER_CAN_IN,
            ],
            ActionType::OUT => [
                //order is important
                self::FOR_PERMISSION_NUMBER_CANNOT_OUT,
                self::FOR_PERMISSION_NUMBER_CANNOT_IN,
            ],
        ];
        return $set[(string)$param][(int)$hasPermission];
    }

    public static function getVehicleNumbersForAction(ActionType $type)
    {
        $map = [
            ActionType::IN => self::FOR_ACTION_IN,
            ActionType::OUT => self::FOR_ACTION_OUT,
        ];

        return $map[(string)$type];
    }

    public function load(ObjectManager $manager)
    {
        $this->loadVehicleForPermissionCheck($manager);
        $this->loadVehicleForGetVehicleVisits($manager);
        $this->loadVehicles($manager);
        $this->loadVehicleForAction($manager);

        $manager->flush();
    }
    private function loadVehicleForAction(ObjectManager $manager): void
    {
        $obj = new Vehicle();
        $obj->setIsActive(true);
        $obj->setNumber(self::FOR_ACTION_IN);
        $manager->persist($obj);

        $obj = new Vehicle();
        $obj->setIsActive(true);
        $obj->setNumber(self::FOR_ACTION_OUT);
        $visit = new Visit();
        $visit->setVehicle($obj);
        $manager->persist($visit);
    }

    private function loadVehicleForPermissionCheck(ObjectManager $manager): void
    {
        // can in
        $obj = new Vehicle();
        $obj->setIsActive(true);
        $obj->setNumber(self::FOR_PERMISSION_NUMBER_CAN_IN);
        $manager->persist($obj);

        // cannot in
        $obj = new Vehicle();
        $obj->setIsActive(false);
        $obj->setNumber(self::FOR_PERMISSION_NUMBER_CANNOT_IN);
        $manager->persist($obj);

        // can out
        $obj = new Vehicle();
        $obj->setIsActive(true);
        $obj->setNumber(self::FOR_PERMISSION_NUMBER_CAN_OUT);
        $visit = new Visit();
        $visit->setVehicle($obj);
        $manager->persist($visit);

        //cannot out
        $obj = new Vehicle();
        $obj->setIsActive(true);
        $obj->setNumber(self::FOR_PERMISSION_NUMBER_CANNOT_OUT);
        $visit = new Visit();
        $visit->setVehicle($obj);
        $visit->setClosedAt(new DateTimeImmutable());
        $manager->persist($visit);
    }

    private function loadVehicleForGetVehicleVisits(ObjectManager $manager): void
    {
        $obj = new Vehicle();
        $obj->setNumber(self::FOR_VEHICLE_VISITS);
        $obj->setIsActive(true);

        for ($i = 10; $i--;) {
            $visit = new Visit();
            $visit->setVehicle($obj);

            if ($i !== 0) {
                $visit->setClosedAt(new DateTimeImmutable());
            }

            $manager->persist($visit);
        }
    }

    private function loadVehicles(ObjectManager $manager): void
    {
        foreach (static::getVehicleNumbers() as $number) {
            $obj = new Vehicle();
            $obj->setNumber($number);
            $obj->setIsActive(true);
            $manager->persist($obj);
        }
    }
}
