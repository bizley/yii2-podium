<?php

namespace bizley\podium\db;

use bizley\podium\Podium;
use yii\db\Query as YiiQuery;

/**
 * Query extended to use Podium db component.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.5
 */
class Query extends YiiQuery
{
    /**
     * Creates a DB command that can be used to execute this query.
     * @param Connection $db the database connection used to generate the SQL statement.
     * If this parameter is not given, the `db` application component will be used.
     * @return Command the created DB command instance.
     */
    public function createCommand($db = null)
    {
        if ($db === null) {
            $db = Podium::getInstance()->db;
        }
        return parent::createCommand($db);
    }
}
