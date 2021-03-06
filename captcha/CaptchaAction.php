<?php

namespace yii\kladovka\captcha;

use yii\captcha\CaptchaAction as YiiCaptchaAction,
    Yii;


class CaptchaAction extends YiiCaptchaAction
{

    public $regenerateIfAjax = false;

    public $isDigital = false;

    public function getVerifyCode($regenerate = false)
    {
        if (!$this->regenerateIfAjax) {
            $request = Yii::$app->getRequest();
            if ($request->getIsAjax() && !$request->getQueryParam(self::REFRESH_GET_VAR)) {
                $regenerate = false;
            }
        }
        return parent::getVerifyCode($regenerate);
    }

    protected function generateVerifyCode()
    {
        if ($this->isDigital) {
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
        }
        return parent::generateVerifyCode();
    }
}
