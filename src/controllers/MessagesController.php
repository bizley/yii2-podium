<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\components\Log;
use bizley\podium\models\Message;
use bizley\podium\models\MessageSearch;
use bizley\podium\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Podium Messages controller
 * All actions concerning members messages.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class MessagesController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class'        => AccessControl::className(),
                'denyCallback' => function () {
                    return $this->redirect(['account/login']);
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

    /**
     * Deleting the message of given ID.
     * @param integer $id
     * @param integer $perm wheter to delete message permanently
     * @return \yii\web\Response
     */
    public function actionDelete($id = null, $perm = 0)
    {
        if (!is_numeric($id) || $id < 1 || !in_array($perm, [0, 1])) {
            $this->error('Sorry! We can not find the message you are looking for.');
            return $this->redirect(['inbox']);
        }
        else {
        
            $model = Message::findOne(['and', ['id' => (int)$id], ['or', 'receiver_id' => Yii::$app->user->id, 'sender_id' => Yii::$app->user->id]]);

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
                    Log::error('Error while deleting message', !empty($model->id) ? $model->id : '', __METHOD__);
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
    }
    
    /**
     * Listing the deleted messages.
     * @return string
     */
    public function actionDeleted()
    {
        $searchModel  = new MessageSearch();
        $dataProvider = $searchModel->searchDeleted(Yii::$app->request->get());
        
        return $this->render('deleted', [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel
        ]);
    }
    
    /**
     * Listing the messages inbox.
     * @return string
     */
    public function actionInbox()
    {
        $searchModel  = new MessageSearch();
        $dataProvider = $searchModel->searchInbox(Yii::$app->request->get());
        
        return $this->render('inbox', [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel
        ]);
    }
    
    /**
     * Adding a new message.
     * @param integer $user message receiver's ID
     * @return string|\yii\web\Response
     */
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
                    $this->error('Sorry! This member ignores you so you can not send the message.');
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
    
    /**
     * Replying to the message of given ID.
     * @param integer $id
     * @return string|\yii\web\Response
     */
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
                        $this->error('Sorry! This member ignores you so you can not send the message.');
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
    
    /**
     * Listing the sent messages.
     * @return string
     */
    public function actionSent()
    {
        $searchModel  = new MessageSearch();
        $dataProvider = $searchModel->searchSent(Yii::$app->request->get());
        
        return $this->render('sent', [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel
        ]);
    }
    
    /**
     * Viewing the message of given ID.
     * @param integer $id
     * @return string|\yii\web\Response
     */  
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
}                