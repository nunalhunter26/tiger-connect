<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Core\Content\Flow\Dispatching\Action;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Flow\Dispatching\Action\FlowAction;
use Shopware\Core\Content\Flow\Dispatching\StorableFlow;
use Shopware\Core\DevOps\Environment\EnvironmentHelper;
use Shopware\Core\Framework\Event\OrderAware;
use Symfony\Component\Messenger\MessageBusInterface;
use TigerMedia\TigerConnect\Core\Content\Logger\LoggerDefinition;
use TigerMedia\TigerConnect\Exception\OrderException;
use TigerMedia\TigerConnect\Helper\OrderHelper;
use TigerMedia\TigerConnect\MessageQueue\OrderDataMessage;

class OrderExportAction extends FlowAction
{
    const ACTION_NAME = 'action.tiger_connect.order.export';

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly OrderHelper $orderHelper,
    )
    {
    }

    public function requirements(): array
    {
        return [OrderAware::class];
    }

    public static function getName(): string
    {
        return self::ACTION_NAME;
    }

    /**
     * @throws OrderException
     */
    public function handleFlow(StorableFlow $flow): void
    {
        if (EnvironmentHelper::getVariable('APP_ENV') !== 'prod') {
            return;
        }

        /** @var OrderEntity $order */
        $order = $flow->getData('order');
        $this->orderHelper->addToLogger($order->getOrderNumber(), "Starting flow action for Order [{$order->getOrderNumber()}].", LoggerDefinition::LOG_INFO, $flow->getContext());
        $this->orderHelper->addToLogger($order->getOrderNumber(), "Starting dispatch for Order [{$order->getOrderNumber()}].", LoggerDefinition::LOG_INFO, $flow->getContext());
        $this->messageBus->dispatch(new OrderDataMessage($order->getOrderNumber(), $flow->getContext()));
        $this->orderHelper->addToLogger($order->getOrderNumber(), "Dispatched queue for Order [{$order->getOrderNumber()}].", LoggerDefinition::LOG_INFO, $flow->getContext());
    }
}