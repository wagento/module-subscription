<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateProductAttributes implements DataPatchInterface
{
    /**
     * ModuleDataSetupInterface.
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply new attribute function.
     *
     * @return void|CreateProductAttributes
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

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
                'group' => 'Subscription Options',
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
                'group' => 'Subscription Options',
            ]
        );
    }

    /**
     * Get dependencies function.
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases function.
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
