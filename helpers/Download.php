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

    private $_postFields = null;

    public function setPostFields($postFields)
    {
        $this->_postFields = $postFields;
        return $this;
    }

    public function getPostFields()
    {
        return $this->_postFields;
    }

    public function postFields($postFields = null)
    {
        if (!is_null($postFields)) {
            return $this->setPostFields($postFields);
        } else {
            return $this->getPostFields();
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

    private $_connectTimeout = 5;

    public function setConnectTimeout($connectTimeout)
    {
        $this->_connectTimeout = $connectTimeout;
        return $this;
    }

    public function getConnectTimeout()
    {
        return $this->_connectTimeout;
    }

    public function connectTimeout($connectTimeout = null)
    {
        if (!is_null($connectTimeout)) {
            return $this->setConnectTimeout($connectTimeout);
        } else {
            return $this->getConnectTimeout();
        }
    }

    private $_timeout = 3600;

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

    private $_outputFile = null;
    private $_isTemporaryFile = false;

    public function setOutputFile($outputFile)
    {
        if (is_resource($this->_outputFile) && $this->_isTemporaryFile) {
            fclose($this->_outputFile);
        } elseif (is_string($this->_outputFile) && $this->_isTemporaryFile && file_exists($this->_outputFile)) {
            unlink($this->_outputFile);
        }
        $this->_outputFile = $outputFile;
        $this->_isTemporaryFile = false;
        return $this;
    }

    public function getOutputFile()
    {
        return $this->_outputFile;
    }

    public function outputFile($outputFile = null)
    {
        if (!is_null($outputFile)) {
            return $this->setOutputFile($outputFile);
        } else {
            return $this->getOutputFile();
        }
    }

    public function tempFile()
    {
        $result = $this->setOutputFile(tmpfile());
        $this->_isTemporaryFile = true;
        return $result;
    }

    public function tempFilename()
    {
        $result = $this->setOutputFile(tempnam(sys_get_temp_dir(), uniqid(time())));
        $this->_isTemporaryFile = true;
        return $result;
    }

    private $_options = null;

    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }

    public function getOptions()
    {
        $options = is_array($this->_options) ? $this->_options : [];
        $options[CURLINFO_HEADER_OUT] = true;
        $options[CURLOPT_FOLLOWLOCATION] = true;
        $options[CURLOPT_MAXREDIRS] = 5;
        $url = $this->getUrl();
        if (is_string($url)) {
            $options[CURLOPT_URL] = $url;
        }
        $postFields = $this->getPostFields();
        $options[CURLOPT_POST] = !is_null($postFields);
        if (is_string($postFields)) {
            $options[CURLOPT_POSTFIELDS] = $postFields;
        } elseif (is_array($postFields)) {
            $isMultiPartFormData = false;
            $postFields2 = [];
            foreach ($postFields as $key => $value) {
                if (is_int($key) && is_string($value)) {
                    $postFields2[] = $value;
                } elseif (is_string($key) && is_string($value)) {
                    if ((strlen($value) > 1) && (substr($value, 0, 1) == '@') && file_exists(substr($value, 1))) {
                        $isMultiPartFormData = true;
                        break;
                    }
                    $postFields2[] = $key . '=' . urlencode($value);
                }
            }
            $options[CURLOPT_POSTFIELDS] = $isMultiPartFormData ? $postFields : implode('&', $postFields2);
        }
        $cookie = $this->getCookie();
        if (is_string($cookie)) {
            $options[CURLOPT_COOKIE] = $cookie;
        } elseif (is_array($cookie)) {
            $cookie2 = [];
            foreach ($cookie as $key => $value) {
                if (is_int($key) && is_string($value)) {
                    $cookie2[] = $value;
                } elseif (is_string($key) && is_string($value)) {
                    $cookie2[] = $key . '=' . urlencode($value);
                }
            }
            $options[CURLOPT_COOKIE] = implode('; ', $cookie2);
        }
        $referer = $this->getReferer();
        if (is_string($referer)) {
            $options[CURLOPT_REFERER] = $referer;
        }
        $userAgent = $this->getUserAgent();
        if (is_string($userAgent)) {
            $options[CURLOPT_USERAGENT] = $userAgent;
        }
        $httpHeader = $this->getHttpHeader();
        if (is_string($httpHeader)) {
            $options[CURLOPT_HTTPHEADER] = preg_split('~[\r\n]+~', $httpHeader, -1, PREG_SPLIT_NO_EMPTY);
        } elseif (is_array($httpHeader)) {
            $httpHeader2 = [];
            foreach ($httpHeader as $key => $value) {
                if (is_int($key) && is_string($value)) {
                    $httpHeader2[] = $value;
                } elseif (is_string($key) && is_string($value)) {
                    $httpHeader2[] = $key . ': ' . $value;
                }
            }
            $options[CURLOPT_HTTPHEADER] = $httpHeader2;
        }
        $connectTimeout = $this->getConnectTimeout();
        if (is_int($connectTimeout)) {
            $options[CURLOPT_CONNECTTIMEOUT] = $connectTimeout;
        }
        $timeout = $this->getTimeout();
        if (is_int($timeout)) {
            $options[CURLOPT_TIMEOUT] = $timeout;
        }
        $outputFile = $this->getOutputFile();
        if (is_resource($outputFile) || is_string($outputFile)) {
            $options[CURLOPT_FILE] = $outputFile;
        } else {
            $options[CURLOPT_RETURNTRANSFER] = true;
        }
        return $options;
    }

    public function options($options = null)
    {
        if (!is_null($options)) {
            return $this->setOptions($options);
        } else {
            return $this->getOptions();
        }
    }

    private $_info = null;

    protected function setInfo($info)
    {
        $this->_info = $info;
        return $this;
    }

    public function getInfo()
    {
        return $this->_info;
    }

    public function getHttpCode()
    {
        return is_array($this->_info) ? $this->_info['http_code'] : null;
    }

    public function getContentLength()
    {
        return is_array($this->_info) ? $this->_info['download_content_length'] : null;
    }

    public function execute()
    {
        $result = false;
        $url = $this->setInfo(null)->getUrl();
        $ch = curl_init($url);
        if ($ch) {
            $options = $this->getOptions();
            $isOutputFileString = false;
            if (array_key_exists(CURLOPT_FILE, $options) && is_string($options[CURLOPT_FILE])) {
                $isOutputFileString = true;
                $options[CURLOPT_FILE] = fopen($options[CURLOPT_FILE], 'w');
            }
            if (curl_setopt_array($ch, $options)) {
                $result = curl_exec($ch);
                $info = curl_getinfo($ch);
                $n = 0;
                while (!$result && !$info['http_code'] && (++ $n <= 3)) {
                    sleep(5);
                    $result = curl_exec($ch);
                    $info = curl_getinfo($ch);
                }
                if (($info['http_code'] == 200) && ($info['download_content_length'] <= 0)) {
                    $info['http_code'] = 204; // No Content
                }
                $this->setInfo($info);
            }
            if ($isOutputFileString) {
                fclose($options[CURLOPT_FILE]);
            }
            curl_close($ch);
        }
        return $result;
    }

    public function __destruct()
    {
        $this->setOutputFile(null);
    }
}
