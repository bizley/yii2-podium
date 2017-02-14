<?php

namespace bizley\podium\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Podium Page Sizer widget
 * Renders dropdown  list with acceptable page sizes.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class PageSizer extends Widget
{
    /**
     * @var array List of page sizes
     */
    public $pageSizes = [5 => 5, 10 => 10, 20 => 20, 50 => 50];

    /**
     * Rendering the widget dropdown.
     * Default page size is 20.
     * @return string
     */
    public function run()
    {
        $size = 20;
        $saved = Yii::$app->session->get('per-page');
        if (in_array($saved, $this->pageSizes)) {
            $size = $saved;
        }
        $selected = Yii::$app->request->get('per-page');
        if (in_array($selected, $this->pageSizes)) {
            $size = $selected;
        }

        Yii::$app->session->set('per-page', $size);

        return Html::tag('div', Html::tag('div',
            Html::label(Yii::t('podium/view', 'Results per page'), 'per-page')
            . ' '
            . Html::dropDownList('per-page', $size, $this->pageSizes, ['class' => 'form-control input-sm', 'id' => 'per-page']),
            ['class' => 'form-group']
        ), ['class' => 'pull-right form-inline']) . '<br><br>';
    }
}
