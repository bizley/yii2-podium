<?php

namespace bizley\podium\models;

use bizley\podium\db\Query;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ForumSearch model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class ForumSearch extends Forum
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [['name', 'string']];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Checks if user of given ID is moderator of this forum.
     * @param int $userId
     * @return bool
     */
    public function isMod($userId = null)
    {
        return (new Query())->from(Mod::tableName())->where(['forum_id' => $this->id, 'user_id' => $userId])->exists();
    }

    /**
     * Searches for forums on admin page.
     * @param type $params
     * @return ActiveDataProvider
     */
    public function searchForMods($params)
    {
        $query = static::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 10,
                'forcePageParam' => false
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['name' => SORT_ASC];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['name' => $this->name]);

        return $dataProvider;
    }
}
