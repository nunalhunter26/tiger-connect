<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Event\LineItem;

use Symfony\Contracts\EventDispatcher\Event;
use TigerMedia\TigerConnect\Exception\LineItemException;
use TigerMedia\TigerConnect\Model\ExportDataModel;

class AfterShippingLineItemAddedEvent extends Event
{
    private ExportDataModel $model;

    /** @var mixed[] $data */
    private array $data;

    /**
     * @param ExportDataModel $model
     * @param mixed[] $data
     */
    public function __construct(
        ExportDataModel $model,
        array $data
    )
    {
        $this->model = $model;
        $this->data = $data;
    }

    public function getModel(): ExportDataModel
    {
        return $this->model;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
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