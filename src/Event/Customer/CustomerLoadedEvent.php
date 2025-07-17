<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Event\Customer;

use stdClass;
use Symfony\Contracts\EventDispatcher\Event;
use TigerMedia\TigerConnect\Model\ExportDataModel;

class CustomerLoadedEvent extends Event
{
    private ExportDataModel $model;

    /** @var stdClass[] $debtor */
    private array $debtor;

    /**
     * @param ExportDataModel $model
     * @param stdClass[] $debtor
     */
    public function __construct(
        ExportDataModel $model,
        array $debtor
    )
    {
        $this->model = $model;
        $this->debtor = $debtor;
    }

    public function getModel(): ExportDataModel
    {
        return $this->model;
    }

    /**
     * @return stdClass[]
     */
    public function getDebtor(): array
    {
        return $this->debtor;
    }

    /**
     * @param stdClass[] $debtor
     * @return void
     */
    public function setDebtor(array $debtor): void
    {
        $this->debtor = $debtor;
    }
}