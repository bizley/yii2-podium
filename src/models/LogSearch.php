<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

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
            [['id', 'level', 'model', 'blame'], 'integer'],
            [['category', 'prefix', 'message'], 'string'],
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
