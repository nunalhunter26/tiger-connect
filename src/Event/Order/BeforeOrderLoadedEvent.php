<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Event\Order;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeOrderLoadedEvent extends Event
{
    private string $orderNumber;
    private Criteria $criteria;

    public function __construct(
        string $orderNumber,
        Criteria $criteria
    )
    {
        $this->orderNumber = $orderNumber;
        $this->criteria = $criteria;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }
}