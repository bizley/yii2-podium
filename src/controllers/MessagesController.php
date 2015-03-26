<?php

namespace bizley\podium\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use bizley\podium\models\Message;
use bizley\podium\behaviors\FlashBehavior;

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
        $searchModel  = new Message();
        $dataProvider = $searchModel->search(['receiver' => Yii::$app->user->id], Yii::$app->request->get());
        
        return $this->render('inbox', [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel
        ]);
    }
}
                