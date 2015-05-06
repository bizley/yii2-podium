<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Podium Page Sizer widget
 * Renders dropdown  list with acceptable page sizes.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class PageSizer extends Widget
{

    /**
     * @var array List of page sizes
     */
    public $pageSizes = [5 => 5, 10 => 10, 20 => 20, 50 => 50, 100 => 100];

    /**
     * Rendering the widget dropdown.
     * Default page size is 20.
     * @return string
     */
    public function run()
    {
        $selected = Yii::$app->request->get('per-page', 20);

        return Html::tag('div', Html::tag('div', 
                Html::label(Yii::t('podium/view', 'Results per page'), 'per-page') . ' ' . Html::dropDownList('per-page', $selected, $this->pageSizes, ['class' => 'form-control input-sm', 'id' => 'per-page'], ['class' => '']), 
                ['class' => 'form-group']), ['class' => 'pull-right form-inline']) . '<br><br>';
    }

}