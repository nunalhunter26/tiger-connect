<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\MessageQueue;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

class OrderDataMessage implements AsyncMessageInterface
{
    public function __construct(
        private readonly string $orderNumber,
        private readonly Context $context
    )
    {
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}