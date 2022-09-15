<?php

namespace yii2dev\ar\softdelete\tests\data;

use yii\db\ActiveRecord;
use yii2dev\ar\softdelete\SoftDeleteBehavior;

/**
 * @property int    $id
 * @property string $name
 * @property bool   $isDeleted
 * @property int    $deletedAt
 * @property int    $version
 */
class VersionedItem extends ActiveRecord
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
    public function optimisticLock()
    {
        return 'version';
    }
}
