<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Event\Order;

use Symfony\Contracts\EventDispatcher\Event;
use TigerMedia\TigerConnect\Model\ExportDataModel;

class AfterOrderSuccessExportEvent extends Event
{
    private ExportDataModel $model;
    private string $erpIdentifier;

    public function __construct(
        ExportDataModel $model,
        string $erpIdentifier
    )
    {
        $this->model = $model;
        $this->erpIdentifier = $erpIdentifier;
    }

    public function getModel(): ExportDataModel
    {
        return $this->model;
    }

    public function getErpIdentifier(): string
    {
        return $this->erpIdentifier;
    }
}