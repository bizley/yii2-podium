<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

class LogSearch extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_log}}';
    }
    
    public function rules()
    {
        return [
            [['id', 'level', 'model', 'blame'], 'integer'],
            [['category', 'prefix', 'message'], 'string'],
        ];
    }

    public function search($params)
    {
        $query = static::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id])
                ->andFilterWhere(['level' => $this->level])
                ->andFilterWhere(['model' => $this->model])
                ->andFilterWhere(['blame' => $this->blame])
                ->andFilterWhere(['like', 'category', $this->category])
                ->andFilterWhere(['like', 'prefix', $this->prefix])
                ->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }

}
