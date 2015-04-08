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
    
    public function actionDelete($id)
    {
        $model = User::findOne((int)$id);
        
        if (empty($model)) {
            $this->error('Sorry! We can not find Member with this ID.');
        }
        elseif ($model->id == Yii::$app->user->id) {
            $this->error('Sorry! You can not delete your own account.');
        }
        else {
            if ($model->delete()) {
                $this->success('User has been deleted.');
            }
            else {
                $this->error('Sorry! There was some error while deleting the user.');
            }
        }
        
        return $this->redirect(['members']);
    }
    
    public function actionBan($id)
    {
        $model = User::findOne((int)$id);
        
        if (empty($model)) {
            $this->error('Sorry! We can not find Member with this ID.');
        }
        elseif ($model->id == Yii::$app->user->id) {
            $this->error('Sorry! You can not ban or unban your own account.');
        }
        else {
            $model->setScenario('ban');
            
            if ($model->status == User::STATUS_ACTIVE) {
                if ($model->ban()) {
                    $this->success('User has been banned.');
                }
                else {
                    $this->error('Sorry! There was some error while banning the user.');
                }
            }
            elseif ($model->status == User::STATUS_BANNED) {
                if ($model->unban()) {
                    $this->success('User has been unbanned.');
                }
                else {
                    $this->error('Sorry! There was some error while unbanning the user.');
                }
            }
            else {
                $this->error('Sorry! User has got the wrong status.');
            }
        }
        
        return $this->redirect(['members']);
    }
    
    public function actionForums()
    {
        

        return $this->render('forums', [
                    //'dataProvider' => $dataProvider,
                    //'searchModel'  => $searchModel
        ]);
    }
}
        