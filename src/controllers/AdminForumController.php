<?php

namespace bizley\podium\controllers;

use bizley\podium\filters\AccessControl;
use bizley\podium\log\Log;
use bizley\podium\models\Category;
use bizley\podium\models\Content;
use bizley\podium\models\forms\ConfigForm;
use bizley\podium\models\Forum;
use bizley\podium\models\LogSearch;
use bizley\podium\models\Post;
use bizley\podium\models\User;
use bizley\podium\PodiumCache;
use bizley\podium\rbac\Rbac;
use Yii;
use yii\helpers\Html;
use yii\web\Response;

/**
 * Podium Admin controller
 * All actions concerning module forum administration.
 * Not accessible directly.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.5
 */
class AdminForumController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [['allow' => false]],
            ],
        ];
    }

    /**
     * Listing categories.
     * @return string
     */
    public function actionCategories()
    {
        return $this->render('categories', ['dataProvider' => (new Category())->show()]);
    }

    /**
     * Clearing all cache.
     * @return string
     */
    public function actionClear()
    {
        if ($this->module->podiumCache->flush()) {
            $this->success(Yii::t('podium/flash', 'Cache has been cleared.'));
        } else {
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while clearing the cache.'));
        }
        return $this->redirect(['admin/settings']);
    }

    /**
     * Listing the contents.
     * @param string $name content name
     * @return string|Response
     */
    public function actionContents($name = '')
    {
        if (empty($name)) {
            $name = Content::TERMS_AND_CONDS;
        }
        $model = Content::fill($name);
        if ($model->load(Yii::$app->request->post())) {
            if (User::can(Rbac::PERM_CHANGE_SETTINGS)) {
                if ($model->save()) {
                    $this->success(Yii::t('podium/flash', 'Content has been saved.'));
                } else {
                    $this->error(Yii::t('podium/flash', 'Sorry! There was some error while saving the content.'));
                }
            } else {
                $this->error(Yii::t('podium/flash', 'You are not allowed to perform this action.'));
            }
            return $this->refresh();
        }
        return $this->render('contents', ['model' => $model]);
    }

    /**
     * Deleting the category of given ID.
     * @param int $id
     * @return Response
     */
    public function actionDeleteCategory($id = null)
    {
        $model = Category::find()->where(['id' => $id])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Category with this ID.'));
            return $this->redirect(['admin/categories']);
        }
        if ($model->delete()) {
            PodiumCache::clearAfter('categoryDelete');
            Log::info('Category deleted', $model->id, __METHOD__);
            $this->success(Yii::t('podium/flash', 'Category has been deleted.'));
        } else {
            Log::error('Error while deleting category', $model->id, __METHOD__);
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while deleting the category.'));
        }
        return $this->redirect(['admin/categories']);
    }

    /**
     * Deleting the forum of given ID.
     * @param int $cid parent category ID
     * @param int $id forum ID
     * @return Response
     */
    public function actionDeleteForum($cid = null, $id = null)
    {
        $model = Forum::find()->where(['id' => $id, 'category_id' => $cid])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Forum with this ID.'));
            return $this->redirect(['admin/forums', 'cid' => $cid]);
        }
        if ($model->delete()) {
            PodiumCache::clearAfter('forumDelete');
            Log::info('Forum deleted', $model->id, __METHOD__);
            $this->success(Yii::t('podium/flash', 'Forum has been deleted.'));
        } else {
            Log::error('Error while deleting forum', $model->id, __METHOD__);
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while deleting the forum.'));
        }
        return $this->redirect(['admin/forums', 'cid' => $cid]);
    }

    /**
     * Editing the category of given ID.
     * @param int $id
     * @return string|Response
     */
    public function actionEditCategory($id = null)
    {
        $model = Category::find()->where(['id' => $id])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Category with this ID.'));
            return $this->redirect(['admin/categories']);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Log::info('Category updated', $model->id, __METHOD__);
                $this->success(Yii::t('podium/flash', 'Category has been updated.'));
                return $this->refresh();
            }
            $this->error(Yii::t('podium/flash', 'Sorry! There was an error while updating the category.'));
        }
        return $this->render('category', [
            'model' => $model,
            'categories' => Category::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all()
        ]);
    }

    /**
     * Editing the forum of given ID.
     * @param int $cid parent category ID
     * @param int $id forum ID
     * @return string|Response
     */
    public function actionEditForum($cid = null, $id = null)
    {
        $model = Forum::find()->where(['id' => $id, 'category_id' => $cid])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Forum with this ID.'));
            return $this->redirect(['admin/forums', 'cid' => $cid]);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Log::info('Forum updated', $model->id, __METHOD__);
                $this->success(Yii::t('podium/flash', 'Forum has been updated.'));
                return $this->refresh();
            }
            $this->error(Yii::t('podium/flash', 'Sorry! There was an error while updating the forum.'));
        }
        return $this->render('forum', [
            'model' => $model,
            'forums' => Forum::find()->where(['category_id' => $cid])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all(),
            'categories' => Category::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all()
        ]);
    }

    /**
     * Listing the forums of given category ID.
     * @param int $cid parent category ID
     * @return string|Response
     */
    public function actionForums($cid = null)
    {
        $model = Category::find()->where(['id' => $cid])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Category with this ID.'));
            return $this->redirect(['admin/categories']);
        }
        return $this->render('forums', [
            'model' => $model,
            'categories' => Category::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all(),
            'forums' => Forum::find()->where(['category_id' => $model->id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all()
        ]);
    }

    /**
     * Dashboard.
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'members' => User::find()->orderBy(['id' => SORT_DESC])->limit(10)->all(),
            'posts' => Post::find()->orderBy(['id' => SORT_DESC])->limit(10)->all()
        ]);
    }

    /**
     * Listing the logs.
     * @return string
     */
    public function actionLogs()
    {
        $searchModel = new LogSearch();
        return $this->render('logs', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Adding new category.
     * @return string|Response
     */
    public function actionNewCategory()
    {
        $model = new Category();
        $model->visible = 1;
        $model->sort = 0;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::info('Category added', $model->id, __METHOD__);
            $this->success(Yii::t('podium/flash', 'New category has been created.'));
            return $this->redirect(['admin/categories']);
        }
        return $this->render('category', [
            'model' => $model,
            'categories' => Category::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all()
        ]);
    }

    /**
     * Adding new forum.
     * @param int $cid parent category ID
     * @return string|Response
     */
    public function actionNewForum($cid = null)
    {
        $category = Category::find()->where(['id' => $cid])->limit(1)->one();
        if (empty($category)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Category with this ID.'));
            return $this->redirect(['admin/categories']);
        }
        $model = new Forum();
        $model->category_id = $category->id;
        $model->visible = 1;
        $model->sort = 0;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::info('Forum added', $model->id, __METHOD__);
            $this->success(Yii::t('podium/flash', 'New forum has been created.'));
            return $this->redirect(['admin/forums', 'cid' => $category->id]);
        }
        return $this->render('forum', [
            'model' => $model,
            'forums' => Forum::find()->where(['category_id' => $category->id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all(),
            'categories' => Category::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all()
        ]);
    }

    /**
     * Updating the module configuration.
     * @return string|Response
     */
    public function actionSettings()
    {
        $model = new ConfigForm();
        $data = Yii::$app->request->post('ConfigForm');
        if ($data) {
            if (User::can(Rbac::PERM_CHANGE_SETTINGS)) {
                if ($model->update($data)) {
                    Log::info('Settings updated', null, __METHOD__);
                    $this->success(Yii::t('podium/flash', 'Settings have been updated.'));
                    return $this->refresh();
                }
                $this->error(Yii::t('podium/flash', "One of the setting's values is too long (255 characters max)."));
            } else {
                $this->error(Yii::t('podium/flash', 'You are not allowed to perform this action.'));
            }
        }
        return $this->render('settings', ['model' => $model]);
    }

    /**
     * Updating the categories order.
     * @return string|Response
     */
    public function actionSortCategory()
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['admin/categories']);
        }
        if (!User::can(Rbac::PERM_UPDATE_CATEGORY)) {
            return Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                . ' ' . Yii::t('podium/view', 'You are not allowed to perform this action.'),
                ['class' => 'text-danger']
            );
        }

        $modelId = Yii::$app->request->post('id');
        $new = Yii::$app->request->post('new');
        if (!is_numeric($modelId) || !is_numeric($new)) {
            return Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                . ' ' . Yii::t('podium/view', 'Sorry! Sorting parameters are wrong.'),
                ['class' => 'text-danger']
            );
        }

        $moved = Category::find()->where(['id' => $modelId])->limit(1)->one();
        if (empty($moved)) {
            return Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                . ' ' . Yii::t('podium/view', 'Sorry! We can not find Category with this ID.'),
                ['class' => 'text-danger']
            );
        }
        if ($moved->newOrder((int)$new)) {
            return Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok-circle'])
                . ' ' . Yii::t('podium/view', "New categories' order has been saved."),
                ['class' => 'text-success']
            );
        }
        return Html::tag('span',
            Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
            . ' ' . Yii::t('podium/view', "Sorry! We can not save new categories' order."),
            ['class' => 'text-danger']
        );
    }

    /**
     * Updating the forums order.
     * @return string|Response
     */
    public function actionSortForum()
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['admin/forums']);
        }
        if (!User::can(Rbac::PERM_UPDATE_FORUM)) {
            return Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                . ' ' . Yii::t('podium/view', 'You are not allowed to perform this action.'),
                ['class' => 'text-danger']
            );
        }

        $modelId = Yii::$app->request->post('id');
        $modelCategory = Yii::$app->request->post('category');
        $new = Yii::$app->request->post('new');
        if (!is_numeric($modelId) || !is_numeric($modelCategory) || !is_numeric($new)) {
            return Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                . ' ' . Yii::t('podium/view', 'Sorry! Sorting parameters are wrong.'),
                ['class' => 'text-danger']
            );
        }

        $moved = Forum::find()->where(['id' => $modelId])->limit(1)->one();
        $movedCategory = Category::find()->where(['id' => $modelCategory])->limit(1)->one();
        if (empty($moved) || empty($modelCategory) || $moved->category_id != $movedCategory->id) {
            return Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                . ' ' . Yii::t('podium/view', 'Sorry! We can not find Forum with this ID.'),
                ['class' => 'text-danger']
            );
        }
        if ($moved->newOrder((int)$new)) {
            return Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok-circle'])
                . ' ' . Yii::t('podium/view', "New forums' order has been saved."),
                ['class' => 'text-success']
            );
        }
        return Html::tag('span',
            Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
            . ' ' . Yii::t('podium/view', "Sorry! We can not save new forums' order."),
            ['class' => 'text-danger']
        );
    }
}
