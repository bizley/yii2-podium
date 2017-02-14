<?php

namespace bizley\podium\widgets\poll;

use bizley\podium\models\Poll as PollModel;
use bizley\podium\models\Thread;
use bizley\podium\models\User;
use yii\base\Widget;
use yii\bootstrap\ActiveForm;

/**
 * Podium Poll widget
 * Create new poll and renders votes and results.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.5
 */
class Poll extends Widget
{
    /**
     * @var PollModel
     */
    public $model;

    /**
     * @var bool Display only
     */
    public $display = false;


    /**
     * Rendering the poll.
     * @return string
     */
    public function run()
    {
        if (!$this->model) {
            return null;
        }
        $hidden = $this->model->hidden;
        if ($hidden && !empty($this->model->end_at) && $this->model->end_at < time()) {
            $hidden = 0;
        }
        return $this->render('view', [
            'model' => $this->model,
            'hidden' => $hidden,
            'voted' => $this->display ? true : $this->model->getUserVoted(User::loggedId()),
            'display' => $this->display
        ]);
    }

    /**
     * Renders poll create form.
     * @param ActiveForm $form
     * @param Thread $model
     * @return string
     */
    public static function create($form, $model)
    {
        return (new static)->render('create', ['form' => $form, 'model' => $model]);
    }

    /**
     * Renders poll update form.
     * @param ActiveForm $form
     * @param Poll $model
     * @return string
     */
    public static function update($form, $model)
    {
        return (new static)->render('update', ['form' => $form, 'model' => $model]);
    }

    /**
     * Returns rendered preview of the poll.
     * @param Thread $model
     * @return string
     */
    public static function preview($model)
    {
        if (!$model->pollAdded) {
            return null;
        }
        return (new static)->render('preview', ['model' => $model]);
    }
}
