<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Controller;

use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use TigerMedia\TigerConnect\Service\OrderInterface;

#[Route(path: '/api/_action/tiger-connect', defaults: ['_routeScope' => ['api']])]
class OrderExportController extends AbstractController
{
    public function __construct(
        private readonly OrderInterface $orderService
    )
    {
    }

    #[Route(path: '/order/export/{orderNumber}', name: 'api.tiger.connect.export.order', methods: ['POST'])]
    public function runOrderExport(string $orderNumber, Context $context): JsonResponse
    {
        $result = $this->orderService->export($orderNumber, $context);
        return new JsonResponse(['result' => $result]);
    }
}