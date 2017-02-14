<?php

namespace bizley\podium\services;

use bizley\podium\db\ActiveRecord;
use bizley\podium\db\Query;
use bizley\podium\log\Log;
use bizley\podium\Podium;
use Exception;
use yii\base\Component;

/**
 * Order Sorter
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class Sorter extends Component
{
    /**
     * @var ActiveRecord
     */
    public $target;

    /**
     * @var Query
     */
    public $query;

    /**
     * @var int new order
     */
    public $order;

    /**
     * Runs sorter.
     * @return bool
     */
    public function run()
    {
        try {
            $next = 0;
            $newSort = -1;
            foreach ($this->query->each() as $id => $model) {
                if ($next == $this->order) {
                    $newSort = $next++;
                }
                Podium::getInstance()->db->createCommand()->update(
                        call_user_func([$this->target, 'tableName']), ['sort' => $next], ['id' => $id]
                    )->execute();
                $next++;
            }
            if ($newSort == -1) {
                $newSort = $next;
            }
            $this->target->sort = $newSort;
            if (!$this->target->save()) {
                throw new Exception('Order saving error');
            }
            Log::info('Orded updated', $this->target->id, __METHOD__);
            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
