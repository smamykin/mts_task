<?php

namespace App\Controller;

use App\Entity\Visit;
use App\Repository\VehicleRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SimpleApiController
 * @package App\Controller
 * @Route("/api/v0.1")
 */
class RestApiController extends AbstractController
{
    /**
     * @Route("/vehicles", name="api_vehicle_list", methods={"GET"})
     * @param VehicleRepository $repository
     * @return JsonResponse
     */
    public function vehicleList(VehicleRepository $repository): JsonResponse
    {
        $vehicles = $repository->findAll();
        if (empty($vehicles)) {
            return $this->json([], 204);
        }

        return $this->json(['payload' => $vehicles,], 200);
    }

    /**
     * @Route("/vehicle/{vehicleNumber}", name="api_vehicle_detail", methods={"GET"}, requirements={"vehicleNumber"="^[\w\d]+$"})
     * @param $vehicleNumber
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function vehicle($vehicleNumber, VehicleRepository $vehicleRepository): JsonResponse
    {
        $vehicle = $vehicleRepository->findOneByNumber($vehicleNumber);
        if (empty($vehicle)) {
            return $this->json([], 404);
        }

        return $this->json(['payload' => $vehicle], 200);
    }

    /**
     * @Route("/vehicle/{vehicleNumber}/visits", name="api_vehicle_visits", methods={"GET"}, requirements={"vehicleNumber"="^[\w\d]+$"})
     * @param $vehicleNumber
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function visits($vehicleNumber, VehicleRepository $vehicleRepository): JsonResponse
    {
        $vehicle = $vehicleRepository->findOneByNumber($vehicleNumber);
        if (empty($vehicle)) {
            return $this->json([], 404);
        }
        $visits = $vehicle->getVisits();
        if(empty($visits->count())) {
            return $this->json([], 204);
        }

        return $this->json(['payload' => $visits], 200);
    }

    /**
     * @Route("/vehicle/{vehicleNumber}/visits", name="api_vehicle_new_visit", methods={"POST"}, requirements={"vehicleNumber"="^[\w\d]+$"})
     * @param $vehicleNumber
     * @param EntityManagerInterface $em
     * @param VehicleRepository $vehicleRepository
     * @param Request $request
     * @return JsonResponse
     */
    public function newVisit(
        $vehicleNumber,
        EntityManagerInterface $em,
        VehicleRepository $vehicleRepository,
        Request $request
    ): JsonResponse
    {
        $vehicle = $vehicleRepository->findOneByNumber($vehicleNumber);
        if (empty($vehicle)) {
            return $this->json([], 404);
        }

        $data = $request->request->get('payload');

        /** @var Visit $lastVisit */
        $lastVisit = $vehicle->getVisits()->last();
        if ($lastVisit && empty($data['closed_at']) && empty($lastVisit->getClosedAt())) {
            return $this->json([
                'payload' =>[
                    'message' => 'Не возможно создать открытую запись для ТС: открытая запись уже создана.'
                ]
            ], 400);
        }

        $newVisit = new Visit();
        $newVisit->setVehicle($vehicle);
        if (!empty($data['closed_at'])) {
            $closedAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['closed_at']);
            $newVisit->setClosedAt($closedAt);
        }

        $em->persist($newVisit);
        $em->flush();

        return $this->json(['payload' => $newVisit,]);
    }

    /**
     * @Route("/vehicle/{vehicleNumber}/visit/{visit}", name="api_vehicle_edit_visit", methods={"POST"}, requirements={"vehicleNumber"="^[\w\d]+$"})
     * @param $vehicleNumber
     * @param Visit $visit
     * @param EntityManagerInterface $em
     * @param VehicleRepository $vehicleRepository
     * @param Request $request
     * @return JsonResponse
     */
    public function editVisit(
        $vehicleNumber,
        Visit $visit,
        EntityManagerInterface $em,
        VehicleRepository $vehicleRepository,
        Request $request
    ): JsonResponse
    {
        $vehicle = $vehicleRepository->findOneByNumber($vehicleNumber);
        if (empty($vehicle)) {
            return $this->json([], 404);
        }

        if ($vehicle->getId() !== $visit->getVehicle()->getId()) {
            return $this->json([
                'payload' => [
                    'message' => 'Id посещения не соответсвует номеру ТС.'
                ]
            ], 400);
        }

        $data = $request->request->get('payload');

        /** @var Visit $lastVisit */
        $lastVisit = $vehicle->getVisits()->last();
        if ($lastVisit
            && $lastVisit->getId() !== $visit->getId()
            && empty($data['closed_at'])
            && empty($lastVisit->getClosedAt())) {
            return $this->json([
                'payload' =>[
                    'message' => 'Не возможно сделать посещение открытым для ТС: открытая запись уже существует.'
                ]
            ], 400);
        }

        if (!empty($data['closed_at'])) {
            $closedAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['closed_at']);
            if (empty($closedAt)) {
                return $this->json(['payload' => ['message' => 'Не верный формат даты поля closed_at',]], 400);
            }
            $visit->setClosedAt($closedAt);
        }

        $em->persist($visit);
        $em->flush();

        return $this->json(['payload' => $visit,]);
    }

    /**
     * @Route("/vehicle/{vehicleNumber}/last-visit", name="api_vehicle_last-visit", methods={"GET"}, requirements={"vehicleNumber"="^[\w\d]+$"})
     * @param $vehicleNumber
     * @param VehicleRepository $vehicleRepository
     * @return JsonResponse
     */
    public function lastVisit($vehicleNumber, VehicleRepository $vehicleRepository): JsonResponse
    {
        $vehicle = $vehicleRepository->findOneByNumber($vehicleNumber);
        if (empty($vehicle)) {
            return $this->json([], 404);
        }
        $visits = $vehicle->getVisits();
        if(empty($visits->count())) {
            return $this->json([], 204);
        }
        return $this->json(['payload' => $visits->last()], 200);
    }
}
