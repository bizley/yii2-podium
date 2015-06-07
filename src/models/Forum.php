<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use bizley\podium\components\Cache;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Forum model
 *
 * @property integer $id
 * @property integer $category_id
 * @property string $name
 * @property string $sub
 * @property string $slug
 * @property integer $visible
 * @property integer $sort
 * @property integer $updated_at
 * @property integer $created_at
 */
class Forum extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_forum}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'visible'], 'required'],
            ['visible', 'boolean'],
            [['name', 'sub'], 'validateName'],
        ];
    }
    
    /**
     * Validates name
     * Custom method is required because JS ES5 (and so do Yii 2) doesn't support regex unicode features.
     * @param string $attribute
     */
    public function validateName($attribute)
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[\w\s\p{L}]{1,255}$/u', $this->$attribute)) {
                $this->addError($attribute, Yii::t('podium/view', 'Name must contain only letters, digits, underscores and spaces (255 characters max).'));
            }
        }
    }
    
    public function getLatest()
    {
        return $this->hasOne(Post::className(), ['forum_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
    
    public function search($category_id = null)
    {
        $query = self::find();
        if ($category_id) {
            $query->where(['category_id' => $category_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];

        return $dataProvider;
    }
    
    public function getMods()
    {
        $mods = Cache::getInstance()->getElement('forum.moderators', $this->id);
        if ($mods === false) {
            $mods    = [];
            $modteam = User::find()->select(['id', 'role'])->where(['status' => User::STATUS_ACTIVE, 'role' => [User::ROLE_ADMIN, User::ROLE_MODERATOR]])->asArray()->all();

            foreach ($modteam as $user) {
                if ($user['role'] == User::ROLE_ADMIN) {
                    $mods[] = $user['id'];
                }
                else {
                    if ((new Query)->from(Mod::tableName())->where(['forum_id' => $this->id, 'user_id' => $user->id])->exists()) {
                        $mods[] = $user['id'];
                    }
                }
            }
            Cache::getInstance()->setElement('forum.moderators', $this->id, $mods);
        }
        
        return $mods;        
    }
    
    public function isMod($user_id = null)
    {
        if (Yii::$app->user->can('admin')) {
            return true;
        }
        else {
            if (in_array($user_id, $this->getMods())) {
                return true;
            }
            return false;
        }
    }
}
