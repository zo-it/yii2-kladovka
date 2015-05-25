<?php

namespace yii\kladovka\db;

use yii\db\BaseActiveRecord,
    Exception,
    UnexpectedValueException,
    yii\helpers\VarDumper;


class InvalidModelException extends UnexpectedValueException
{

    private $_model = null;

    public function __construct(BaseActiveRecord $model, $message = 'No message.', $code = 0, Exception $previous = null)
    {
        $this->_model = $model;
        parent::__construct($message, $code, $previous);
    }

    public function getName()
    {
        return 'Invalid Model';
    }

    /**
     * @return BaseActiveRecord
     */
    public function getModel()
    {
        return $this->_model;
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
