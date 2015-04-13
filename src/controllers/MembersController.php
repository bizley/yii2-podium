<?php

namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\models\User;
use bizley\podium\models\UserSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;

class MembersController extends Controller
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

    public function actionFieldlist()
    {
        $request = Yii::$app->request;
        $results = ['data' => [], 'page' => 1, 'total' => 0];
        $query   = $request->post('query');
        $page    = $request->post('page', 1);

        $currentPage = 0;
        if (!empty($page) && is_numeric($page) && $page > 0) {
            $currentPage = $page - 1;
        }

        $query = preg_replace('/[^\p{L}\w]/', '', $query);
        
        $cache = Cache::getInstance()->get('members.fieldlist');
        if ($cache === false || empty($cache[$query . '-' . $currentPage])) {
            if ($cache === false) {
                $cache = [];
            }
        
            if (empty($query)) {
                $queryObject = User::find()->where(['status' => User::STATUS_ACTIVE])->orderBy('username, id');
            }
            else {
                $queryObject = User::find()->where(['and', ['status' => User::STATUS_ACTIVE], ['or', ['like', 'username', $query], ['username' => null]]])->orderBy('username, id');
            }        
            $provider = new ActiveDataProvider([
                'query' => $queryObject,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);

            $provider->getPagination()->setPage($currentPage);

            foreach ($provider->getModels() as $data) {
                $results['data'][] = [
                    'id'    => $data->id,
                    'mark'  => 0,
                    'value' => $data->getPodiumTag(true),
                ];
            }

            $results['page']  = $provider->getPagination()->getPage() + 1;
            $results['total'] = $provider->getPagination()->getPageCount();

            $cache[$query . '-' . $currentPage] = Json::encode($results);
            Cache::getInstance()->set('members.fieldlist', $cache);
        }
        
        return $cache[$query . '-' . $currentPage];
    }

    public function actionIndex()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get(), true);

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'searchModel'  => $searchModel
        ]);
    }
    
    public function actionView($id = null)
    {
        $model = User::findOne(['and', ['id' => (int)$id], ['!=', 'status', User::STATUS_REGISTERED]]);
        
        if (empty($model)) {
            $this->error('Sorry! We can not find Member with this ID.');
            return $this->redirect(['index']);
        }
        
        return $this->render('view', [
            'model' => $model
        ]);
    }
    
    public function actionIgnore($id = null)
    {
        try {
            $model = User::findOne(['and', ['id' => (int)$id], ['!=', 'status', User::STATUS_REGISTERED]]);

            if (empty($model)) {
                $this->error('Sorry! We can not find Member with this ID.');
            }
            elseif ($model->id == Yii::$app->user->id) {
                $this->error('Sorry! You can not ignore your own account.');
            }
            elseif ($model->id == User::ROLE_ADMIN) {
                $this->error('Sorry! You can not ignore Administrator.');
            }
            else {

                if ($model->isIgnoredBy(Yii::$app->user->id)) {

                    Yii::$app->db->createCommand()->delete('{{%podium_user_ignore}}', 'user_id = :uid AND ignored_id = :iid', [':uid' => Yii::$app->user->id, ':iid' => $model->id])->execute();
                    $this->success('User has been unignored.');                    
                }
                else {
                    Yii::$app->db->createCommand()->insert('{{%podium_user_ignore}}', ['user_id' => Yii::$app->user->id, 'ignored_id' => $model->id])->execute();
                    $this->success('User has been ignored.');
                }
            }
        }
        catch (Exception $e) {
            $this->error('Sorry! There was some error while performing this action.');
            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
        }
        
        return $this->redirect(['index']);
    }
    
    public function actionMods()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get(), true, true);

        return $this->render('mods', [
                    'dataProvider' => $dataProvider,
                    'searchModel'  => $searchModel
        ]);
    }
}                