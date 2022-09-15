<?php

namespace yii2dev\ar\softdelete;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveQueryTrait;
use yii\db\BaseActiveRecord;

/**
 * SoftDeleteQueryBehavior provides support for querying "soft" deleted ActiveRecord models.
 *
 * This behavior should be attached to {@see \yii\db\ActiveQueryInterface} instance.
 * In order to function properly {@see SoftDeleteBehavior} should be attached to the ActiveRecord class this query relates to.
 *
 * The easiest way to apply this behavior is its manual attachment to the query instance at {@see \yii\db\BaseActiveRecord::find()}
 * method. For example:
 *
 * ```php
 * use yii2dev\ar\softdelete\SoftDeleteBehavior;
 * use yii2dev\ar\softdelete\SoftDeleteQueryBehavior;
 *
 * class Item extend \yii\db\ActiveRecord
 * {
 *     // ...
 *     public function behaviors()
 *     {
 *         return [
 *             'softDeleteBehavior' => [
 *                 'class' => SoftDeleteBehavior::class,
 *                 // ...
 *             ],
 *         ];
 *     }
 *
 *     public static function find()
 *     {
 *         $query = parent::find();
 *         $query->attachBehavior('softDelete', SoftDeleteQueryBehavior::class);
 *         return $query;
 *     }
 * }
 * ```
 *
 * In case you already define custom query class for your active record, you can move behavior attachment there.
 * For example:
 *
 * ```php
 * use yii2dev\ar\softdelete\SoftDeleteBehavior;
 * use yii2dev\ar\softdelete\SoftDeleteQueryBehavior;
 *
 * class Item extend \yii\db\ActiveRecord
 * {
 *     // ...
 *     public function behaviors()
 *     {
 *         return [
 *             'softDeleteBehavior' => [
 *                 'class' => SoftDeleteBehavior::class,
 *                 // ...
 *             ],
 *         ];
 *     }
 *
 *     public static function find()
 *     {
 *         return new ItemQuery(get_called_class());
 *     }
 * }
 *
 * class ItemQuery extends \yii\db\ActiveQuery
 * {
 *     public function behaviors()
 *     {
 *         return [
 *             'softDelete' => [
 *                 'class' => SoftDeleteQueryBehavior::class,
 *             ],
 *         ];
 *     }
 * }
 * ```
 *
 * Basic usage example:
 *
 * ```php
 * // Find all soft-deleted records:
 * $deletedItems = Item::find()->deleted()->all();
 *
 * // Find all not soft-deleted records:
 * $notDeletedItems = Item::find()->notDeleted()->all();
 *
 * // Filter records by soft-deleted criteria:
 * $filteredItems = Item::find()->filterDeleted(Yii::$app->request->get('filter_deleted'))->all();
 * ```
 *
 * @see SoftDeleteBehavior
 *
 * @property ActiveQueryInterface|ActiveQueryTrait $owner               owner ActiveQuery instance.
 * @property array                                 $deletedCondition    filter condition for 'soft-deleted' records.
 * @property array                                 $notDeletedCondition filter condition for not 'soft-deleted' records.
 *
 * @since 1.0.3
 */
class SoftDeleteQueryBehavior extends Behavior
{
    /**
     * @var array filter condition for 'soft-deleted' records
     */
    private array $_deletedCondition = [];
    /**
     * @var array filter condition for not 'soft-deleted' records
     */
    private array $_notDeletedCondition = [];

    /**
     * @return array filter condition for 'soft-deleted' records
     */
    public function getDeletedCondition(): array
    {
        if (empty($this->_deletedCondition)) {
            $this->_deletedCondition = $this->defaultDeletedCondition();
        }

        return $this->_deletedCondition;
    }

    /**
     * @param array $deletedCondition filter condition for 'soft-deleted' records
     */
    public function setDeletedCondition(array $deletedCondition): void
    {
        $this->_deletedCondition = $deletedCondition;
    }

    /**
     * @throws InvalidConfigException
     *
     * @return array filter condition for not 'soft-deleted' records
     */
    public function getNotDeletedCondition(): array
    {
        if (empty($this->_notDeletedCondition)) {
            $this->_notDeletedCondition = $this->defaultNotDeletedCondition();
        }

        return $this->_notDeletedCondition;
    }

    /**
     * @param array $notDeletedCondition filter condition for not 'soft-deleted' records
     */
    public function setNotDeletedCondition(array $notDeletedCondition): void
    {
        $this->_notDeletedCondition = $notDeletedCondition;
    }

    /**
     * Filters query to return only 'soft-deleted' records.
     *
     * @return ActiveQueryInterface|static query instance
     */
    public function deleted(): ActiveQueryInterface|static
    {
        return $this->addFilterCondition($this->getDeletedCondition());
    }

    /**
     * Filters query to return only not 'soft-deleted' records.
     *
     * @throws InvalidConfigException
     *
     * @return ActiveQueryInterface|static query instance
     */
    public function notDeleted(): ActiveQueryInterface|static
    {
        return $this->addFilterCondition($this->getNotDeletedCondition());
    }

    /**
     * Applies `deleted()` or `notDeleted()` scope to the query regardless to passed filter value.
     * If an empty value is passed - only not deleted records will be queried.
     * If value matching non empty int passed - only deleted records will be queried.
     * If non empty value matching int zero passed (e.g. `0`, `'0'`, `'all'`, `false`) - all records will be queried.
     *
     * @param mixed $deleted filter value
     *
     * @throws InvalidConfigException
     *
     * @return ActiveQueryInterface|SoftDeleteQueryBehavior
     */
    public function filterDeleted(mixed $deleted): ActiveQueryInterface|static
    {
        if ($deleted === '' || $deleted === null || $deleted === []) {
            return $this->notDeleted();
        }

        if ((int) $deleted) {
            return $this->deleted();
        }

        return $this->owner;
    }

    /**
     * Adds given filter condition to the owner query.
     *
     * @param array $condition filter condition
     *
     * @throws InvalidConfigException
     *
     * @return ActiveQueryInterface|static owner query instance
     */
    protected function addFilterCondition(array $condition): ActiveQueryInterface|static
    {
        $condition = $this->normalizeFilterCondition($condition);

        if (method_exists($this->owner, 'andOnCondition')) {
            return $this->owner->andOnCondition($condition);
        }

        return $this->owner->andWhere($condition);
    }

    /**
     * Generates default filter condition for 'deleted' records.
     *
     * @see deletedCondition
     *
     * @return array filter condition
     */
    protected function defaultDeletedCondition(): array
    {
        $modelInstance = $this->getModelInstance();

        $condition = [];
        foreach ($modelInstance->softDeleteAttributeValues as $attribute => $value) {
            if (!is_scalar($value) && is_callable($value)) {
                $value = call_user_func($value, $modelInstance);
            }
            $condition[$attribute] = $value;
        }

        return $condition;
    }

    /**
     * Generates default filter condition for not 'deleted' records.
     *
     * @see notDeletedCondition
     *
     * @throws InvalidConfigException on invalid configuration
     *
     * @return array filter condition
     */
    protected function defaultNotDeletedCondition(): array
    {
        $modelInstance = $this->getModelInstance();

        $condition = [];

        if (empty($modelInstance->restoreAttributeValues)) {
            foreach ($modelInstance->softDeleteAttributeValues as $attribute => $value) {
                if (is_bool($value)) {
                    $restoreValue = !$value;
                } elseif (is_int($value)) {
                    if ($value === 1) {
                        $restoreValue = 0;
                    } elseif ($value === 0) {
                        $restoreValue = 1;
                    } else {
                        $restoreValue = $value + 1;
                    }
                } elseif (!is_scalar($value) && is_callable($value)) {
                    $restoreValue = null;
                } else {
                    throw new InvalidConfigException('Unable to automatically determine not delete condition, "' . get_class($this) . '::$notDeletedCondition" should be explicitly set.');
                }

                $condition[$attribute] = $restoreValue;
            }
        } else {
            foreach ($modelInstance->restoreAttributeValues as $attribute => $value) {
                if (!is_scalar($value) && is_callable($value)) {
                    $value = call_user_func($value, $modelInstance);
                }
                $condition[$attribute] = $value;
            }
        }

        return $condition;
    }

    /**
     * Returns static instance for the model, which owner query is related to.
     *
     * @return BaseActiveRecord|SoftDeleteBehavior
     */
    protected function getModelInstance(): BaseActiveRecord|SoftDeleteBehavior
    {
        return call_user_func([$this->owner->modelClass, 'instance']);
    }

    /**
     * Normalizes raw filter condition adding table alias for relation database query.
     *
     * @param array $condition raw filter condition
     *
     * @throws InvalidConfigException
     *
     * @return array normalized condition
     */
    protected function normalizeFilterCondition(array $condition): array
    {
        if (method_exists($this->owner, 'getTablesUsedInFrom')) {
            $fromTables = $this->owner->getTablesUsedInFrom();
            $alias = array_keys($fromTables)[0];

            foreach ($condition as $attribute => $value) {
                if (is_numeric($attribute) || strpos($attribute, '.') !== false) {
                    continue;
                }

                unset($condition[$attribute]);
                if (strpos($attribute, '[[') === false) {
                    $attribute = '[[' . $attribute . ']]';
                }
                $attribute = $alias . '.' . $attribute;
                $condition[$attribute] = $value;
            }
        }

        return $condition;
    }
}
