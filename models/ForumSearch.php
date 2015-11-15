<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * ForumSearch model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
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
     * Checks if User of given ID is moderator of this forum.
     * @param integer $user_id
     * @return boolean
     */
    public function isMod($user_id = null)
    {
        return (new Query)->from(Mod::tableName())->where(['forum_id' => $this->id, 'user_id' => $user_id])->exists();
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