<?php

namespace yii2dev\ar\softdelete\tests\data;

use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii2dev\ar\softdelete\SoftDeleteBehavior;

/**
 * @property int      $id
 * @property int      $categoryId
 * @property string   $name
 * @property bool     $isDeleted
 * @property int      $deletedAt
 * @property int      $version
 * @property Category $category
 */
class Item extends ActiveRecord
{
    /**
     * @var bool whether to throw {@see onDeleteExceptionClass} exception on {@see delete()}
     */
    public bool $throwOnDeleteException = false;
    /**
     * @var string class name of the exception to be thrown on delete
     */
    public string $onDeleteExceptionClass = IntegrityException::class;

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
                'allowDeleteCallback' => function ($model) {
                    return $model->name === 'allow-delete';
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['categoryId', 'numeric'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        if ($this->throwOnDeleteException) {
            $className = $this->onDeleteExceptionClass;
            throw new $className('Emulation');
        }

        return parent::beforeDelete();
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'categoryId']);
    }
}
