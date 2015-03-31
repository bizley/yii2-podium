<?php

namespace bizley\podium\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserSearch extends User
{

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['username', 'email'], 'safe'],
            [['status'], 'in', 'range' => array_keys(User::getStatuses())],
            [['role'], 'in', 'range' => array_keys(User::getRoles())],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = User::find();

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