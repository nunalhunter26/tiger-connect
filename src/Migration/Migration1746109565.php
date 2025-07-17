<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1746109565 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1746109565;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            "UPDATE `custom_field` SET `config` = JSON_SET(config, '$.disabled', true) WHERE `id`=:processedId OR `id`=:orderIdentifierId",
            [
                'processedId'       => Uuid::fromHexToBytes(Uuid::fromStringToHex('TigerConnectProcessed')),
                'orderIdentifierId' => Uuid::fromHexToBytes(Uuid::fromStringToHex('TigerConnectErpOrderNumber'))
            ]
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
