<?php
namespace Laith\Magentosharecart\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$setup->tableExists('shared_cart')) {
            $table = $setup->getConnection()->newTable($setup->getTable('shared_cart'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'token',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Unique Token'
                )
                ->addColumn(
                    'cart_data',
                    Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => false],
                    'Serialized Cart Data'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Creation Time'
                )
                ->setComment('Shared Cart Table');
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
