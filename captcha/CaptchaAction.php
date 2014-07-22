<?php

namespace yii\kladovka\captcha;

use Yii,
    yii\captcha\CaptchaAction as CaptchaActionBase;


class CaptchaAction extends CaptchaActionBase
{

    private $_ignoreRegenerateIfAjax = true;

    public function setIgnoreRegenerateIfAjax($ignoreRegenerateIfAjax)
    {
        $this->_ignoreRegenerateIfAjax = $ignoreRegenerateIfAjax;
    }

    public function getIgnoreRegenerateIfAjax()
    {
        return $this->_ignoreRegenerateIfAjax;
    }

    private $_isDigital = true;

    public function setIsDigital($isDigital)
    {
        $this->_isDigital = $isDigital;
    }

    public function getIsDigital()
    {
        return $this->_isDigital;
    }

    public function getVerifyCode($regenerate = false)
    {
        if ($this->getIgnoreRegenerateIfAjax() && Yii::$app->getRequest()->getIsAjax()) {
            return parent::getVerifyCode(false);
        } else {
            return parent::getVerifyCode($regenerate);
        }
    }

    protected function generateVerifyCode()
    {
        if ($this->getIsDigital()) {
            if ($this->minLength > $this->maxLength) {
                $this->maxLength = $this->minLength;
            }
            if ($this->minLength < 3) {
                $this->minLength = 3;
            }
            if ($this->maxLength > 20) {
                $this->maxLength = 20;
            }
            $length = mt_rand($this->minLength, $this->maxLength);
            $code = '';
            for ($i = 0; $i < $length; $i ++) {
                $code .= mt_rand(0, 9);
            }
            return $code;
        } else {
            return parent::generateVerifyCode();
        }
    }
}
