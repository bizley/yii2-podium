<?php

namespace bizley\podium\models;

use bizley\podium\db\ActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * LogSearch model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class LogSearch extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'level', 'model', 'user'], 'integer'],
            [['category', 'ip', 'message'], 'string'],
        ];
    }

    /**
     * Searches for logs.
     * @param array $params Attributes
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = static::find();

        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query
            ->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['level' => $this->level])
            ->andFilterWhere(['model' => $this->model])
            ->andFilterWhere(['user' => $this->user])
            ->andFilterWhere(['like', 'category', $this->category])
            ->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}
