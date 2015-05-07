<?php

namespace yii\kladovka\db;

use yii\db\ActiveQuery as YiiActiveQuery;


class ActiveQuery extends YiiActiveQuery
{

    private $_alias = null;

    public function from($tables)
    {
        $this->_alias = null;
        return parent::from($tables);
    }

    public function getAlias()
    {
        if (!is_null($this->_alias)) {
            return $this->_alias;
        }
        if (empty($this->from)) {
            /* @var $modelClass ActiveRecord */
            $modelClass = $this->modelClass;
            $tableName = $modelClass::tableName();
        } else {
            $tableName = '';
            foreach ($this->from as $alias => $tableName) {
                if (is_string($alias)) {
                    $this->_alias = $alias;
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
        $this->_alias = $alias;
        return $alias;
    }
}
