<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Event;

use Symfony\Contracts\EventDispatcher\Event;
use TigerMedia\TigerConnect\Exception\ExportDataException;

class BaseDataMappingEvent extends Event
{
    /** @var mixed[] $data */
    protected array $data = [];

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @throws ExportDataException
     */
    public function add(string $key, mixed $value): static
    {
        if (array_key_exists($key, $this->data)) {
            throw new ExportDataException('Key already exists: ' . $key);
        }

        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @throws ExportDataException
     */
    public function update(string $key, mixed $value): static
    {
        if (array_key_exists($key, $this->data) === false) {
            throw new ExportDataException('Key does not exist: ' . $key);
        }

        $this->data[$key] = $value;
        return $this;

    }

    public function remove(string $key): static
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }
}