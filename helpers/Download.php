<?php

namespace ivanchkv\kladovka\helpers;


class Download
{

    private $_url = null;

    public function url($url)
    {
        $this->_url = $url;
        return $this;
    }

    public function setUrl($url)
    {
        return $this->url($url);
    }

    public function getUrl()
    {
        return $this->_url;
    }
}
