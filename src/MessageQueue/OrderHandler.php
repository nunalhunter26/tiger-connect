<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\MessageQueue;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use TigerMedia\TigerConnect\Service\OrderInterface;

#[AsMessageHandler]
class OrderHandler
{
    public function __construct(
        private readonly OrderInterface $orderService,
    )
    {
    }

    public function __invoke(OrderDataMessage $message): void
    {
        $this->orderService->export($message->getOrderNumber(), $message->getContext());
    }
}