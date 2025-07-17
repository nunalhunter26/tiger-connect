<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Model;

use TigerMedia\TigerConnect\Exception\DebtorException;

class ExportDebtorModel
{
    /** @var mixed[] $data */
    private array $data = [];

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @throws DebtorException
     */
    public function add(string $key, mixed $value): static
    {
        if (array_key_exists($key, $this->data)) {
            throw new DebtorException('Duplicate key: ' . $key);
        }

        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @throws DebtorException
     */
    public function update(string $key, mixed $value): static
    {
        if (array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
            return $this;
        }

        throw new DebtorException('No existing key to update.');
    }

    public function remove(string $key): static
    {
        unset($this->data[$key]);
        return $this;
    }
}