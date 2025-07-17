<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Core\Content\Logger;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class LoggerDefinition extends EntityDefinition
{
    const ENTITY_NAME = 'tiger_connect_logger';
    const ENTITY_PROPERTY_NAME = 'tigerConnectLogger';
    const LOG_INFO = 'INFO';
    const LOG_ERROR = 'ERROR';
    const LOG_CRITICAL = 'CRITICAL';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return LoggerEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('order_number', 'orderNumber'))->addFlags(new Required()),
            new StringField('message', 'message'),
            new StringField('level', 'level'),
            new CreatedAtField(),
            new UpdatedAtField()
        ]);
    }
}