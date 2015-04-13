<?php

namespace bizley\podium\controllers;

use bizley\podium\models\Category;
use bizley\podium\models\Forum;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => false,
                        'matchCallback' => function ($rule, $action) {
                            return !$this->module->getInstalled();
                        },
                        'denyCallback' => function ($rule, $action) {
                            return $this->redirect(['install/run']);
                        }
                    ],
                    [
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }
    
    public function actionIndex()
    {
        $dataProvider = (new Category())->search();
        
        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }
    
    public function actionCategory($id = null, $slug = null)
    {
        if (!is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error('Sorry! We can not find the category you are looking for.');
            return $this->redirect(['index']);
        }
        
        $conditions = ['id' => (int)$id, 'slug' => $slug];
        if (Yii::$app->user->isGuest) {
            $conditions['visible'] = 1;
        }
        $model = Category::findOne($conditions);
        
        if (!$model) {
            $this->error('Sorry! We can not find the category you are looking for.');
            return $this->redirect(['index']);
        }
        
        return $this->render('category', [
            'model' => $model
        ]);
    }
    
    public function actionForum($cid = null, $id = null, $slug = null)
    {
        if (!is_numeric($cid) || $cid < 1 || !is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error('Sorry! We can not find the forum you are looking for.');
            return $this->redirect(['index']);
        }
        
        $conditions = ['id' => (int)$cid];
        if (Yii::$app->user->isGuest) {
            $conditions['visible'] = 1;
        }
        $category = Category::findOne($conditions);
        
        if (!$category) {
            $this->error('Sorry! We can not find the forum you are looking for.');
            return $this->redirect(['index']);
        }
        else {
            $conditions = ['id' => (int)$id];
            if (Yii::$app->user->isGuest) {
                $conditions['visible'] = 1;
            }
            $model = Forum::findOne($conditions);
        }
        
        return $this->render('forum', [
            'model' => $model,
            'category' => $category,
        ]);
    }
}
