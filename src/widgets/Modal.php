<?php

namespace bizley\podium\widgets;

use Yii;
use yii\bootstrap\Modal as YiiModal;
use yii\helpers\Html;

/**
 * Podium Modal widget
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.2
 */
class Modal extends YiiModal
{
    /**
     * @var array the HTML attributes for the widget container tag.
     */
    public $options = ['aria-hidden' => 'true'];
    /**
     * @var array footer confirmation HTML options
     */
    public $footerConfirmOptions = [];
    /**
     * @var mixed footer confirmation URL
     */
    public $footerConfirmUrl = '#';


    /**
     * Initializes the widget.
     */
    public function init()
    {
        $this->header = Html::tag('h4', $this->header, ['class' => 'modal-title', 'id' => $this->id . 'Label']);
        $this->options['aria-labelledby'] = $this->id . 'Label';
        $this->footer = Html::button(Yii::t('podium/view', 'Cancel'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal'])
                . "\n" . Html::a($this->footer, $this->footerConfirmUrl, $this->footerConfirmOptions);

        parent::init();
    }
}
