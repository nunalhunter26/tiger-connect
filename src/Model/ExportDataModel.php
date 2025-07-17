<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Model;

use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use TigerMedia\TigerConnect\Exception\ExportDataException;
use TigerMedia\TigerConnect\TigerConnect;

class ExportDataModel
{

    /** @var mixed[] $mappings */
    private array $mappings = [];
    private OrderEntity $order;
    private string $phoneNumber;
    private OrderCustomerEntity $customer;
    private OrderAddressEntity $billingAddress;
    private OrderAddressEntity $shippingAddress;
    private Context $context;
    private string $number;
    private string $taxState;
    private string $paymentIdentifier;
    private string $shipmentIdentifier;

    public function __construct(
        OrderEntity $order,
        Context $context
    )
    {
        $this->order = $order;
        $this->context = $context;
        $this->customer = $order->getOrderCustomer();
        $this->billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
        $this->shippingAddress = $order->getAddresses()->get($order->getDeliveries()->first()->getShippingOrderAddressId());
        $this->phoneNumber = $this->getBillingAddress()->getPhoneNumber();
        $this->number = $this->getPhoneNumber();
        $this->taxState = $order->getOrderCustomer()?->getCustomer()?->getGroup()?->getDisplayGross() ? 'gross' : 'net';
        $this->paymentIdentifier = $order->getTransactions()->first()?->getPaymentMethod()?->getTranslated()['customFields'][TigerConnect::PAYMENT_IDENTIFIER] ?? '';
        $this->shipmentIdentifier = $order->getDeliveries()->first()?->getShippingMethod()?->getTranslated()['customFields'][TigerConnect::SHIPMENT_IDENTIFIER] ?? '';
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    /**
     * @return mixed[]
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }

    /**
     * @throws ExportDataException
     */
    public function add(string $key, mixed $value): static
    {
        if (array_key_exists($key, $this->mappings)) {
            throw new ExportDataException('Data key already exists, try using replace() instead or use remove() first before adding.');
        }

        $this->mappings[$key] = $value;
        return $this;
    }

    /**
     * @throws ExportDataException
     */
    public function replace(string $key, mixed $value): static
    {
        if (array_key_exists($key, $this->mappings) === false) {
            throw new ExportDataException('Data key not found.');
        }

        $this->mappings[$key] = $value;
        return $this;
    }

    public function remove(string $key): static
    {
        if (array_key_exists($key, $this->mappings)) {
            unset($this->mappings[$key]);
        }

        return $this;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        if ($phoneNumber != null) {
            $this->phoneNumber = str_replace(' ', '', str_replace('+', '', $phoneNumber));
        } else {
            $this->phoneNumber = str_replace(' ', '', str_replace('+', '', $this->getCustomer()->getCustomer()?->getDefaultBillingAddress()?->getPhoneNumber() ?? ''));
        }

        return $this;
    }

    public function getCustomer(): OrderCustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(OrderCustomerEntity $customer): static
    {
        $this->customer = $customer;
        return $this;
    }

    public function getBillingAddress(): OrderAddressEntity
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(mixed $billingAddress): static
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    public function getShippingAddress(): OrderAddressEntity
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(mixed $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;
        return $this;
    }

    public function getTaxState(): string
    {
        return $this->taxState;
    }

    public function getPaymentIdentifier(): string
    {
        return $this->paymentIdentifier;
    }

    public function setPaymentIdentifier(string $paymentIdentifier): static
    {
        $this->paymentIdentifier = $paymentIdentifier;
        return $this;
    }

    public function getShipmentIdentifier(): string
    {
        return $this->shipmentIdentifier;
    }

    public function setShipmentIdentifier(string $shippingIdentifier): static
    {
        $this->shipmentIdentifier = $shippingIdentifier;
        return $this;
    }
}