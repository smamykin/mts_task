<?php declare(strict_types=1);

namespace App\Controller;

use App\Exception\ExceptionInterface;
use App\Service\GateActionHandlerInterface;
use App\Service\GatePermissionInterface;
use App\ValueObject\ActionType;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SimpleApiController
 * @package App\Controller
 * @Route("/api-simple/v0.1")
 */
class SimpleApiController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route(
     *     "/permission/{type}/{vehicleNumber}",
     *      name="permission",
     *      methods={"GET"},
     *      requirements={"type"="^\w+$","vehicleNumber"="^[\w\d]+$"})
     * )
     * @param $type
     * @param $vehicleNumber
     * @param GatePermissionInterface $permission
     * @return JsonResponse
     */
    public function permission($type, $vehicleNumber, GatePermissionInterface $permission)
    {
        try {
            $result = $permission->has(new ActionType($type), $vehicleNumber);
        } catch (ExceptionInterface $e) {
            return $this->json([
                'payload' => ['message' => $e->getMessage(),],
            ], 400);
        } catch (Exception $exception) {
            return $this->json([
                'payload' => ['message' => 'Сообщите о проблеме в техническую поддержку.',],
            ], 500);
        }

        return $this->json([
            'payload' => [
                'type' => $type,
                'permission' => $result,
            ],
        ], 200);
    }

    /**
     * @Route(
     *     "/action/{type}/{vehicleNumber}",
     *      name="permission",
     *      methods={"POST"},
     *      requirements={"type"="^\w+$","vehicleNumber"="^[\w\d]+$"})
     * )
     * @param $type
     * @param $vehicleNumber
     * @param GatePermissionInterface $permission
     * @param GateActionHandlerInterface $gateActionHandler
     * @return JsonResponse
     */
//    public function action($type, $vehicleNumber, GatePermissionInterface $permission, GateActionHandlerInterface $gateActionHandler)
//    {
//        try {
//            $type = new ActionType($type);
//            $result = $permission->has($type, $vehicleNumber);
//            if (!$result) {
//                return $this->json([
//                    'payload' => ['message' => 'Нет прав на совершение действия',],
//                ], 403);
//            }
//            $gateActionHandler->do($type, $vehicleNumber);
//        } catch (ExceptionInterface $e) {
//            return $this->json([
//                'payload' => ['message' => $e->getMessage(),],
//            ], 400);
//        } catch (Exception $exception) {
//            return $this->json([
//                'payload' => ['message' => 'Сообщите о проблеме в техническую поддержку.',],
//            ], 500);
//        }
//
//        return $this->json([
//            'payload' => [
//                'type' => $type,
//                'result' => true,
//            ],
//        ], 200);
//    }
}
