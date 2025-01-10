<?php

namespace Laith\ShareCart\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddSharedCartTable implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * Constructor
     *
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * Apply Patch
     *
     * @return void
     */
    public function apply()
    {
        $setup = $this->schemaSetup;
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

    /**
     * Retrieve list of dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Retrieve aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
