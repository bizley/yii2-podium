<?php

namespace bizley\podium\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class PostSearch extends Post
{

    public function rules()
    {
        return [
            [['username', 'email'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $active = false, $mods = false)
    {
        $query = User::find();
        if ($active) {
            $query->andWhere(['!=', 'status', User::STATUS_REGISTERED]);
        }
        if ($mods) {
            $query->andWhere(['role' => [User::ROLE_ADMIN, User::ROLE_MODERATOR]]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_ASC];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id])
                ->andFilterWhere(['status' => $this->status])
                ->andFilterWhere(['role' => $this->role])
                ->andFilterWhere(['like', 'email', $this->email])
                ->andFilterWhere(['like', 'username', $this->username]);

        return $dataProvider;
    }

}