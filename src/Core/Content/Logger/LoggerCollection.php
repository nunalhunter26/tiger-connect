<?php declare(strict_types=1);

namespace TigerMedia\Core\Content\Logger;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use TigerMedia\TigerConnect\Core\Content\Logger\LoggerEntity;

/**
 * @extends EntityCollection<LoggerEntity>
 */
class LoggerCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return LoggerEntity::class;
    }
}