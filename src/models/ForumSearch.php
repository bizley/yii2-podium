<?php

namespace bizley\podium\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class ForumSearch extends Forum
{

    public function rules()
    {
        return [
            ['name', 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function isMod($user_id)
    {
        return (new Query)->from(Mod::tableName())->where(['forum_id' => $this->id, 'user_id' => $user_id])->exists();
    }
    
    public function searchForMods($params)
    {
        $query = self::find();

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