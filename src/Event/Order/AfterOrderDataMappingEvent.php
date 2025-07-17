<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Event\Order;

use Shopware\Core\Checkout\Order\OrderEntity;
use Symfony\Contracts\EventDispatcher\Event;
use TigerMedia\TigerConnect\Model\ExportDataModel;

class AfterOrderDataMappingEvent extends Event
{
    private ExportDataModel $model;

    public function __construct(
        ExportDataModel $model
    )
    {
        $this->model = $model;
    }

    public function getModel(): ExportDataModel
    {
        return $this->model;
    }
}