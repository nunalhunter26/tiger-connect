<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1734630524 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1734630524;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
            ALTER TABLE `tiger_connect_logger`
            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            ADD PRIMARY KEY (`id`);
        SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
