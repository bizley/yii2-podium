<?php

namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\models\Message;
use bizley\podium\models\MessageSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class MessagesController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class'        => AccessControl::className(),
                'denyCallback' => function () {
                    return $this->redirect(['login']);
                },
                'rules'  => [
                    [
                        'allow'         => false,
                        'matchCallback' => function () {
                            return !$this->module->getInstalled();
                        },
                        'denyCallback' => function () {
                            return $this->redirect(['install/run']);
                        }
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'flash' => FlashBehavior::className(),
        ];
    }

    public function actionInbox()
    {
        $searchModel  = new MessageSearch();
        $dataProvider = $searchModel->searchInbox(Yii::$app->request->get());
        
        return $this->render('inbox', [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel
        ]);
    }
    
    public function actionSent()
    {
        $searchModel  = new MessageSearch();
        $dataProvider = $searchModel->searchSent(Yii::$app->request->get());
        
        return $this->render('sent', [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel
        ]);
    }
    
    public function actionDeleted()
    {
        $searchModel  = new MessageSearch();
        $dataProvider = $searchModel->searchDeleted(Yii::$app->request->get());
        
        return $this->render('deleted', [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel
        ]);
    }
    
    public function actionNew()
    {
        $model = new Message();
        
        return $this->render('new', [
                'model' => $model
        ]);
    }
}
                