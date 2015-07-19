<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\components\Log;
use bizley\podium\models\Category;
use bizley\podium\models\ConfigForm;
use bizley\podium\models\Content;
use bizley\podium\models\Forum;
use bizley\podium\models\ForumSearch;
use bizley\podium\models\LogSearch;
use bizley\podium\models\Mod;
use bizley\podium\models\User;
use bizley\podium\models\UserSearch;
use Exception;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Controller;

/**
 * Podium Admin controller
 * All actions concerning module administration.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class AdminController extends Controller
{

    /**
     * @inheritdoc
     */
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

    /**
     * Banning the user of given ID.
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionBan($id = null)
    {
        $model = User::findOne((int) $id);

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
                    Cache::getInstance()->delete('members.fieldlist');
                    Log::info('User banned', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->success('User has been banned.');
                }
                else {
                    Log::error('Error while banning user', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->error('Sorry! There was some error while banning the user.');
                }
            }
            elseif ($model->status == User::STATUS_BANNED) {
                if ($model->unban()) {
                    Cache::getInstance()->delete('members.fieldlist');
                    Log::info('User unbanned', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->success('User has been unbanned.');
                }
                else {
                    Log::error('Error while unbanning user', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->error('Sorry! There was some error while unbanning the user.');
                }
            }
            else {
                $this->error('Sorry! User has got the wrong status.');
            }
        }

        return $this->redirect(['admin/members']);
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
        if (Cache::getInstance()->flush()) {
            $this->success('Cache has been cleared.');
        }
        else {
            $this->error('Sorry! There was some error while clearing the cache.');
        }
        
        return $this->redirect(['admin/settings']);
    }
    
    /**
     * Listing the contents.
     * @param string $name content name
     * @return string|\yii\web\Response
     */
    public function actionContents($name = '')
    {
        $allowed = ['terms', 'email-reg', 'email-new', 'email-react', 'email-pass'];
        
        if ($name == '' || !in_array($name, $allowed)) {
            $name = 'terms';
        }        
        
        $model = Content::find()->where(['name' => $name])->one();
        if (!$model) {
            $model = new Content();
            $model->name = $name;
        }        

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            $this->success('Content has been saved.');
            return $this->refresh();
        }

        return $this->render('contents', [
                    'model'      => $model,
        ]);
    }
    
    /**
     * Deleting the user of given ID.
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionDelete($id = null)
    {
        $model = User::findOne((int) $id);

        if (empty($model)) {
            $this->error('Sorry! We can not find Member with this ID.');
        }
        elseif ($model->id == Yii::$app->user->id) {
            $this->error('Sorry! You can not delete your own account.');
        }
        else {
            if ($model->delete()) {
                Cache::getInstance()->delete('members.fieldlist');
                Cache::getInstance()->delete('forum.memberscount');
                Log::info('User deleted', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success('User has been deleted.');
            }
            else {
                Log::error('Error while deleting user', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->error('Sorry! There was some error while deleting the user.');
            }
        }

        return $this->redirect(['admin/members']);
    }
    
    /**
     * Deleting the category of given ID.
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionDeleteCategory($id = null)
    {
        $model = Category::findOne((int) $id);

        if (empty($model)) {
            $this->error('Sorry! We can not find Category with this ID.');
        }
        else {
            if ($model->delete()) {
                Cache::getInstance()->delete('forum.threadscount');
                Cache::getInstance()->delete('forum.postscount');
                Log::info('Category deleted', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success('Category has been deleted.');
            }
            else {
                Log::error('Error while deleting category', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->error('Sorry! There was some error while deleting the category.');
            }
        }

        return $this->redirect(['admin/categories']);
    }
    
    /**
     * Deleting the forum of given ID.
     * @param integer $cid parent category ID
     * @param integer $id forum ID
     * @return \yii\web\Response
     */
    public function actionDeleteForum($cid = null, $id = null)
    {
        $category = Category::findOne((int) $cid);

        if (empty($category)) {
            $this->error('Sorry! We can not find Category with this ID.');
            return $this->redirect(['admin/categories']);
        }

        $model = Forum::findOne(['id' => (int) $id, 'category_id' => $category->id]);

        if (empty($model)) {
            $this->error('Sorry! We can not find Forum with this ID.');
        }
        else {
            if ($model->delete()) {
                Cache::getInstance()->delete('forum.threadscount');
                Cache::getInstance()->delete('forum.postscount');
                Log::info('Forum deleted', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success('Forum has been deleted.');
            }
            else {
                Log::error('Error while deleting forum', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->error('Sorry! There was some error while deleting the forum.');
            }
        }

        return $this->redirect(['admin/forums', 'cid' => $category->id]);
    }
    
    /**
     * Demoting the user of given ID.
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionDemote($id = null)
    {
        $model = User::findOne((int) $id);

        if (empty($model)) {
            $this->error('Sorry! We can not find User with this ID.');
        }
        else {
            $model->setScenario('role');
            if ($model->role != User::ROLE_MODERATOR) {
                $this->error('You can only demote Moderators to Members.');
            }
            else {
                $transaction = User::getDb()->beginTransaction();
                try {
                    $model->role = User::ROLE_MEMBER;
                    if ($model->save()) {
                        if (!empty(Yii::$app->authManager->getRolesByUser($model->id))) {
                            Yii::$app->authManager->revokeAll($model->id);
                        }
                        if (Yii::$app->authManager->assign(Yii::$app->authManager->getRole('user'), $model->id)) {
                            Yii::$app->db->createCommand()->delete(Mod::tableName(), 'user_id = :id', [':id' => $model->id])->execute();
                            $transaction->commit();
                            Log::info('User demoted', !empty($model->id) ? $model->id : '', __METHOD__);
                            $this->success('User has been demoted.');
                            return $this->redirect(['admin/members']);
                        }
                    }
                    Log::error('Error while demoting user', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->error('Sorry! There was an error while demoting the user.');
                }
                catch (Exception $e) {
                    $transaction->rollBack();
                    Log::error($e->getMessage(), null, __METHOD__);
                    $this->error('Sorry! There was an error while demoting the user.');
                }
            }
        }

        return $this->redirect(['admin/members']);
    }
    
    /**
     * Editing the category of given ID.
     * @param integer $id
     * @return string|\yii\web\Response
     */
    public function actionEditCategory($id = null)
    {
        $model = Category::findOne((int) $id);

        if (empty($model)) {
            $this->error('Sorry! We can not find Category with this ID.');
            return $this->redirect(['admin/categories']);
        }
        else {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Log::info('Category updated', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success('Category has been updated.');
                return $this->redirect(['admin/categories']);
            }
            else {
                return $this->render('category', [
                            'model'      => $model,
                            'categories' => Category::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all()
                ]);
            }
        }
    }
    
    /**
     * Editing the forum of given ID.
     * @param integer $cid parent category ID
     * @param integer $id forum ID
     * @return string|\yii\web\Response
     */
    public function actionEditForum($cid = null, $id = null)
    {
        $category = Category::findOne((int) $cid);

        if (empty($category)) {
            $this->error('Sorry! We can not find Category with this ID.');
            return $this->redirect(['admin/categories']);
        }

        $model = Forum::findOne(['id' => (int) $id, 'category_id' => $category->id]);

        if (empty($model)) {
            $this->error('Sorry! We can not find Forum with this ID.');
            return $this->redirect(['admin/forums', 'cid' => $category->id]);
        }
        else {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Log::info('Forum updated', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success('Forum has been updated.');
                return $this->redirect(['admin/forums']);
            }
            else {
                return $this->render('forum', [
                            'model'      => $model,
                            'forums'     => Forum::find()->where(['category_id' => $category->id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all(),
                            'categories' => Category::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all()
                ]);
            }
        }
    }
    
    /**
     * Listing the forums of given category ID.
     * @param integer $cid parent category ID
     * @return string|\yii\web\Response
     */
    public function actionForums($cid = null)
    {
        $model = Category::findOne((int) $cid);

        if (empty($model)) {
            $this->error('Sorry! We can not find Category with this ID.');
            return $this->redirect(['admin/categories']);
        }

        return $this->render('forums', [
                    'model'      => $model,
                    'categories' => Category::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all(),
                    'forums'     => Forum::find()->where(['category_id' => $model->id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all()
        ]);
    }
    
    /**
     * Dashboard
     * @return string
     */
    public function actionIndex()
    {
        if ($this->module->getParam('mode') == 'INSTALL') {
            $this->warning('Parameter {mode} with {install} value found in configuration! Make sure you remove this parameter in production environment.', [
                'mode'    => '<code>mode</code>',
                'install' => '<code>INSTALL</code>'
            ]);
        }

        return $this->render('index');
    }

    /**
     * Listing the logs.
     * @return string
     */
    public function actionLogs()
    {
        $searchModel  = new LogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('logs', [
                    'dataProvider' => $dataProvider,
                    'searchModel'  => $searchModel,
        ]);
    }
    
    /**
     * Listing the users.
     * @return string
     */
    public function actionMembers()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('members', [
                    'dataProvider' => $dataProvider,
                    'searchModel'  => $searchModel,
        ]);
    }
    
    /**
     * Adding/removing forum from the moderation list for user of given ID.
     * @param integer $uid user ID
     * @param integer $fid forum ID
     * @return \yii\web\Response
     */
    public function actionMod($uid = null, $fid = null)
    {
        if (!is_numeric($uid) || $uid < 1 || !is_numeric($fid) || $fid < 1) {
            $this->error('Sorry! We can not find the moderator or forum with this ID.');
            return $this->redirect(['admin/mods']);
        }
        else {
            $mod = User::findOne(['id' => $uid, 'role' => User::ROLE_MODERATOR]);
            if (!$mod) {
                $this->error('Sorry! We can not find the moderator with this ID.');
                return $this->redirect(['admin/mods']);
            }
            else {
                $forum = Forum::findOne(['id' => $fid]);
                if (!$forum) {
                    $this->error('Sorry! We can not find the forum with this ID.');
                }
                else {
                    try {
                        if ((new Query)->from(Mod::tableName())->where(['forum_id' => $forum->id, 'user_id' => $mod->id])->exists()) {
                            Yii::$app->db->createCommand()->delete(Mod::tableName(), ['forum_id' => $forum->id, 'user_id' => $mod->id])->execute();
                        }
                        else {
                            Yii::$app->db->createCommand()->insert(Mod::tableName(), ['forum_id' => $forum->id, 'user_id' => $mod->id])->execute();
                        }
                        Cache::getInstance()->deleteElement('forum.moderators', $forum->id);
                        Log::info('Moderator updated', $mod->id, __METHOD__);
                        $this->success('Moderation list has been updated.');
                    }
                    catch (Exception $e) {
                        Log::error($e->getMessage(), null, __METHOD__);
                        $this->error('Sorry! There was an error while updating the moderatoration list.');
                    }
                }
                
                return $this->redirect(['admin/mods', 'id' => $uid]);
            }
        }
    }
    
    /**
     * Listing and updating moderation list for the forum of given ID.
     * @param integer $id forum ID
     * @return string|\yii\web\Response
     */
    public function actionMods($id = null)
    {
        $mod        = null;
        $moderators = User::find()->where(['role' => User::ROLE_MODERATOR])->orderBy(['username' => SORT_ASC])->indexBy('id')->all();

        if (is_numeric($id) && $id > 0) {
            if (isset($moderators[$id])) {
                $mod = $moderators[$id];
            }
        }
        if ($id == null) {
            foreach ($moderators as $moderator) {
                $mod = $moderator;
                break;
            }
        }

        $searchModel  = new ForumSearch();
        $dataProvider = $searchModel->searchForMods(Yii::$app->request->get());

        $postData = Yii::$app->request->post();
        if ($postData) {
            $mod_id    = !empty($postData['mod_id']) && is_numeric($postData['mod_id']) && $postData['mod_id'] > 0 ? $postData['mod_id'] : 0;
            $selection = !empty($postData['selection']) ? $postData['selection'] : [];
            $pre       = !empty($postData['pre']) ? $postData['pre'] : [];
            
            if ($mod_id != $mod->id) {
                $this->error('Sorry! There was an error while selecting the moderator ID.');
            }
            else {
                try {
                    $add = [];
                    foreach ($selection as $select) {
                        if (!in_array($select, $pre)) {
                            if ((new Query)->from(Forum::tableName())->where(['id' => $select])->exists() && (new Query)->from(Mod::tableName())->where(['forum_id' => $select, 'user_id' => $mod->id])->exists() === false) {
                                $add[] = [$select, $mod->id];
                            }
                        }
                    }
                    $remove = [];
                    foreach ($pre as $p) {
                        if (!in_array($p, $selection)) {
                            if ((new Query)->from(Mod::tableName())->where(['forum_id' => $p, 'user_id' => $mod->id])->exists()) {
                                $remove[] = $p;
                            }
                        }
                    }
                    if (!empty($add)) {
                        Yii::$app->db->createCommand()->batchInsert(Mod::tableName(), ['forum_id', 'user_id'], $add)->execute();
                    }
                    if (!empty($remove)) {
                        Yii::$app->db->createCommand()->delete(Mod::tableName(), ['forum_id' => $remove, 'user_id' => $mod->id])->execute();
                    }
                    Cache::getInstance()->delete('forum.moderators');
                    Log::info('Moderators updated', null, __METHOD__);
                    $this->success('Moderation list has been saved.');
                }
                catch (Exception $e) {
                    Log::error($e->getMessage(), null, __METHOD__);
                    $this->error('Sorry! There was an error while saving the moderatoration list.');
                }
                
                return $this->refresh();
            }
        }
        
        return $this->render('mods', [
                    'moderators'   => $moderators,
                    'mod'          => $mod,
                    'searchModel'  => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Adding new category.
     * @return string|\yii\web\Response
     */
    public function actionNewCategory()
    {
        $model          = new Category();
        $model->visible = 1;
        $model->sort    = 0;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::info('Category added', !empty($model->id) ? $model->id : '', __METHOD__);
            $this->success('New category has been created.');
            return $this->redirect(['admin/categories']);
        }
        else {
            return $this->render('category', [
                        'model'      => $model,
                        'categories' => Category::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all()
            ]);
        }
    }
    
    /**
     * Adding new forum.
     * @param integer $cid parent category ID
     * @return string|\yii\web\Response
     */
    public function actionNewForum($cid = null)
    {
        $category = Category::findOne((int) $cid);

        if (empty($category)) {
            $this->error('Sorry! We can not find Category with this ID.');
            return $this->redirect(['admin/categories']);
        }

        $model              = new Forum();
        $model->category_id = $category->id;
        $model->visible     = 1;
        $model->sort        = 0;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::info('Forum added', !empty($model->id) ? $model->id : '', __METHOD__);
            $this->success('New forum has been created.');
            return $this->redirect(['admin/forums', 'cid' => $category->id]);
        }
        else {
            return $this->render('forum', [
                        'model'      => $model,
                        'forums'     => Forum::find()->where(['category_id' => $category->id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all(),
                        'categories' => Category::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all()
            ]);
        }
    }
    
    /**
     * Promoting the user of given ID.
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionPromote($id = null)
    {
        $model = User::findOne((int) $id);

        if (empty($model)) {
            $this->error('Sorry! We can not find User with this ID.');
        }
        else {
            $model->setScenario('role');
            if ($model->role != User::ROLE_MEMBER) {
                $this->error('You can only promote Members to Moderators.');
            }
            else {
                $transaction = User::getDb()->beginTransaction();
                try {
                    $model->role = User::ROLE_MODERATOR;
                    if ($model->save()) {
                        if (!empty(Yii::$app->authManager->getRolesByUser($model->id))) {
                            Yii::$app->authManager->revokeAll($model->id);
                        }
                        if (Yii::$app->authManager->assign(Yii::$app->authManager->getRole('moderator'), $model->id)) {
                            $transaction->commit();
                            Log::info('User promoted', !empty($model->id) ? $model->id : '', __METHOD__);
                            $this->success('User has been promoted.');
                            return $this->redirect(['admin/mods', 'id' => $model->id]);
                        }
                    }
                    $this->error('Sorry! There was an error while promoting the user.');
                }
                catch (Exception $e) {
                    $transaction->rollBack();
                    Log::error($e->getMessage(), null, __METHOD__);
                    $this->error('Sorry! There was an error while promoting the user.');
                }
            }
        }

        return $this->redirect(['members']);
    }
    
    /**
     * Updating the module configuration.
     * @return string|\yii\web\Response
     */
    public function actionSettings()
    {
        $model = new ConfigForm();

        if ($data = Yii::$app->request->post('ConfigForm')) {
            if ($model->update($data)) {
                Log::info('Settings updated', null, __METHOD__);
                $this->success('Settings have been updated.');
                return $this->refresh();
            }
            else {
                $this->error('One of the setting\'s value is too long (255 characters max).');
            }
        }

        return $this->render('settings', ['model' => $model]);
    }
    
    /**
     * Updating the categories order.
     * @return string|\yii\web\Response
     */
    public function actionSortCategory()
    {
        if (Yii::$app->request->isAjax) {
            $modelId = Yii::$app->request->post('id');
            $new     = Yii::$app->request->post('new');

            if (is_numeric($modelId) && is_numeric($new) && $modelId > 0 && $new >= 0) {
                $moved = Category::findOne((int) $modelId);
                if ($moved) {
                    $query = (new Query)->from(Category::tableName())->where('id != :id')->
                            params([':id' => $moved->id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->indexBy('id');
                    $next    = 0;
                    $newSort = -1;
                    try {
                        foreach ($query->each() as $id => $forum) {
                            if ($next == (int) $new) {
                                $newSort = $next;
                                $next++;
                            }
                            Yii::$app->db->createCommand()->update(Category::tableName(), ['sort' => $next], 'id = :id', [':id' => $id])->execute();
                            $next++;
                        }
                        if ($newSort == -1) {
                            $newSort = $next;
                        }
                        $moved->sort = $newSort;
                        if (!$moved->save()) {
                            return Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Sorry! We can not save new categories\' order.'), ['class' => 'text-danger']);
                        }
                        else {
                            Log::info('Categories orded updated', !empty($moved->id) ? $moved->id : '', __METHOD__);
                            return Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok-circle']) . ' ' . Yii::t('podium/view', 'New categories\' order has been saved.'), ['class' => 'text-success']);
                        }
                    }
                    catch (Exception $e) {
                        Log::error($e->getMessage(), null, __METHOD__);
                        return Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Sorry! We can not save new categories\' order.'), ['class' => 'text-danger']);
                    }
                }
                else {
                    return Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Sorry! We can not find Category with this ID.'), ['class' => 'text-danger']);
                }
            }
            else {
                return Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Sorry! Sorting parameters are wrong.'), ['class' => 'text-danger']);
            }
        }
        else {
            return $this->redirect(['admin/categories']);
        }
    }
    
    /**
     * Updating the forums order.
     * @return string|\yii\web\Response
     */
    public function actionSortForum()
    {
        if (Yii::$app->request->isAjax) {
            $modelId       = Yii::$app->request->post('id');
            $modelCategory = Yii::$app->request->post('category');
            $new           = Yii::$app->request->post('new');

            if (is_numeric($modelId) && is_numeric($modelCategory) && is_numeric($new) && $modelId > 0 && $modelCategory > 0 && $new >= 0) {
                $moved         = Forum::findOne((int) $modelId);
                $movedCategory = Category::findOne((int) $modelCategory);
                if ($moved && $modelCategory && $moved->category_id == $movedCategory->id) {
                    $query = (new Query)->from(Forum::tableName())->where('id != :id AND category_id = :cid')->
                            params([':id' => $moved->id, ':cid' => $movedCategory->id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->indexBy('id');
                    $next    = 0;
                    $newSort = -1;
                    try {
                        foreach ($query->each() as $id => $forum) {
                            if ($next == (int) $new) {
                                $newSort = $next;
                                $next++;
                            }
                            Yii::$app->db->createCommand()->update(Forum::tableName(), ['sort' => $next], 'id = :id', [':id' => $id])->execute();
                            $next++;
                        }
                        if ($newSort == -1) {
                            $newSort = $next;
                        }
                        $moved->sort = $newSort;
                        if (!$moved->save()) {
                            return Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Sorry! We can not save new forums\' order.'), ['class' => 'text-danger']);
                        }
                        else {
                            Log::info('Forums orded updated', !empty($moved->id) ? $moved->id : '', __METHOD__);
                            return Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok-circle']) . ' ' . Yii::t('podium/view', 'New forums\' order has been saved.'), ['class' => 'text-success']);
                        }
                    }
                    catch (Exception $e) {
                        Log::error($e->getMessage(), null, __METHOD__);
                        return Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Sorry! We can not save new forums\' order.'), ['class' => 'text-danger']);
                    }
                }
                else {
                    return Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Sorry! We can not find Forum with this ID.'), ['class' => 'text-danger']);
                }
            }
            else {
                return Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Sorry! Sorting parameters are wrong.'), ['class' => 'text-danger']);
            }
        }
        else {
            return $this->redirect(['admin/forums']);
        }
    }

    /**
     * Listing the details of user of given ID.
     * @param integer $id
     * @return string|\yii\web\Response
     */
    public function actionView($id = null)
    {
        $model = User::findOne((int) $id);

        if (empty($model)) {
            $this->error('Sorry! We can not find Member with this ID.');
            return $this->redirect(['admin/members']);
        }

        return $this->render('view', ['model' => $model]);
    }
}