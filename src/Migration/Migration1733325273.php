<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1733325273 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1733325273;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `tiger_connect_logger` (
            `id` BINARY(16) NOT NULL,
            `order_number` VARCHAR(30) NOT NULL,
            `level` VARCHAR(20) NOT NULL,
            `message` LONGTEXT NOT NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL
        )
        SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
