<?php

namespace ivanchkv\kladovka\helpers;


class Download
{

    public static function init($url = null)
    {
        return new self($url);
    }

    public function __construct($url = null)
    {
        if (!is_null($url)) {
            $this->setUrl($url);
        }
    }

    private $_url = null;

    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function url($url = null)
    {
        if (!is_null($url)) {
            return $this->setUrl($url);
        } else {
            return $this->getUrl();
        }
    }

    private $_timeout = null;

    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    public function getTimeout()
    {
        return $this->_timeout;
    }

    public function timeout($timeout = null)
    {
        if (!is_null($timeout)) {
            return $this->setTimeout($timeout);
        } else {
            return $this->getTimeout();
        }
    }

    private $_cookie = null;

    public function setCookie($cookie)
    {
        $this->_cookie = $cookie;
        return $this;
    }

    public function getCookie()
    {
        return $this->_cookie;
    }

    public function cookie($cookie = null)
    {
        if (!is_null($cookie)) {
            return $this->setCookie($cookie);
        } else {
            return $this->getCookie();
        }
    }

    private $_referer = null;

    public function setReferer($referer)
    {
        $this->_referer = $referer;
        return $this;
    }

    public function getReferer()
    {
        return $this->_referer;
    }

    public function referer($referer = null)
    {
        if (!is_null($referer)) {
            return $this->setReferer($referer);
        } else {
            return $this->getReferer();
        }
    }

    private $_userAgent = null;

    public function setUserAgent($userAgent)
    {
        $this->_userAgent = $userAgent;
        return $this;
    }

    public function getUserAgent()
    {
        return $this->_userAgent;
    }

    public function userAgent($userAgent = null)
    {
        if (!is_null($userAgent)) {
            return $this->setUserAgent($userAgent);
        } else {
            return $this->getUserAgent();
        }
    }

    private $_httpHeader = null;

    public function setHttpHeader($httpHeader)
    {
        $this->_httpHeader = $httpHeader;
        return $this;
    }

    public function getHttpHeader()
    {
        return $this->_httpHeader;
    }

    public function httpHeader($httpHeader = null)
    {
        if (!is_null($httpHeader)) {
            return $this->setHttpHeader($httpHeader);
        } else {
            return $this->getHttpHeader();
        }
    }

    protected function getCurlOptions()
    {
        $curlOptions = [];
        $url = $this->getUrl();
        if (is_string($url)) {
            $curlOptions[CURLOPT_URL] = $url;
        }
        $timeout = $this->getTimeout();
        if (is_int($timeout)) {
            $curlOptions[CURLOPT_TIMEOUT] = $timeout;
        }
        $cookie = $this->getCookie();
        if (is_string($cookie)) {
            $curlOptions[CURLOPT_COOKIE] = $cookie;
        } elseif (is_array($cookie)) {
            $cookie2 = [];
            foreach ($cookie as $key => $value) {
                if (is_int($key) && is_string($value)) {
                    $cookie2[] = $value;
                } elseif (is_string($key) && is_string($value)) {
                    $cookie2[] = $key . '=' . urlencode($value);
                }
            }
            $curlOptions[CURLOPT_COOKIE] = implode('; ', $cookie2);
        }
        $referer = $this->getReferer();
        if (is_string($referer)) {
            $curlOptions[CURLOPT_REFERER] = $referer;
        }
        $userAgent = $this->getUserAgent();
        if (is_string($userAgent)) {
            $curlOptions[CURLOPT_USERAGENT] = $userAgent;
        }
        $httpHeader = $this->getHttpHeader();
        if (is_string($httpHeader)) {
            $curlOptions[CURLOPT_HTTPHEADER] = preg_split('~[\r\n]+~', $httpHeader, -1, PREG_SPLIT_NO_EMPTY);
        } elseif (is_array($httpHeader)) {
            $httpHeader2 = [];
            foreach ($httpHeader as $key => $value) {
                if (is_int($key) && is_string($value)) {
                    $httpHeader2[] = $value;
                } elseif (is_string($key) && is_string($value)) {
                    $httpHeader2[] = $key . ': ' . $value;
                }
            }
            $curlOptions[CURLOPT_HTTPHEADER] = $httpHeader2;
        }
        return $curlOptions;
    }
}
