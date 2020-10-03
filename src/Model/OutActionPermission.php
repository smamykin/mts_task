<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\Vehicle;
use App\Entity\Visit;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

class OutActionPermission implements ActionPermissionInterface
{
    /**
     * @var ObjectManager
     */
    private $em;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ObjectManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function has(string $vehicleNumber): bool
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->em
            ->getRepository(Vehicle::class)
            ->findOneByNumber($vehicleNumber);

        if (empty($vehicle) || !$vehicle->getIsActive()) {
            $this->logger->info(
                "Запрашивается разрешение на \"выезд\" для неизвестного ТC c номером {$vehicleNumber}",
                [__CLASS__]
            );
            return true;
        }

        /** @var Visit $last */
        $last = $this->em
            ->getRepository(Visit::class)
            ->findLastOfVehicle($vehicle);

        if (empty($last) || $last->getClosedAt()) {
            $this->logger->info(
                "В базе данных отсутствует \"открытая\" запись посещения для ТС с номером $vehicleNumber",
                [__CLASS__]
            );

            return false;
        }

        return true;
    }
}
