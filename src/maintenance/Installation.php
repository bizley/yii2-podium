<?php

namespace bizley\podium\maintenance;

use bizley\podium\models\Content;
use bizley\podium\models\User;
use bizley\podium\Podium;
use bizley\podium\PodiumConfig;
use bizley\podium\rbac\Rbac;
use Exception;
use Yii;
use yii\helpers\Html;
use yii\helpers\VarDumper;

/**
 * Podium Installation
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 *
 * @property array $steps
 */
class Installation extends Maintenance
{
    const DEFAULT_USERNAME = 'admin';
    const SESSION_KEY = 'podium-installation';
    const SESSION_STEPS = 'steps';

    /**
     * @var array Installation steps
     */
    private $_steps;

    /**
     * Adds Administrator account.
     * @return string result message.
     */
    protected function addAdmin()
    {
        if ($this->module->userComponent !== true) {
            return $this->addInheritedAdmin();
        }
        $transaction = $this->db->beginTransaction();
        try {
            $admin = new User();
            $admin->setScenario('installation');
            $admin->setAttributes([
                'username' => self::DEFAULT_USERNAME,
                'status' => User::STATUS_ACTIVE,
                'role' => User::ROLE_ADMIN,
            ], false);
            $admin->generateAuthKey();
            $admin->setPassword(self::DEFAULT_USERNAME);
            if (!$admin->save()) {
                throw new Exception(VarDumper::dumpAsString($admin->errors));
            }
            if (!$this->authManager->assign($this->authManager->getRole(Rbac::ROLE_ADMIN), $admin->id)) {
                throw new Exception('Error during Administrator privileges setting!');
            }
            $transaction->commit();
            return $this->returnSuccess(
                Yii::t('podium/flash', 'Administrator account has been created.')
                . ' ' . Html::tag('strong', Yii::t('podium/flash', 'Login') . ':')
                . ' ' . Html::tag('kbd', self::DEFAULT_USERNAME)
                . ' ' . Html::tag('strong', Yii::t('podium/flash', 'Password') . ':')
                . ' ' . Html::tag('kbd', self::DEFAULT_USERNAME)
                . '<br>'
                . Html::tag('strong', Yii::t('podium/flash', 'Remember to change these credentials after first login!'), ['class' => 'text-danger'])
            );
        } catch (Exception $e) {
            $transaction->rollBack();
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during account creating')
            );
        }
    }

    /**
     * Adds Administrator account for inherited User Identity.
     * @return string result message.
     * @since 0.2
     */
    protected function addInheritedAdmin()
    {
        if (empty($this->module->adminId)) {
            $this->type = self::TYPE_WARNING;
            return Yii::t('podium/flash', 'No administrator privileges have been set!');
        }
        try {
            $identity = Podium::getInstance()->user->identityClass;
            $inheritedUser = $identity::findIdentity($this->module->adminId);
            if (!$inheritedUser) {
                throw new Exception();
            }
        } catch (Exception $e) {
            return $this->returnWarning(Yii::t('podium/flash', 'Cannot find inherited user of given ID. No administrator privileges have been set.'));
        }

        $transaction = $this->db->beginTransaction();
        try {
            $admin = new User();
            $admin->setScenario('installation');
            $admin->setAttributes([
                'inherited_id' => $this->module->adminId,
                'username' => self::DEFAULT_USERNAME,
                'status' => User::STATUS_ACTIVE,
                'role' => User::ROLE_ADMIN,
            ], false);
            if (!$admin->save()) {
                throw new Exception(VarDumper::dumpAsString($admin->errors));
            }
            if (!$this->authManager->assign($this->authManager->getRole(Rbac::ROLE_ADMIN), $admin->id)) {
                throw new Exception('Error during Administrator privileges setting!');
            }
            $transaction->commit();
            return $this->returnSuccess(Yii::t('podium/flash', 'Administrator privileges have been set for the user of ID {id}.', [
                'id' => $this->module->adminId
            ]));
        } catch (Exception $e) {
            $transaction->rollBack();
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during account creating')
            );
        }
    }

    /**
     * Adds config default settings.
     * @return string result message.
     */
    protected function addConfig()
    {
        try {
            $this->db->createCommand()->batchInsert(
                    PodiumConfig::tableName(),
                    ['name', 'value'],
                    [
                        ['activation_token_expire', PodiumConfig::SECONDS_ACTIVATION_TOKEN_EXPIRE],
                        ['allow_polls', PodiumConfig::FLAG_ALLOW_POLLS],
                        ['email_token_expire', PodiumConfig::SECONDS_EMAIL_TOKEN_EXPIRE],
                        ['from_email', PodiumConfig::DEFAULT_FROM_EMAIL],
                        ['from_name', PodiumConfig::DEFAULT_FROM_NAME],
                        ['hot_minimum', PodiumConfig::HOT_MINIMUM],
                        ['maintenance_mode', PodiumConfig::MAINTENANCE_MODE],
                        ['max_attempts', PodiumConfig::MAX_SEND_ATTEMPTS],
                        ['members_visible', PodiumConfig::FLAG_MEMBERS_VISIBLE],
                        ['merge_posts', PodiumConfig::FLAG_MERGE_POSTS],
                        ['meta_description', PodiumConfig::META_DESCRIPTION],
                        ['meta_keywords', PodiumConfig::META_KEYWORDS],
                        ['name', PodiumConfig::PODIUM_NAME],
                        ['password_reset_token_expire', PodiumConfig::SECONDS_PASSWORD_RESET_TOKEN_EXPIRE],
                        ['recaptcha_secretkey', ''],
                        ['recaptcha_sitekey', ''],
                        ['registration_off', PodiumConfig::REGISTRATION_OFF],
                        ['use_captcha', PodiumConfig::FLAG_USE_CAPTCHA],
                        ['use_wysiwyg', PodiumConfig::FLAG_USE_WYSIWYG],
                        ['version', Podium::getInstance()->version],
                    ]
                )->execute();
            return $this->returnSuccess(Yii::t('podium/flash', 'Default Config settings have been added.'));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during settings adding')
            );
        }
    }

    /**
     * Adds default content.
     * @return string result message.
     */
    protected function addContent()
    {
        try {
            $default = Content::defaultContent();
            $this->db->createCommand()->batchInsert(
                    Content::tableName(),
                    ['name', 'topic', 'content'],
                    [
                        [
                            Content::TERMS_AND_CONDS,
                            $default[Content::TERMS_AND_CONDS]['topic'],
                            $default[Content::TERMS_AND_CONDS]['content']
                        ],
                        [
                            Content::EMAIL_REGISTRATION,
                            $default[Content::EMAIL_REGISTRATION]['topic'],
                            $default[Content::EMAIL_REGISTRATION]['content']
                        ],
                        [
                            Content::EMAIL_PASSWORD,
                            $default[Content::EMAIL_PASSWORD]['topic'],
                            $default[Content::EMAIL_PASSWORD]['content']
                        ],
                        [
                            Content::EMAIL_REACTIVATION,
                            $default[Content::EMAIL_REACTIVATION]['topic'],
                            $default[Content::EMAIL_REACTIVATION]['content']
                        ],
                        [
                            Content::EMAIL_NEW,
                            $default[Content::EMAIL_NEW]['topic'],
                            $default[Content::EMAIL_NEW]['content']
                        ],
                        [
                            Content::EMAIL_SUBSCRIPTION,
                            $default[Content::EMAIL_SUBSCRIPTION]['topic'],
                            $default[Content::EMAIL_SUBSCRIPTION]['content']
                        ],
                    ]
                )->execute();
            return $this->returnSuccess(Yii::t('podium/flash', 'Default Content has been added.'));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during content adding')
            );
        }
    }

    /**
     * Adds permission rules.
     * @return string result message.
     */
    protected function addRules()
    {
        try {
            (new Rbac())->add($this->authManager);
            return $this->returnSuccess(Yii::t('podium/flash', 'Access roles have been created.'));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during access roles creating')
            );
        }
    }

    /**
     * Proceeds next installation step.
     * @return array
     * @since 0.2
     */
    public function nextStep()
    {
        $currentStep = Yii::$app->session->get(self::SESSION_KEY, 0);
        if ($currentStep === 0) {
            Yii::$app->session->set(self::SESSION_STEPS, count($this->steps));
        }
        $maxStep = Yii::$app->session->get(self::SESSION_STEPS, 0);

        $this->type = self::TYPE_ERROR;
        $this->table = '...';

        if ($currentStep >= $maxStep) {
            return [
                'drop' => false,
                'type' => $this->type,
                'result' => Yii::t('podium/flash', 'Weird... Installation should already complete...'),
                'percent' => 100
            ];
        }
        if (!isset($this->steps[$currentStep])) {
            return [
                'drop' => false,
                'type' => $this->type,
                'result' => Yii::t('podium/flash', 'Installation aborted! Can not find the requested installation step.'),
                'percent' => 100,
            ];
        }

        $this->table = $this->steps[$currentStep]['table'];
        $result = call_user_func_array([$this, $this->steps[$currentStep]['call']], $this->steps[$currentStep]['data']);

        Yii::$app->session->set(self::SESSION_KEY, ++$currentStep);

        return [
            'drop' => false,
            'type' => $this->type,
            'result' => $result,
            'table' => $this->rawTable,
            'percent' => $this->countPercent($currentStep, $maxStep),
        ];
    }

    /**
     * Proceeds next drop step.
     * @return array
     * @since 0.2
     */
    public function nextDrop()
    {
        $drops = $this->countDrops();
        if (count($drops)) {
            $currentStep = Yii::$app->session->get(self::SESSION_KEY, 0);
            $maxStep = Yii::$app->session->get(self::SESSION_STEPS, 0);
            if ($currentStep < $maxStep) {
                $this->type = self::TYPE_ERROR;
                $this->table = '...';

                if (!isset($drops[$currentStep])) {
                    return [
                        'drop' => false,
                        'type' => $this->type,
                        'result' => Yii::t('podium/flash', 'Installation aborted! Can not find the requested drop step.'),
                        'percent' => 100,
                    ];
                }

                $this->table = $drops[$currentStep]['table'];

                $result = $this->dropTable();
                if ($result === true) {
                    Yii::$app->session->set(self::SESSION_KEY, ++$currentStep);
                    return $this->nextDrop();
                }

                Yii::$app->session->set(self::SESSION_KEY, ++$currentStep);
                return [
                    'drop' => true,
                    'type' => $this->type,
                    'result' => $result,
                    'table' => $this->rawTable,
                    'percent' => $this->countPercent($currentStep, $maxStep),
                ];
            }
        }
        Yii::$app->session->set(self::SESSION_KEY, 0);
        return $this->nextStep();
    }

    /**
     * Returns list of drops.
     * @return array
     */
    protected function countDrops()
    {
        $steps = array_reverse($this->steps);
        $drops = [];
        foreach ($steps as $step) {
            if (isset($step['call']) && $step['call'] === 'createTable') {
                $drops[] = $step;
            }
        }
        if (Yii::$app->session->get(self::SESSION_KEY, 0) === 0) {
            Yii::$app->session->set(self::SESSION_STEPS, count($drops));
        }
        return $drops;
    }

    /**
     * Installation steps.
     * @since 0.2
     */
    public function getSteps()
    {
        if ($this->_steps === null) {
            $this->_steps = require(__DIR__ . '/steps/install.php');
        }
        return $this->_steps;
    }
}
