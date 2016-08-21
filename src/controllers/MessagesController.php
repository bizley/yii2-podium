<?php

namespace bizley\podium\controllers;

use bizley\podium\log\Log;
use bizley\podium\models\Message;
use bizley\podium\models\MessageReceiver;
use bizley\podium\models\MessageSearch;
use bizley\podium\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Response;

/**
 * Podium Messages controller
 * All actions concerning members messages.
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class MessagesController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class'        => AccessControl::className(),
                'denyCallback' => function ($rule, $action) {
                    return $this->redirect(['account/login']);
                },
                'rules'  => [
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
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Deleting the sent message of given ID.
     * @param int $id
     * @return Response
     */
    public function actionDeleteSent($id = null)
    {
        if (!is_numeric($id) || $id < 1) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the message you are looking for.'));
        } else {
            $model = Message::find()
                        ->where([
                            'and', 
                            ['id' => $id, 'sender_id' => User::loggedId()], 
                            ['!=', 'sender_status', Message::STATUS_DELETED]
                        ])
                        ->limit(1)
                        ->one();
            if (empty($model)) {
                $this->error(Yii::t('podium/flash', 'Sorry! We can not find the message with the given ID.'));
            } else {
                if ($model->remove()) {
                    $this->success(Yii::t('podium/flash', 'Message has been deleted.'));
                } else {
                    Log::error('Error while deleting sent message', $model->id, __METHOD__);
                    $this->error(Yii::t('podium/flash', 'Sorry! We can not delete this message. Contact administrator about this problem.'));
                }            
            }
        }
        return $this->redirect(['messages/sent']);
    }
    
    /**
     * Deleting the received message of given ID.
     * @param int $id
     * @return Response
     */
    public function actionDeleteReceived($id = null)
    {
        if (!is_numeric($id) || $id < 1) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the message you are looking for.'));
        } else {
            $model = MessageReceiver::find()
                        ->where([
                            'and', 
                            ['id' => $id, 'receiver_id' => User::loggedId()], 
                            ['!=', 'receiver_status', MessageReceiver::STATUS_DELETED]
                        ])
                        ->limit(1)
                        ->one();
            if (empty($model)) {
                $this->error(Yii::t('podium/flash', 'Sorry! We can not find the message with the given ID.'));
            } else {
                if ($model->remove()) {
                    $this->success(Yii::t('podium/flash', 'Message has been deleted.'));
                } else {
                    Log::error('Error while deleting received message', $model->id, __METHOD__);
                    $this->error(Yii::t('podium/flash', 'Sorry! We can not delete this message. Contact administrator about this problem.'));
                }            
            }
        }
        return $this->redirect(['messages/inbox']);
    }
    
    /**
     * Listing the messages inbox.
     * @return string
     */
    public function actionInbox()
    {
        $searchModel = new MessageReceiver;
        return $this->render('inbox', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel
        ]);
    }
    
    /**
     * Adding a new message.
     * @param int $user message receiver's ID
     * @return string|Response
     */
    public function actionNew($user = null)
    {
        $podiumUser = User::findMe();
        
        if (Message::tooMany($podiumUser->id)) {
            $this->warning(
                Yii::t(
                    'podium/flash', 
                    'You have reached maximum {max_messages, plural, =1{ message} other{ messages}} per {max_minutes, plural, =1{ minute} other{ minutes}} limit. Wait few minutes before sending a new message.', [
                        'max_messages' => Message::SPAM_MESSAGES, 
                        'max_minutes'  => Message::SPAM_WAIT
                    ]
                )
            );
            return $this->redirect(['messages/inbox']);
        }
        
        $model = new Message;
        $to = null;
        if (!empty($user) && (int)$user > 0 && (int)$user != $podiumUser->id) {
            $member = User::find()->where(['id' => (int)$user, 'status' => User::STATUS_ACTIVE])->limit(1)->one();
            if ($member) {
                $model->receiversId = [$member->id];
                $to = $member;
            }
        }
        
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $validated    = [];
                $errors       = false;
                if (!empty($model->friendsId)) {
                    $model->receiversId = array_merge(
                        is_array($model->receiversId) ? $model->receiversId : [], 
                        is_array($model->friendsId) ? $model->friendsId : []
                    );
                }
                if (empty($model->receiversId)) {
                    $this->addError('receiver_id', Yii::t('podium/view', 'You have to select at least one message receiver.'));
                    $errors = true;
                } else {
                    foreach ($model->receiversId as $r) {
                        if ($r == $podiumUser->id) {
                            $this->addError('receiver_id', Yii::t('podium/view', 'You can not send message to yourself.'));
                            $errors = true;
                        } elseif ($podiumUser->isIgnoredBy($r)) {
                            $this->addError('receiver_id', Yii::t('podium/view', 'One of the selected members ignores you and has been removed from message receivers.'));
                            $errors = true;
                        } else {
                            $member = User::find()->where(['id' => (int)$r, 'status' => User::STATUS_ACTIVE])->limit(1)->one();
                            if ($member) {
                                $validated[] = $member->id;
                                if (count($validated) > Message::MAX_RECEIVERS) {
                                    $this->addError('receiver_id', Yii::t('podium/view', 'You can send message up to a maximum of 10 receivers at once.'));
                                    $errors = true;
                                    break;
                                }
                            }
                        }
                    }
                    $model->receiversId = $validated;
                }
                if (!$errors) {
                    if ($model->send()) {
                        $this->success(Yii::t('podium/flash', 'Message has been sent.'));
                        return $this->redirect(['messages/inbox']);
                    } else {
                        $this->error(Yii::t('podium/flash', 'Sorry! There was some error while sending your message.'));
                    }
                }
            }
        }
        return $this->render('new', ['model' => $model, 'to' => $to, 'friends' => User::friendsList()]);
    }
    
    /**
     * Replying to the message of given ID.
     * @param int $id
     * @return string|Response
     */
    public function actionReply($id = null)
    {
        $podiumUser = User::findMe();
        
        if (Message::tooMany($podiumUser->id)) {
            $this->warning(
                Yii::t(
                    'podium/flash', 
                    'You have reached maximum {max_messages, plural, =1{ message} other{ messages}} per {max_minutes, plural, =1{ minute} other{ minutes}} limit. Wait few minutes before sending a new message.', [
                        'max_messages' => Message::SPAM_MESSAGES, 
                        'max_minutes'  => Message::SPAM_WAIT
                    ]
                )
            );
            return $this->redirect(['messages/inbox']);
        }
        
        $reply = Message::find()
                    ->where([Message::tableName() . '.id' => $id])
                    ->joinWith([
                        'messageReceivers' => function ($q) use ($podiumUser) {
                            $q->where(['receiver_id' => $podiumUser->id]);
                        }
                    ])
                    ->limit(1)
                    ->one();
        if (empty($reply)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the message with the given ID.'));
            return $this->redirect(['messages/inbox']);
        }
        
        $model = new Message;
        $model->topic = Message::re() . ' ' . $reply->topic;
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if (!$podiumUser->isIgnoredBy($model->receiversId[0])) {
                    $model->replyto = $reply->id;
                    if ($model->send()) {
                        $this->success(Yii::t('podium/flash', 'Message has been sent.'));
                        return $this->redirect(['messages/inbox']);
                    }
                    $this->error(Yii::t('podium/flash', 'Sorry! There was some error while sending your message.'));
                } else {
                    $this->error(Yii::t('podium/flash', 'Sorry! This member ignores you so you can not send the message.'));
                }
            }
        }
        $model->receiversId = [$reply->sender_id];
        return $this->render('reply', ['model' => $model, 'reply' => $reply]);
    }
    
    /**
     * Listing the sent messages.
     * @return string
     */
    public function actionSent()
    {
        $searchModel  = new MessageSearch;
        return $this->render('sent', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel
        ]);
    }
    
    /**
     * Viewing the sent message of given ID.
     * @param int $id
     * @return string|Response
     */  
    public function actionViewSent($id = null)
    {
        $model = Message::find()
                    ->where([
                        'and', 
                        ['id' => $id, 'sender_id' => User::loggedId()], 
                        ['!=', 'sender_status', Message::STATUS_DELETED]
                    ])
                    ->limit(1)
                    ->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the message with the given ID.'));
            return $this->redirect(['messages/inbox']);
        }
        $model->markRead();
        return $this->render('view', [
            'model' => $model, 
            'type'  => 'sent', 
            'id'    => $model->id
        ]);
    }
    
    /**
     * Viewing the received message of given ID.
     * @param int $id
     * @return string|Response
     */  
    public function actionViewReceived($id = null)
    {
        $model = MessageReceiver::find()
                    ->where([
                        'and', 
                        ['id' => $id, 'receiver_id' => User::loggedId()], 
                        ['!=', 'receiver_status', MessageReceiver::STATUS_DELETED]
                    ])
                    ->limit(1)
                    ->one();        
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the message with the given ID.'));
            return $this->redirect(['messages/inbox']);
        }
        $model->markRead();
        return $this->render('view', [
            'model' => $model->message, 
            'type'  => 'received', 
            'id'    => $model->id
        ]);
    }
    
    /**
     * Loads older messages in thread.
     * @return string
     */
    public function actionLoad()
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['forum/index']);
        }
            
        $result = ['messages' => '', 'more' => 0];

        if (!Yii::$app->user->isGuest) {
            $loggedId = User::loggedId();
            $id = Yii::$app->request->post('message');
            $message = Message::find()->where(['id' => $id])->limit(1)->one();
            if ($message && ($message->sender_id == $loggedId || $message->isMessageReceiver($loggedId))) {
                $stack = 0;
                $reply = clone $message;
                while ($reply->reply && $stack < 5) {
                    $result['more'] = 0;
                    if ($reply->reply->sender_id == $loggedId && $reply->reply->sender_status == Message::STATUS_DELETED) {
                        $reply = $reply->reply;
                        continue;
                    }
                    $result['messages'] .= $this->renderPartial('load', ['reply' => $reply]);
                    $reply = $reply->reply;
                    if ($reply) {
                        $result['more'] = $reply->id;
                    }
                    $stack++;
                }
            }                
        }
        return Json::encode($result);
    }
}
