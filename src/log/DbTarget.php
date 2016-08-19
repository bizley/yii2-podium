<?php

namespace bizley\podium\log;

use yii\log\DbTarget as YiiDbTarget;
use yii\helpers\VarDumper;

/**
 * Database log target
 * Few extra columns.
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class DbTarget extends YiiDbTarget
{
    /**
     * Stores log messages to DB.
     */
    public function export()
    {
        $tableName = $this->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[prefix]], [[message]], [[model]], [[blame]])
                VALUES (:level, :category, :log_time, :prefix, :message, :model, :blame)";
        $command = $this->db->createCommand($sql);
        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            $extracted = [
                'msg'   => '',
                'model' => null,
                'blame' => null,
            ];
            if (is_array($text) && (isset($text['msg']) || isset($text['model']) || isset($text['blame']))) {
                if (isset($text['msg'])) {
                    if (!is_string($text['msg'])) {
                        $extracted['msg'] = VarDumper::export($text['msg']);
                    } else {
                        $extracted['msg'] = $text['msg'];
                    }
                }
                if (isset($text['model'])) {
                    $extracted['model'] = $text['model'];
                }
                if (isset($text['blame'])) {
                    $extracted['blame'] = $text['blame'];
                }
            } elseif (is_string($text)) {
                $extracted['msg'] = $text;
            } else {
                $extracted['msg'] = VarDumper::export($text);
            }
            $command->bindValues([
                ':level'    => $level,
                ':category' => $category,
                ':log_time' => $timestamp,
                ':prefix'   => $this->getMessagePrefix($message),
                ':message'  => $extracted['msg'],
                ':model'    => $extracted['model'],
                ':blame'    => $extracted['blame'],
            ])->execute();
        }
    }
}
