<?php

namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\models\Forum;
use bizley\podium\models\User;
use bizley\podium\models\UserSearch;
use Exception;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Controller;

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
                        'matchCallback' => function () {
                            return !$this->module->getInstalled();
                        },
                        'denyCallback' => function () {
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

    public function actionView($id = null)
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
    
    public function actionDelete($id = null)
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
    
    public function actionBan($id = null)
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
        $dataProvider = (new Forum())->search(Yii::$app->request->get());

        return $this->render('forums', [
                    'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionNewForum()
    {
        $model = new Forum();
        $model->visible = 1;
        $model->sort = 0;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->success('New forum has been created.');
            return $this->redirect(['forums']);
        }
        else {
            return $this->render('forum', [
                        'model' => $model,
                        'forums' => Forum::find()->all()
            ]);
        }
    }
    
    public function actionEditForum($id = null)
    {
        $model = Forum::findOne((int)$id);

        if (empty($model)) {
            $this->error('Sorry! We can not find Forum with this ID.');
            return $this->redirect(['forums']);
        }
        else {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $this->success('Forum has been updated.');
                return $this->redirect(['forums']);
            }
            else {
                return $this->render('forum', [
                            'model' => $model,
                            'forums' => Forum::find()->all()
                ]);
            }
        }
    }
    
    public function actionDeleteForum($id = null)
    {
        $model = Forum::findOne((int)$id);

        if (empty($model)) {
            $this->error('Sorry! We can not find Forum with this ID.');
        }
        else {
            if ($model->delete()) {
                $this->success('Forum has been deleted.');
            }
            else {
                $this->error('Sorry! There was some error while deleting the forum.');
            }            
        }
        
        return $this->redirect(['forums']);
    }
    
    public function actionSort()
    {
        if (Yii::$app->request->isAjax) {
            $modelId = Yii::$app->request->post('id');
            $new     = Yii::$app->request->post('new');

            if (is_numeric($modelId) && is_numeric($new) && $modelId > 0 && $new >= 0) {
                $moved = Forum::findOne((int)$modelId);
                if ($moved) {
                    $query = (new Query())->from('{{%podium_forum}}')->where('id != :id')->params([':id' => $moved->id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->indexBy('id');
                    $next = 0;
                    $newSort = -1;
                    try {
                        foreach ($query->each() as $id => $forum) {
                            if ($next == (int)$new) {
                                $newSort = $next;
                                $next++;
                            }
                            Yii::$app->db->createCommand()->update('{{%podium_forum}}', ['sort' => $next], 'id = :id', [':id' => $id])->execute();
                            $next++;
                        }
                        if ($newSort == -1) {
                            $newSort = $next;
                        }
                        $moved->sort = $newSort;
                        if (!$moved->save()) {
                            return Html::tag('span', Yii::t('podium/view', 'Sorry! We can not save new forum\'s order.'), ['class' => 'text-danger']);
                        }
                        else {
                            return Html::tag('span', Yii::t('podium/view', 'New forum\'s order has been saved.'), ['class' => 'text-success']);
                        }
                    }
                    catch (Exception $e) {
                        return Html::tag('span', Yii::t('podium/view', 'Sorry! We can not save new forum\'s order.'), ['class' => 'text-danger']);
                    }
                }
                else {
                    return Html::tag('span', Yii::t('podium/view', 'Sorry! We can not find Forum with this ID.'), ['class' => 'text-danger']);
                }
            }
            else {
                return Html::tag('span', Yii::t('podium/view', 'Sorry! Sorting parameters are wrong.'), ['class' => 'text-danger']);
            }
        }
        else {
            return $this->redirect(['forums']);
        }
    }
}
        