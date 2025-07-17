<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Core\Content\Logger;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class LoggerEntity extends Entity
{
    use EntityIdTrait;

    protected string $orderNumber;
    protected string $message;
    protected string $level;

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): LoggerEntity
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): LoggerEntity
    {
        $this->message = $message;
        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): LoggerEntity
    {
        $this->level = $level;
        return $this;
    }
}