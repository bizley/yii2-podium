<?php

namespace bizley\podium\models;

use bizley\podium\models\db\MetaActiveRecord;

/**
 * Meta model
 * User's meta data.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Meta extends MetaActiveRecord
{
    /**
     * Max image sizes.
     */
    const MAX_WIDTH  = 165;
    const MAX_HEIGHT = 165;
    const MAX_SIZE   = 204800;
    
    /**
     * @var mixed Avatar image
     */
    public $image;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [['image', 'image', 
                'mimeTypes' => 'image/png, image/jpeg, image/gif', 
                'maxWidth' => self::MAX_WIDTH, 
                'maxHeight' => self::MAX_HEIGHT, 
                'maxSize' => self::MAX_SIZE],
            ]
        );
    }
}
