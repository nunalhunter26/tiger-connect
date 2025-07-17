<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Service;

use Shopware\Core\Content\Mail\Service\MailService as ShopwareMailService;
use Shopware\Core\Content\MailTemplate\MailTemplateCollection;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use TigerMedia\TigerConnect\Exception\OrderException;
use TigerMedia\TigerConnect\Model\ExportDataModel;
use TigerMedia\TigerConnect\TigerConnect;

class MailService
{
    /**
     * @param SystemConfigService $systemConfigService
     * @param EntityRepository<MailTemplateCollection> $mailTemplateRepository
     * @param ShopwareMailService $mailService
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly EntityRepository    $mailTemplateRepository,
        private readonly ShopwareMailService $mailService
    )
    {
    }

    /**
     * @throws OrderException
     */
    public function send(ExportDataModel $dataModel): void
    {
        /** @var MailTemplateEntity|null $mailTemplate */
        $mailTemplate = $this->mailTemplateRepository->search(new Criteria([Uuid::fromStringToHex('TigerConnect_MailTemplate')]), $dataModel->getContext())->first();

        if ($mailTemplate === null) {
            throw new OrderException('Unable to send mail - missing mail template.');
        }

        $mailRecipients = $this->systemConfigService->get(TigerConnect::CONFIG_PREFIX . 'mailSelectConfig', $dataModel->getOrder()->getSalesChannelId());

        if (empty($mailRecipients)) {
            throw new OrderException('Unable to send mail - missing mail recipients.');
        }

        $data = new DataBag();
        $data->set('senderName', $mailTemplate->getTranslation('senderName'));
        $data->set('contentHtml', $mailTemplate->getTranslation('contentHtml'));
        $data->set('contentPlain', $mailTemplate->getTranslation('contentHtml'));
        $data->set('subject', $mailTemplate->getTranslation('subject'));
        $data->set('salesChannelId', $dataModel->getOrder()->getSalesChannelId());
        $data->set('recipients', array_reduce($mailRecipients, function($carry, $recipient) {
            $carry[$recipient['email']] = $recipient['firstName'] . ' ' . $recipient['lastName'];
            return $carry;
        }));

        $this->mailService->send($data->all(), $dataModel->getContext(), [
            'order'        => $dataModel->getOrder(),
            'salesChannel' => $dataModel->getOrder()->getSalesChannel()
        ]);
    }
}