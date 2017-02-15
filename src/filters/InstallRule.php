<?php

namespace bizley\podium\filters;

use bizley\podium\Podium;
use Yii;

/**
 * Installation access rule
 * Redirects user to installation page if Podium is not installed.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class InstallRule extends PodiumRoleRule
{
    /**
     * @var boolean whether this is an 'allow' rule or 'deny' rule.
     */
    public $allow = false;

    /**
     * Sets match and deny callbacks.
     */
    public function init()
    {
        parent::init();
        $this->matchCallback = function () {
            return !Podium::getInstance()->getInstalled();
        };
        $this->denyCallback = function () {
            return Yii::$app->response->redirect([Podium::getInstance()->prepareRoute('install/run')]);
        };
    }
}
