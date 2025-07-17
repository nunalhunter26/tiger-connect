<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Migration;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Content\MailTemplate\MailTemplateTypes;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1744178818 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1744178818;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $exists = $connection->fetchOne('SELECT LOWER(HEX(`id`)) FROM `mail_template` WHERE `id` = :id', ['id' => Uuid::fromHexToBytes(Uuid::fromStringToHex('TigerConnect_MailTemplate'))]);

        if ($exists !== false) {
            return;
        }

        $mailTemplateTypeId = $connection->fetchOne('SELECT LOWER(HEX(`id`)) FROM `mail_template_type` WHERE `technical_name` = :name', [
            'name' => MailTemplateTypes::MAILTYPE_CONTACT_FORM
        ]);

        if ($mailTemplateTypeId === false) {
            return;
        }

        $connection->beginTransaction();
        $connection->executeStatement("
            INSERT INTO `mail_template` (`id`, `mail_template_type_id`, `system_default`, `created_at`)
            VALUES (:id, :templateTypeId, 0, :createdAt)",
            [
                'id'             => Uuid::fromHexToBytes(Uuid::fromStringToHex('TigerConnect_MailTemplate')),
                'templateTypeId' => Uuid::fromHexToBytes($mailTemplateTypeId),
                'createdAt'      => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
            ]
        );

        $connection->executeStatement("
                INSERT INTO `mail_template_translation` (`mail_template_id`, `language_id`, `sender_name`, `subject`, `description`, `content_html`, `content_plain`, `created_at`)
                VALUES (:mailTemplateId, :languageId, :senderName, :subject, :description, :contentHtml, :contentPlain, :createdAt)",
            [
                'mailTemplateId' => Uuid::fromHexToBytes(Uuid::fromStringToHex('TigerConnect_MailTemplate')),
                'languageId' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'senderName' => '{{ salesChannel.name }}',
                'subject' => 'ERP Order Export Failed',
                'description' => 'TigerConnect - Failed Export Mail Template',
                'contentHtml' => file_get_contents(__DIR__ . '/EmailTemplate/OrderExportFailedTemplate.html.twig'),
                'contentPlain' => '',
                'createdAt' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
            ]
        );
        $connection->commit();
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
