<?php

namespace bizley\podium\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\models\User;
use bizley\podium\models\UserSearch;

class AdminController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow'         => false,
                        'matchCallback' => function ($rule, $action) {
                            return !$this->module->getInstalled();
                        },
                        'denyCallback' => function ($rule, $action) {
                            return $this->redirect(['install/run']);
                        }
                    ],
                    [
                        'allow' => true,
                        'roles' => ['admin']
                    ],
                ],
            ],
            'flash' => FlashBehavior::className(),
        ];
    }

    public function actionIndex()
    {
        if ($this->module->getParam('mode') == 'INSTALL') {
            $this->warning('Parameter {MODE} with {INSTALL} value found in configuration! Make sure you remove this parameter in production environment.', ['MODE' => '<code>mode</code>',
                'INSTALL' => '<code>INSTALL</code>']);
        }

        return $this->render('index');
    }

    public function actionMembers()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('members', [
                    'dataProvider' => $dataProvider,
                    'searchModel'  => $searchModel
        ]);
    }

    public function actionSettings()
    {


        return $this->render('settings');
    }

    public function actionView($id)
    {
        $model = User::findOne((int)$id);
        
        if (empty($model)) {
            $this->error('Sorry! We can not find Member with this ID.');
            return $this->redirect(['members']);
        }
        
        
        
        return $this->render('view', [
            'model' => $model
        ]);
    }
}
        