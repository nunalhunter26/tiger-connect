<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect;

use Exception;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopware\Core\Checkout\Shipping\ShippingMethodDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetCollection;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use TigerMedia\Base\Core\Framework\TigerPlugin;

class TigerConnect extends TigerPlugin
{
    const CONFIG_PREFIX = 'TigerConnect.config.';
    const PAYMENT_IDENTIFIER = 'tiger_connect__payment_method_erp_identifier';
    const SHIPMENT_IDENTIFIER = 'tiger_connect__shipping_method_erp_identifier';
    const CUSTOM_FIELD_SET_IDS = [
        'TigerConnectCustomFieldSet',
        'TigerConnectShippingMethodCustomFieldSet',
        'TigerConnectPaymentMethodCustomFieldSet'
    ];

    public function executeComposerCommands(): bool
    {
        return true;
    }

    /**
     * @throws Exception
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $locator = new FileLocator('Resources/config');
        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator)
        ]);
        $configLoader = new DelegatingLoader($resolver);
        $configDirectory = rtrim($this->getPath(), '/') . '/Resources/config';
        $configLoader->load($configDirectory . '/{packages}/*.yaml', 'glob');
    }

    public function activate(ActivateContext $activateContext): void
    {
        /** @var EntityRepository<CustomFieldSetCollection> $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $this->removeCustomFieldSets($customFieldSetRepository, $activateContext->getContext());
        $this->createPaymentMethodCustomFieldSets($customFieldSetRepository, $activateContext);
        $this->createOrderCustomFieldSets($customFieldSetRepository, $activateContext);
        $this->createShippingMethodCustomFieldSets($customFieldSetRepository, $activateContext);
        parent::activate($activateContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        $this->removeCustomFieldSets(
            $this->container->get('custom_field_set.repository'),
            $uninstallContext->getContext()
        );

        parent::uninstall($uninstallContext);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        $this->removeCustomFieldSets(
            $this->container->get('custom_field_set.repository'),
            $deactivateContext->getContext()
        );

        parent::deactivate($deactivateContext);
    }

    /**
     * @param EntityRepository<CustomFieldSetCollection> $customFieldSetRepository
     * @param Context $context
     * @return void
     */
    private function removeCustomFieldSets(EntityRepository $customFieldSetRepository, Context $context): void
    {
        $customFieldSetRepository->delete(array_map(function(string $id) {
            return ['id' => Uuid::fromStringToHex($id)];
        }, self::CUSTOM_FIELD_SET_IDS), $context);
    }

    /**
     * @param EntityRepository<CustomFieldSetCollection> $customFieldSetRepository
     * @param ActivateContext $activateContext
     * @param string $id
     * @param string $technicalName
     * @param string $label
     * @param string $entityName
     * @param mixed[] $customFields
     * @param bool $active
     * @return void
     */
    private function createCustomFieldSets(
        EntityRepository $customFieldSetRepository,
        ActivateContext $activateContext,
        string $id,
        string $technicalName,
        string $label,
        string $entityName,
        array $customFields,
        bool $active = true
    ): void
    {
        $customFieldSetRepository->upsert([
            [
                'id'     => $id,
                'name'   => $technicalName,
                'active' => $active,
                'config' => [
                    'label' => [
                        'en-GB' => $label
                    ]
                ],
                'customFields' => $customFields,
                'relations' => [
                    [
                        'id'         => Uuid::randomHex(),
                        'entityName' => $entityName
                    ]
                ]
            ]
        ], $activateContext->getContext());
    }

    /**
     * @param EntityRepository<CustomFieldSetCollection> $customFieldSetRepository
     * @param ActivateContext $activateContext
     * @return void
     */
    private function createOrderCustomFieldSets(EntityRepository $customFieldSetRepository, ActivateContext $activateContext): void
    {
        $this->createCustomFieldSets(
            $customFieldSetRepository,
            $activateContext,
            Uuid::fromStringToHex('TigerConnectCustomFieldSet'),
            'tiger_connect_custom_field_set',
            'TigerConnect',
            OrderDefinition::ENTITY_NAME,
            [
                [
                    'id'     => Uuid::fromStringToHex('TigerConnectProcessed'),
                    'name'   => 'tiger_connect_custom_field_set_processed',
                    'type'   => CustomFieldTypes::BOOL,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Order Processed'
                        ],
                        'componentName'       => 'sw-field',
                        'customFieldPosition' => 0
                    ]
                ],
                [
                    'id'     => Uuid::fromStringToHex('TigerConnectErpOrderNumber'),
                    'name'   => 'tiger_connect_custom_field_set_erp_order_number',
                    'type'   => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'ERP Order Number'
                        ],
                        'componentName'       => 'sw-field',
                        'customFieldPosition' => 1
                    ]
                ]
            ]
        );
    }

    /**
     * @param EntityRepository<CustomFieldSetCollection> $customFieldSetRepository
     * @param ActivateContext $activateContext
     * @return void
     */
    private function createShippingMethodCustomFieldSets(EntityRepository $customFieldSetRepository, ActivateContext $activateContext): void
    {
        $this->createCustomFieldSets(
            $customFieldSetRepository,
            $activateContext,
            Uuid::fromStringToHex('TigerConnectShippingMethodCustomFieldSet'),
            'tiger_connect__shipping_method',
            'TigerConnect',
            ShippingMethodDefinition::ENTITY_NAME,
            [
                [
                    'id'     => Uuid::fromStringToHex('TigerConnectShippingMethodErpIdentifier'),
                    'name'   => 'tiger_connect__shipping_method_erp_identifier',
                    'type'   => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'ERP Identifier'
                        ],
                        'componentName'       => 'sw-field',
                        'customFieldPosition' => 0
                    ]
                ]
            ]
        );
    }

    /**
     * @param EntityRepository<CustomFieldSetCollection> $customFieldSetRepository
     * @param ActivateContext $activateContext
     * @return void
     */
    private function createPaymentMethodCustomFieldSets(EntityRepository $customFieldSetRepository, ActivateContext $activateContext): void
    {
        $this->createCustomFieldSets(
            $customFieldSetRepository,
            $activateContext,
            Uuid::fromStringToHex('TigerConnectPaymentMethodCustomFieldSet'),
            'tiger_connect__payment_method',
            'TigerConnect',
            PaymentMethodDefinition::ENTITY_NAME,
            [
                [
                    'id'     => Uuid::fromStringToHex('TigerConnectPaymentMethodErpIdentifier'),
                    'name'   => 'tiger_connect__payment_method_erp_identifier',
                    'type'   => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'ERP Identifier'
                        ],
                        'componentName'       => 'sw-field',
                        'customFieldPosition' => 0
                    ]
                ]
            ]
        );
    }
}