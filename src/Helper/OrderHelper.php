<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Helper;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\ConnectionException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Sync\SyncBehavior;
use Shopware\Core\Framework\Api\Sync\SyncOperation;
use Shopware\Core\Framework\Api\Sync\SyncService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use TigerMedia\TigerConnect\Core\Content\Logger\LoggerDefinition;
use TigerMedia\TigerConnect\Event\Order\BeforeOrderLoadedEvent;
use TigerMedia\TigerConnect\Event\Order\OrderLoadedEvent;
use TigerMedia\TigerConnect\Exception\OrderException;
use TigerMedia\TigerConnect\Model\ExportDataModel;
use TigerMedia\TigerConnect\Service\MailService;
use TigerMedia\TigerConnect\Service\OrderInterface;

class OrderHelper
{

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param EntityRepository<OrderCollection> $orderRepository
     * @param SyncService $syncService
     * @param MailService $mailService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityRepository $orderRepository,
        private readonly SyncService $syncService,
        private readonly MailService $mailService,
        private readonly LoggerInterface $logger
    )
    {
    }

    public function dispatch(
        Event $event
    ): void
    {
        $this->dispatcher->dispatch($event);
    }

    public function getOrder(
        string $orderNumber,
        Context $context
    ): ?ExportDataModel
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('orderNumber', $orderNumber))->addAssociations([
            'addresses.country',
            'transactions.paymentMethod',
            'currency',
            'lineItems',
            'salesChannel.country',
            'deliveries.shippingMethod',
            'orderCustomer.customer.defaultBillingAddress',
            'orderCustomer.customer.group',
            'orderCustomer.vatIds'
        ]);
        $event = new BeforeOrderLoadedEvent($orderNumber, $criteria);
        $this->dispatch($event);

        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search(
            $event->getCriteria(),
            $context
        )->first();

        if ($order === null) {
            return null;
        }

        $model = new ExportDataModel($order, $context);
        $this->dispatch(new OrderLoadedEvent($model));
        return $model;
    }

    /**
     * @param ExportDataModel $model
     * @param string $message
     * @param Context|null $context
     * @return void
     * @throws OrderException
     */
    public function markAsFailed(
        ExportDataModel $model,
        string $message,
        Context $context = null
    ): void
    {
        $this->logger->critical($message);
        $this->addToLogger($model->getOrder()->getOrderNumber(), $message, LoggerDefinition::LOG_CRITICAL, $context ?? Context::createDefaultContext());
        $payload = [
            'id'           => $model->getOrder()->getId(),
            'customFields' => [
                'tiger_connect_custom_field_set_processed' => false
            ]
        ];

        $this->sync(
            'tiger_connect_order-' . $model->getOrder()->getOrderNumber(),
            $model->getOrder()->getOrderNumber(),
            LoggerDefinition::LOG_CRITICAL,
            OrderDefinition::ENTITY_NAME,
            [$payload],
            $context ?? Context::createDefaultContext());
    }

    /**
     * @throws OrderException
     */
    public function markAsSuccess(
        ExportDataModel $model,
        string $message,
        string $erpOrderNumber,
        Context $context = null
    ): bool
    {
        $this->logger->info($message);
        $this->addToLogger($model->getOrder()->getOrderNumber(), $message, LoggerDefinition::LOG_INFO, $context ?? Context::createDefaultContext());
        $payload = [
            'id'           => $model->getOrder()->getId(),
            'customFields' => [
                'tiger_connect_custom_field_set_processed'        => true,
                'tiger_connect_custom_field_set_erp_order_number' => $erpOrderNumber
            ]
        ];

        $this->sync(
            'tiger_connect_order-' . $model->getOrder()->getOrderNumber(),
            $model->getOrder()->getOrderNumber(),
            LoggerDefinition::LOG_INFO,
            OrderDefinition::ENTITY_NAME,
            [$payload],
            $context ?? Context::createDefaultContext());
        return OrderInterface::SUCCESS;
    }

    /**
     * @throws OrderException
     */
    public function addToLogger(
        string $orderNumber,
        string $message,
        string $level,
        Context $context
    ): void
    {
        $payload = [
            [
                'id'          => Uuid::randomHex(),
                'message'     => $message,
                'level'       => $level,
                'orderNumber' => $orderNumber,
                'createdAt'   => (new DateTimeImmutable())->setTimezone(new DateTimeZone('Europe/Copenhagen'))->format(Defaults::STORAGE_DATE_TIME_FORMAT)
            ]
        ];

        $this->sync(
            'tiger_connect_logger-' . $orderNumber,
            $message,
            $level,
            LoggerDefinition::ENTITY_NAME,
            $payload,
            $context
        );
    }

    /**
     * @throws OrderException
     */
    public function sendMail(ExportDataModel $dataModel): void
    {
        try {
            $this->mailService->send($dataModel);
        } catch (OrderException $exception) {
            $this->addToLogger($dataModel->getOrder()->getOrderNumber(), $exception->getMessage(), LoggerDefinition::LOG_CRITICAL, $dataModel->getContext());
            throw $exception;
        }
    }

    /**
     * @param string $key
     * @param string $orderNumber
     * @param string $level
     * @param string $entityName
     * @param mixed[] $payload
     * @param Context $context
     * @return void
     * @throws OrderException
     */
    private function sync(
        string $key,
        string $orderNumber,
        string $level,
        string $entityName,
        array $payload,
        Context $context
    ): void
    {
        try {
            $this->syncService->sync([
                new SyncOperation(
                    $key,
                    $entityName,
                    SyncOperation::ACTION_UPSERT,
                    $payload
                )
            ], $context, new SyncBehavior(EntityIndexerRegistry::USE_INDEXING_QUEUE));
        } catch (ConnectionException $exception) {
            $this->addToLogger($orderNumber, $exception->getMessage(), $level, $context);
            throw new OrderException($exception->getMessage());
        }
    }
}