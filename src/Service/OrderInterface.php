<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Service;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

interface OrderInterface
{
    const SUCCESS = true;
    CONST FAILED = false;
    public function export(string $orderNumber, ?Context $context = null): bool;
}