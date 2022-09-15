<?php

namespace yii2dev\ar\softdelete\tests;

use Yii;
use yii\console\Application;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

/**
 * Base class for the test cases.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();

        $this->setupTestDbData();
    }

    protected function tearDown(): void
    {
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     *
     * @param array  $config   The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication(array $config = [], string $appClass = Application::class): void
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => $this->getVendorPath(),
            'components' => [
                'db' => [
                    'class' => Connection::class,
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ], $config));
    }

    /**
     * @return string vendor path
     */
    protected function getVendorPath()
    {
        return dirname(__DIR__) . '/vendor';
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }

    /**
     * Setup tables for test ActiveRecord.
     */
    protected function setupTestDbData()
    {
        $db = Yii::$app->getDb();

        // Structure :

        $db->createCommand()
            ->createTable('Category', [
                'id' => 'pk',
                'name' => 'string',
                'isDeleted' => 'boolean',
            ])
            ->execute();

        $db->createCommand()
            ->createTable('Item', [
                'id' => 'pk',
                'categoryId' => 'integer',
                'name' => 'string',
                'isDeleted' => 'boolean DEFAULT 0',
                'deletedAt' => 'integer',
                'version' => 'integer',
            ])
            ->execute();

        // Data :
        $categoryIds = [
            $db->getSchema()->insert('Category', ['name' => 'category1', 'isDeleted' => false])['id'],
            $db->getSchema()->insert('Category', ['name' => 'category2', 'isDeleted' => false])['id'],
            $db->getSchema()->insert('Category', ['name' => 'category3', 'isDeleted' => false])['id'],
        ];

        $db->createCommand()->batchInsert('Item', ['name', 'categoryId'], [
            ['item1', $categoryIds[0]],
            ['item2', $categoryIds[1]],
        ])->execute();
    }
}
