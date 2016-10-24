<?php

namespace bizley\podium\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class UserSearch extends User
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['username', 'email'], 'safe'],
            [['status'], 'in', 'range' => array_keys(User::getStatuses())],
            [['role'], 'in', 'range' => array_keys(User::getRoles())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @inheritdoc
     */
    public function search($params, $active = false, $mods = false)
    {
        $query = User::find();
        if ($active) {
            $query->andWhere(['!=', 'status', User::STATUS_REGISTERED]);
        }
        if ($mods) {
            $query->andWhere(['role' => [User::ROLE_ADMIN, User::ROLE_MODERATOR]]);
        }

        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_ASC];
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query
            ->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['role' => $this->role])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'username', $this->username]);

        return $dataProvider;
    }
}
