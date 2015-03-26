<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use yii\helpers\HtmlPurifier;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use bizley\podium\components\Helper;
use bizley\podium\models\User;

/**
 * Message model
 *
 * @property integer $id
 * @property integer $sender
 * @property integer $receiver
 * @property string $topic
 * @property string $content
 * @property integer $sender_status
 * @property integer $receiver_status
 * @property integer $updated_at
 * @property integer $created_at
 */
class Message extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_message}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receiver', 'topic', 'content'], 'required'],
            ['content', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process($value, Helper::podiumPurifier());
            }],
        ];
    }
    
    public function getSender()
    {
        return $this->hasOne(User::className(), ['sender' => 'id']);
    }
    
    public function getReceiver()
    {
        return $this->hasOne(User::className(), ['receiver' => 'id']);
    }

    public function search($where, $params)
    {
        $query = self::find()->where($where);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

//        $query->andFilterWhere(['id' => $this->id])
//                ->andFilterWhere(['status' => $this->status])
//                ->andFilterWhere(['role' => $this->role])
//                ->andFilterWhere(['like', 'email', $this->email])
//                ->andFilterWhere(['like', 'username', $this->username]);

        return $dataProvider;
    }
}
