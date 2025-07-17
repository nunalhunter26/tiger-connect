<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Event\LineItem;

use Symfony\Contracts\EventDispatcher\Event;
use TigerMedia\TigerConnect\Model\ExportDataModel;

class AddCustomLineItemEvent extends Event
{
    /** @var mixed[] $data */
    private array $data = [];
    private ExportDataModel $model;

    public function __construct(
        ExportDataModel $model
    )
    {
        $this->model = $model;
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
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getModel(): ExportDataModel
    {
        return $this->model;
    }
}