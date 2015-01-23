<?php

namespace yii\kladovka\db;

use yii\db\BaseActiveRecord;


class InvalidModelException extends \UnexpectedValueException
{

    public $model = null;

    public function __construct(BaseActiveRecord $model, $message = 'No message.', $code = 0, \Exception $previous = null)
    {
        $this->model = $model;
        parent::__construct($message, $code, $previous);
    }

    public function getName()
    {
        return 'Invalid Model';
    }
}
