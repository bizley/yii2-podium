<?php

namespace bizley\podium\widgets\gridview;

use bizley\podium\helpers\Helper;
use yii\grid\DataColumn as YiiDataColumn;

/**
 * Podium DataColumn
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.2
 */
class DataColumn extends YiiDataColumn
{
    /**
     * @var boolean whether the header label should be HTML-encoded.
     */
    public $encodeLabel = false;

    /**
     * @inheritdoc
     */
    protected function getHeaderCellLabel()
    {
        if (!empty($this->attribute)) {
            return parent::getHeaderCellLabel() . Helper::sortOrder($this->attribute);
        }
        return parent::getHeaderCellLabel();
    }
}
