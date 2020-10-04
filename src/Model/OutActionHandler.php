<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\Vehicle;
use App\Entity\Visit;
use App\ValueObject\ActionType;
use DateTimeImmutable;
use LogicException;

class OutActionHandler extends AbstractActionHandler
{
    protected function doAction(string $vehicleNumber): void
    {
        $em = $this->getEm();

        $vehicle = $em
            ->getRepository(Vehicle::class)
            ->findOneByNumber($vehicleNumber);

        if (empty($vehicle)) {
            throw new LogicException(
                "Не возможно закрыть \"посещение: ТС с номером {$vehicleNumber} не существует."
            );
        }

        $visit = $em
            ->getRepository(Visit::class)
            ->findLastOfVehicle($vehicle);

        if (!$visit) {
            $visit = new Visit();
            $visit->setVehicle($vehicle);
        }

        $visit->setClosedAt(new DateTimeImmutable());

        $em->persist($visit);
        $em->flush();
    }

    protected function getActionType(): ActionType
    {
        return new ActionType(ActionType::OUT);
    }
}
