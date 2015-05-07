<?php

namespace yii\kladovka\db;

use yii\db\ActiveQuery as YiiActiveQuery;


class ActiveQuery extends YiiActiveQuery
{

    public function from($tables)
    {
        return parent::from($tables);
    }

    public function getAlias()
    {
        if (empty($this->from)) {
            /* @var $modelClass ActiveRecord */
            $modelClass = $this->modelClass;
            $tableName = $modelClass::tableName();
        } else {
            $tableName = '';
            foreach ($this->from as $alias => $tableName) {
                if (is_string($alias)) {
                    return $alias;
                } else {
                    break;
                }
            }
        }
        if (preg_match('/^(.*?)\s+({{\w+}}|\w+)$/', $tableName, $matches)) {
            $alias = $matches[2];
        } else {
            $alias = $tableName;
        }
        return $alias;
    }
}
