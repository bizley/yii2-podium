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
            'query'      => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $query->join('LEFT JOIN', '{{%podium_auth_assignment}}', 'user_id=id');

//        $dataProvider->sort->attributes['author.name'] = [
//            'asc'  => ['author.name' => SORT_ASC],
//            'desc' => ['author.name' => SORT_DESC],
//        ];

        $dataProvider->sort->defaultOrder = ['id' => SORT_ASC];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['status' => $this->status]);
        $query->andFilterWhere(['like', 'email', $this->email])
                ->andFilterWhere(['like', 'username', $this->username]);

        return $dataProvider;
    }

}
        