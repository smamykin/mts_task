<?php declare(strict_types=1);

namespace App\Controller;

use App\Exception\ExceptionInterface;
use App\Exception\PermissionException;
use App\Model\ActionHandlerFactoryInterface;
use App\Model\ActionPermissionFactoryInterface;
use App\ValueObject\ActionType;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Упрощенное api для получения и обновления информации:
 * - можно ли открыть шлагбаум для ТС по гос номеру.
 * - зафиксировать "заезд" или "выезд" ТС с паркинга.
 * @package App\Controller
 * @Route("/api-simple/v0.1")
 */
class SimpleApiController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SimpleApiController constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Экшен позволяет получить информацию "можно ли открыть шлагбаум для заезда/выезда ТС"
     *
     * @Route(
     *     "/permission/{type}/{vehicleNumber}",
     *      name="permission",
     *      methods={"GET"},
     *      requirements={"type"="^\w+$","vehicleNumber"="^[\w\d]+$"})
     * )
     * @param $type
     * @param $vehicleNumber
     * @param ActionPermissionFactoryInterface $factory
     * @return JsonResponse
     */
    public function permission($type, $vehicleNumber, ActionPermissionFactoryInterface $factory): JsonResponse
    {
        try {
            $result = $factory->create(new ActionType($type))->has($vehicleNumber);
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
     * Экшен позволяет зафиксировать информацию о заезде/выезде ТС.
     *
     * @Route(
     *     "/action/{type}/{vehicleNumber}",
     *      name="action",
     *      methods={"POST"},
     *      requirements={"type"="^\w+$","vehicleNumber"="^[\w\d]+$"})
     * )
     * @param $type
     * @param $vehicleNumber
     * @param ActionHandlerFactoryInterface $factory
     * @return JsonResponse
     */
    public function action($type, $vehicleNumber, ActionHandlerFactoryInterface $factory): JsonResponse
    {
        try {
            $factory->create(new ActionType($type))->do($vehicleNumber);
        } catch (PermissionException $e) {
            return $this->json([
                'payload' => ['message' => $e->getMessage(),],
            ], 403);
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
                'result' => true,
            ],
        ], 200);
    }
}
