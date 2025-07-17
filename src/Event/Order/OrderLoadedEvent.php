<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Event\Order;

use Symfony\Contracts\EventDispatcher\Event;
use TigerMedia\TigerConnect\Model\ExportDataModel;

class OrderLoadedEvent extends Event
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