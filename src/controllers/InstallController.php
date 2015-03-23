<?php

namespace bizley\podium\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Json;
use bizley\podium\components\Installation;

class InstallController extends Controller
{
    public $layout = 'installation';
    
    protected function _passCheck()
    {
        if ($this->module->getParam('mode') !== 'INSTALL') {
            return $this->redirect('prereq');
        }
    }
    
    public function actionPrereq()
    {
        return $this->render('prereq');
    }
    
    public function actionRun()
    {
        $this->_passCheck();
        
        return $this->render('run');
    }

    public function actionImport()
    {
        $this->_passCheck();
        
        $result = ['error' => Yii::t('podium/view', 'Error')];

        if (Yii::$app->request->isPost) {
            $step = Yii::$app->request->post('step');
            if (is_numeric($step)) {
                $result = (new Installation())->step($step);
            }
        }

        return Json::encode($result);
    }
}
