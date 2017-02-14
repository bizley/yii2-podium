<?php

namespace bizley\podium\actions;

use bizley\podium\models\Thread;
use bizley\podium\models\User;
use bizley\podium\services\ThreadVerifier;
use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * Thread Action
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class ThreadAction extends Action
{
    /**
     * @var string permission name
     */
    public $permission;

    /**
     * @var string boolean attribute name to check
     */
    public $boolAttribute;

    /**
     * @var string switcher method name
     */
    public $switcher;

    /**
     * @var string message after on switch
     */
    public $onMessage;

    /**
     * @var string message after off switch
     */
    public $offMessage;

    private $_thread;

    /**
     * Returns thread.
     * @param int $cid
     * @param int $fid
     * @param int $id
     * @param string $slug
     * @return Thread
     */
    public function getThread($cid, $fid, $id, $slug)
    {
        if ($this->_thread === null) {
            $this->_thread = (new ThreadVerifier([
                    'categoryId' => $cid,
                    'forumId' => $fid,
                    'threadId' => $id,
                    'threadSlug' => $slug
                ]))->verify();
        }
        return $this->_thread;
    }

    /**
     * Runs action.
     * @param int $cid
     * @param int $fid
     * @param int $id
     * @param string $slug
     * @return Response
     */
    public function run($cid = null, $fid = null, $id = null, $slug = null)
    {
        $thread = $this->getThread($cid, $fid, $id, $slug);
        if (empty($thread)) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->controller->redirect(['forum/index']);
        }

        if (!User::can($this->permission, ['item' => $thread])) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->controller->redirect(['forum/index']);
        }

        if (call_user_func([$thread, $this->switcher])) {
            $this->controller->success($thread->{$this->boolAttribute} ? $this->onMessage : $this->offMessage);
        } else {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! There was an error while updating the thread.'));
        }
        return $this->controller->redirect([
            'forum/thread',
            'cid' => $thread->forum->category->id,
            'fid' => $thread->forum->id,
            'id' => $thread->id,
            'slug' => $thread->slug
        ]);
    }
}
