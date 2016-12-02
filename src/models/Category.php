<?php

namespace bizley\podium\models;

use bizley\podium\db\Query;
use bizley\podium\log\Log;
use bizley\podium\models\db\CategoryActiveRecord;
use bizley\podium\Podium;
use Exception;
use yii\data\ActiveDataProvider;

/**
 * Category model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Category extends CategoryActiveRecord
{
    /**
     * Searches users.
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = static::find();        
        if (Podium::getInstance()->user->isGuest) {
            $query->andWhere(['visible' => 1]);
        }
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];
        return $dataProvider;
    }
    
    /**
     * Returns categories.
     * @return Category[]
     */
    public function show()
    {
        $dataProvider = new ActiveDataProvider(['query' => static::find()]);
        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];
        return $dataProvider->getModels();
    }
    
    /**
     * Sets new categories order.
     * @param int $order new category sorting order number
     * @return bool
     * @since 0.2
     */
    public function newOrder($order)
    {
        try {
            $next = 0;
            $newSort = -1;
            $query = (new Query)
                        ->from(static::tableName())
                        ->where(['!=', 'id', $this->id])
                        ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
                        ->indexBy('id');
            foreach ($query->each() as $id => $forum) {
                if ($next == $order) {
                    $newSort = $next;
                    $next++;
                }
                Podium::getInstance()->db->createCommand()->update(
                        static::tableName(), ['sort' => $next], ['id' => $id]
                    )->execute();
                $next++;
            }
            if ($newSort == -1) {
                $newSort = $next;
            }
            $this->sort = $newSort;
            if (!$this->save()) {
                throw new Exception('Categories order saving error');
            }
            Log::info('Categories orded updated', $this->id, __METHOD__);
            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
