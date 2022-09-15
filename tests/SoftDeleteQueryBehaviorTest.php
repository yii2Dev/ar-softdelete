<?php

namespace yii2dev\ar\softdelete\tests;

use yii2dev\ar\softdelete\tests\data\Category;
use yii2dev\ar\softdelete\tests\data\Item;

class SoftDeleteQueryBehaviorTest extends TestCase
{
    public function testWhereDeleted()
    {
        $this->assertCount(0, Category::find()->deleted()->all());

        Category::find()->limit(1)->one()->softDelete();
        $this->assertCount(1, Category::find()->deleted()->all());
    }

    public function testWhereNotDeleted()
    {
        $this->assertCount((int) Category::find()->count(), Category::find()->notDeleted()->all());

        Category::find()->limit(1)->one()->softDelete();
        $this->assertCount((int) Category::find()->count() - 1, Category::find()->notDeleted()->all());
    }

    public function testJoin()
    {
        Category::find()->limit(1)->one()->softDelete();

        $categories = Category::find()
            ->joinWith('items')
            ->deleted()
            ->all();

        $this->assertCount(1, $categories);

        $items = Item::find()
            ->innerJoinWith(['category' => function ($query) {
                $query->deleted();
            }])
            ->all();

        $this->assertCount(1, $items);
    }

    /**
     * Data provider for {@see testFilterDeleted()}.
     *
     * @return array test data
     */
    public function dataProviderFilterDeleted()
    {
        return [
            ['', 2],
            [null, 2],
            ['1', 1],
            [true, 1],
            ['0', 3],
            [false, 3],
            ['all', 3],
        ];
    }

    /**
     * @dataProvider dataProviderFilterDeleted
     *
     * @param mixed $filterDeleted
     * @param int   $expectedCount
     */
    public function testFilterDeleted($filterDeleted, $expectedCount)
    {
        Category::find()->limit(1)->one()->softDelete();

        $this->assertCount($expectedCount, Category::find()->filterDeleted($filterDeleted)->all());
    }

    /**
     * @depends testWhereDeleted
     * @depends testJoin
     */
    public function testAlias()
    {
        Category::find()->limit(1)->one()->softDelete();

        $this->assertCount(1, Category::find()->alias('t1')->deleted()->all());

        $rows = Category::find()
            ->alias('t1')
            ->deleted()
            ->leftJoin(Item::tableName() . ' t2', 't1.id=t2.categoryId')
            ->all();

        $this->assertCount(1, $rows);
    }
}
