<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Event\Customer;

use Symfony\Contracts\EventDispatcher\Event;
use TigerMedia\TigerConnect\Model\ExportDataModel;
use TigerMedia\TigerConnect\Model\ExportDebtorModel;

class AfterDebtorMappingEvent extends Event
{
    private ExportDataModel $exportDataModel;
    private ExportDebtorModel $debtorModel;

    public function __construct(
        ExportDataModel $exportDataModel,
        ExportDebtorModel $debtorModel
    )
    {
        $this->exportDataModel = $exportDataModel;
        $this->debtorModel = $debtorModel;
    }

    public function getExportDataModel(): ExportDataModel
    {
        return $this->exportDataModel;
    }

    public function getDebtorData(): ExportDebtorModel
    {
        return $this->debtorModel;
    }
}