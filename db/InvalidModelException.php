<?php

namespace yii\kladovka\db;

use yii\db\BaseActiveRecord,
    yii\helpers\VarDumper;


class InvalidModelException extends \UnexpectedValueException
{

    protected $model = null;

    public function __construct(BaseActiveRecord $model, $message = 'No message.', $code = 0, \Exception $previous = null)
    {
        $this->model = $model;
        parent::__construct($message, $code, $previous);
    }

    public function getName()
    {
        return 'Invalid Model';
    }

    public function getModel()
    {
        return $this->model;
    }

    public function __toString()
    {
        $model = $this->getModel();
        if ($model->hasErrors()) {
            return parent::__toString() . PHP_EOL . VarDumper::dumpAsString([
                'class' => get_class($model),
                'attributes' => $model->getAttributes(),
                'errors' => $model->getErrors()
            ]);
        } else {
            return parent::__toString() . PHP_EOL . VarDumper::dumpAsString([
                'class' => get_class($model),
                'attributes' => $model->getAttributes()
            ]);
        }
    }
}