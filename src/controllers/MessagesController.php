<?php

namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\models\Message;
use bizley\podium\models\MessageSearch;
use bizley\podium\models\User;
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
    
    public function actionNew($user = null)
    {
        $model = new Message();
        $data = [];
        
        if (!empty($user) && (int)$user > 0 && (int)$user != Yii::$app->user->id) {
            $model->receiver_id = (int)$user;
            $data[] = [
                'id' => (int)$user,
                'mark' => 0,
                'value' => User::findOne((int)$user)->getPodiumTag(true),
            ];
        }
        
        if ($model->load(Yii::$app->request->post())) {
            
            if ($model->validate()) {
                if (!Yii::$app->user->getIdentity()->isIgnoredBy($model->receiver_id)) {

                    if ($model->send()) {
                        $this->success('Message has been sent.');
                        return $this->redirect(['inbox']);
                    }
                }
                else {
                    $this->error('Sorry! You can not send message to this member because he ignores you.');
                }
            }
            else {
                if ($model->receiver_id) {
                    $receiver = User::findOne(['id' => $model->receiver_id]);
                    if ($receiver) {
                        $data[] = [
                            'id' => $receiver->id,
                            'mark' => 0,
                            'value' => $receiver->getPodiumTag(true),
                        ];
                    }
                }
            }
        }
        
        return $this->render('new', [
                'model' => $model,
                'data' => $data
        ]);
    }
    
    public function actionView($id = null)
    {
        $model = Message::findOne(['and', ['id' => $id], ['or', 'receiver_id' => Yii::$app->user->id, 'sender_id' => Yii::$app->user->id]]);
        
        if ($model) {
            
            if ($model->receiver_id == Yii::$app->user->id && $model->receiver_status == Message::STATUS_NEW) {
                $model->receiver_status = Message::STATUS_READ;
                if ($model->save()) {
                    Cache::getInstance()->deleteElement('user.newmessages', Yii::$app->user->id);
                }
            }
            
            return $this->render('view', [
                    'model' => $model
            ]);
        }
        else {
            $this->error('Sorry! We can not find the message with the given ID.');
            return $this->redirect(['inbox']);
        }        
    }
    
    public function actionDelete($id = null, $perm = 0)
    {
        $model = Message::findOne(['and', ['id' => $id], ['or', 'receiver_id' => Yii::$app->user->id, 'sender_id' => Yii::$app->user->id]]);
        
        if ($model) {
            if ($model->remove($perm)) {
                if ($perm) {
                    $this->success('Message has been deleted permanently.');
                }
                else {
                    $this->success('Message has been moved to Deleted Messages.');
                }
            }
            else {
                $this->error('Sorry! We can not delete this message. Contact administrator about this problem.');
            }            
        }
        else {
            $this->error('Sorry! We can not find the message with the given ID.');
        }
        if ($perm) {
            return $this->redirect(['deleted']);
        }
        else {
            return $this->redirect(['inbox']);
        }
    }
    
    public function actionReply($id = null)
    {
        $model = new Message();
        
        $reply = Message::findOne(['id' => $id, 'receiver_id' => Yii::$app->user->id]);
        
        if ($reply) {
            
            $model->topic = Message::re() . ' ' . $reply->topic;
            
            if ($model->load(Yii::$app->request->post())) {
            
                if ($model->validate()) {
                    if (!Yii::$app->user->getIdentity()->isIgnoredBy($model->receiver_id)) {

                        $model->replyto = $reply->id;

                        if ($model->send()) {
                            $this->success('Message has been sent.');
                            return $this->redirect(['inbox']);
                        }
                    }
                    else {
                        $this->error('Sorry! You can not send message to this member because he ignores you.');
                    }
                }
            }
            
            $model->receiver_id = $reply->sender_id;
            
            return $this->render('reply', [
                    'model' => $model,
                    'reply' => $reply,
            ]);
        }
        else {
            $this->error('Sorry! We can not find the message with the given ID.');
            return $this->redirect(['inbox']);
        }
    }
    
}
                