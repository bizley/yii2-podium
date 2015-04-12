<?php

namespace bizley\podium\controllers;

use bizley\podium\models\Category;
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
}
