<?php

namespace yii2dev\ar\softdelete\tests\data;

use yii\db\ActiveRecord;
use yii2dev\ar\softdelete\SoftDeleteBehavior;
use yii2dev\ar\softdelete\SoftDeleteQueryBehavior;

/**
 * @property int    $id
 * @property string $name
 * @property bool   $isDeleted
 * @property Item[] $items
 */
class Category extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'softDelete' => [
                'class' => SoftDeleteBehavior::class,
                'softDeleteAttributeValues' => [
                    'isDeleted' => true,
                ],
                'useRestoreAttributeValuesAsDefaults' => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return SoftDeleteQueryBehavior|\yii\db\ActiveQuery
     */
    public static function find()
    {
        $query = parent::find();
        $query->attachBehavior('softDelete', [
            'class' => SoftDeleteQueryBehavior::class,
        ]);

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'Category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['name', 'required'],
        ];
    }

    public function getItems()
    {
        return $this->hasMany(Item::class, ['categoryId' => 'id']);
    }
}
