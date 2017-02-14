<?php

namespace bizley\podium\widgets\gridview;

use bizley\podium\Podium;
use bizley\podium\widgets\PageSizer;
use yii\grid\GridView as YiiGridView;
use yii\widgets\Pjax;

/**
 * Podium GridView widget.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.2
 */
class GridView extends YiiGridView
{
    /**
     * @var array the HTML attributes for the container tag of the grid view.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     */
    public $options = ['class' => 'grid-view table-responsive'];
    /**
     * @var string the default data column class if the class name is not explicitly specified when configuring a data column.
     */
    public $dataColumnClass = 'bizley\podium\widgets\gridview\DataColumn';
    /**
     * @var array the HTML attributes for the grid table element.
     */
    public $tableOptions = ['class' => 'table table-striped table-hover'];
    /**
     * @var string additional jQuery selector for selecting filter input fields
     */
    public $filterSelector = 'select#per-page';

    /**
     * Sets formatter to use Podium component.
     * @since 0.5
     */
    public function init()
    {
        parent::init();
        $this->formatter = Podium::getInstance()->formatter;
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        Pjax::begin();
        echo PageSizer::widget();
        parent::run();
        Pjax::end();
    }
}
