<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    private $eavSetup;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $eavSetup
     */
    public function __construct(EavSetupFactory $eavSetupFactory, ModuleDataSetupInterface $eavSetup)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavSetup = $eavSetup;
    }

    /**
     * Install table function.
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavSetup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'subscription_configurate',
            [
                'type' => 'varchar',
                'backend' => \Wagento\Subscription\Model\Attribute\Backend\Subscription::class,
                'frontend' => '',
                'label' => 'Subscription Option',
                'input' => 'select',
                'source' => \Wagento\Subscription\Model\Subscription\Attribute\Source\SubscriptionOptions::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,downloadable,virtual',
                'group' => 'Subscription Options'
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'subscription_attribute_product',
            [
                'type' => 'varchar',
                'backend' => \Wagento\Subscription\Model\Attribute\Backend\Subscription::class,
                'frontend' => '',
                'label' => 'Subscription Name',
                'input' => 'select',
                'source' => \Wagento\Subscription\Model\Subscription\Attribute\Source\SubscriptionList::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,downloadable,virtual',
                'group' => 'Subscription Options'
            ]
        );
    }
}
