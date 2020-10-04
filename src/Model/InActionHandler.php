<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\Vehicle;
use App\Entity\Visit;
use App\ValueObject\ActionType;
use LogicException;

class InActionHandler extends AbstractActionHandler
{
    protected function doAction(string $vehicleNumber): void
    {
        $em = $this->getEm();

        $vehicle = $em
            ->getRepository(Vehicle::class)
            ->findOneByNumber($vehicleNumber);

        if (empty($vehicle)) {
            throw new LogicException(
                "Для создания \"посещения\" ТС уже должено быть добавленно в Базу. Гос номер: {$vehicleNumber}"
            );
        }

        $visit = new Visit();
        $visit->setVehicle($vehicle);
        $em->persist($visit);
        $em->flush();
    }

    protected function getActionType(): ActionType
    {
        return new ActionType(ActionType::IN);
    }
}
