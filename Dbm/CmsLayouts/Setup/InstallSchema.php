<?php

namespace Dbm\CmsLayouts\Setup;

use Dbm\CmsLayouts\Model\Config\Source\Template;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Psr\Log\LoggerInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Dbm\CmsLayouts\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var Template
     */
    protected $template;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * InstallSchema constructor.
     *
     * @param Template $template
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        Template $template,
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->template = $template;
        $this->fileSystem = $filesystem;
    }

    /**
     * install tables
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('dbm_cmslayouts_cms')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('dbm_cmslayouts_cms'))
                ->addColumn(
                    'cms_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Cms ID'
                )
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable => false'], 'Cms Name')
                ->addColumn('status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Status')
                ->addColumn('type', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Cms Type')
                ->addColumn('cms_serialize_data', Table::TYPE_TEXT, 10000, [], 'Cms Serialize')
                ->addColumn('store_ids', Table::TYPE_TEXT, 255, [])
                ->addColumn('image', Table::TYPE_TEXT, 255, [], 'Cms Image')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Cms Created At')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Cms Updated At')
                ->setComment('Cms Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('dbm_cmslayouts_cms'),
                $setup->getIdxName(
                    $installer->getTable('dbm_cmslayouts_cms'),
                    ['name', 'image'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['name', 'image'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        if (!$installer->tableExists('dbm_cmslayouts_layouts')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('dbm_cmslayouts_layouts'))
                ->addColumn(
                    'layouts_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Layouts ID'
                )
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable => false'], 'Layouts Name')
                ->addColumn('status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Status')
                ->addColumn('location', Table::TYPE_TEXT, 255, [])
                ->addColumn('store_ids', Table::TYPE_TEXT, 255, [])
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Layouts Created At')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Layouts Updated At')
                ->setComment('Layouts Table');

            $installer->getConnection()->createTable($table);
        }
        if (!$installer->tableExists('dbm_cmslayouts_cms_layouts')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('dbm_cmslayouts_cms_layouts'))
                ->addColumn('layouts_id', Table::TYPE_INTEGER, null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true], 'Layouts ID')
                ->addColumn('cms_id', Table::TYPE_INTEGER, null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true], 'Cms ID')
                ->addColumn('position', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Position')
                ->addIndex($installer->getIdxName('dbm_cmslayouts_cms_layouts', ['layouts_id']), ['layouts_id'])
                ->addIndex($installer->getIdxName('dbm_cmslayouts_cms_layouts', ['cms_id']), ['cms_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'dbm_cmslayouts_cms_layouts',
                        'layouts_id',
                        'dbm_cmslayouts_layouts',
                        'layouts_id'
                    ),
                    'layouts_id',
                    $installer->getTable('dbm_cmslayouts_layouts'),
                    'layouts_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'dbm_cmslayouts_cms_layouts',
                        'cms_id',
                        'dbm_cmslayouts_cms',
                        'cms_id'
                    ),
                    'cms_id',
                    $installer->getTable('dbm_cmslayouts_cms'),
                    'cms_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName(
                        'dbm_cmslayouts_cms_layouts',
                        [
                            'layouts_id',
                            'cms_id',
                        ],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'layouts_id',
                        'cms_id',
                    ],
                    [
                        'type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                    ]
                )
                ->setComment('Layouts To Cms Link Table');
            $installer->getConnection()->createTable($table);
        }

        $this->copyDemoImage();

        $installer->endSetup();
    }

    /**
     * Copy image demo
     */
    private function copyDemoImage()
    {
        try {
            $mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
            $url = 'dbm/cmslayouts/cms/demo/';
            $mediaDirectory->create($url);
            $demos = $this->template->toOptionArray();
            foreach ($demos as $demo) {
                $targetPath = $mediaDirectory->getAbsolutePath($url . $demo['value']);
                $DS = DIRECTORY_SEPARATOR;
                $oriPath = dirname(__DIR__) . $DS . 'view' . $DS . 'adminhtml' . $DS . 'web' . $DS . 'images' . $DS . $demo['value'];
                $mediaDirectory->getDriver()->copy($oriPath, $targetPath);
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
