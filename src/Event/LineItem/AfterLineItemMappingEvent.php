<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Event\LineItem;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Symfony\Contracts\EventDispatcher\Event;
use TigerMedia\TigerConnect\Exception\LineItemException;

class AfterLineItemMappingEvent extends Event
{
    /** @var mixed[] $data */
    private array $data;
    private OrderLineItemEntity $orderLineItem;

    /**
     * @param OrderLineItemEntity $orderLineItem
     * @param mixed[] $data
     */
    public function __construct(
        OrderLineItemEntity $orderLineItem,
        array $data
    )
    {
        $this->data = $data;
        $this->orderLineItem = $orderLineItem;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param mixed[] $data
     * @return void
     */
    public function setData(array $data) : void
    {
        $this->data = $data;
    }

    public function getOrderLineItem(): OrderLineItemEntity
    {
        return $this->orderLineItem;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function add(string $key, mixed $value): static
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @throws LineItemException
     */
    public function replace(string $key, mixed $value): static
    {
        if (array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
            return $this;
        }

        throw new LineItemException('Data key not found.');
    }

    public function remove(string $key): static
    {
        unset($this->data[$key]);
        return $this;
    }

    public function unsetData(): void
    {
        $this->data = [];
    }
}