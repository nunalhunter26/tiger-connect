<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Service;

use Shopware\Core\Framework\Context;
use TigerMedia\TigerConnect\Core\Content\Logger\LoggerDefinition;
use TigerMedia\TigerConnect\Exception\OrderException;
use TigerMedia\TigerConnect\Helper\OrderHelper;
use TigerMedia\TigerConnect\Model\ExportDataModel;

abstract class AbstractOrderService implements OrderInterface
{
    public function __construct(
        protected readonly BaseApiService $baseApiService,
        protected readonly OrderHelper $orderHelper
    )
    {
    }

    abstract protected function run(ExportDataModel $dataModel, Context $context): bool;
    abstract protected function removeOrder(ExportDataModel $dataModel, string $erpNumber): void;

    /**
     * @throws OrderException
     */
    public function export(string $orderNumber, ?Context $context = null): bool
    {
        $context ??= Context::createDefaultContext();
        $this->baseApiService->setOrderNumber($orderNumber);
        $orderModel = $this->orderHelper->getOrder($orderNumber, $context);
        $this->validateDataModel($orderModel, $orderNumber, $context);
        $this->baseApiService->setSalesChannelId($orderModel->getOrder()->getSalesChannelId());
        return $this->run($orderModel, $context);
    }

    /**
     * @throws OrderException
     */
    private function validateDataModel(?ExportDataModel $dataModel, string $orderNumber, Context $context): void
    {
        if ($dataModel === null) {
            $this->orderHelper->addToLogger($orderNumber, "Order [$orderNumber] not found in Shopware.", LoggerDefinition::LOG_CRITICAL, $context);
            throw new OrderException("Order [$orderNumber] not found in Shopware.");
        }

        if ($dataModel->getNumber() == null) {
            $this->markAsFailed($dataModel, "Order [$orderNumber] - no identifier set.");
        }
    }

    /**
     * @throws OrderException
     */
    protected function markAsFailed(ExportDataModel $dataModel, string $message, string $erpNumber = null): void
    {
        if ($erpNumber !== null) {
            $this->removeOrder($dataModel, $erpNumber);
        }

        try {
            $this->orderHelper->markAsFailed(
                $dataModel,
                $message,
                $dataModel->getContext()
            );
            $this->orderHelper->sendMail($dataModel);
        } catch (OrderException $exception) {
            $this->baseApiService->logger->critical($exception->getMessage(), ['exception' => $exception]);
            throw new OrderException($message, $exception->getCode(), $exception);
        }

        throw new OrderException($message);
    }
}