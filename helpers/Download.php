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

    private $_scheme = null;

    public function setScheme($scheme)
    {
        $this->_scheme = $scheme;
        return $this;
    }

    public function getScheme()
    {
        return $this->_scheme;
    }

    public function scheme($scheme = null)
    {
        if (!is_null($scheme)) {
            return $this->setScheme($scheme);
        } else {
            return $this->getScheme();
        }
    }

    private $_user = null;

    public function setUser($user)
    {
        $this->_user = $user;
        return $this;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function user($user = null)
    {
        if (!is_null($user)) {
            return $this->setUser($user);
        } else {
            return $this->getUser();
        }
    }

    private $_password = null;

    public function setPassword($password)
    {
        $this->_password = $password;
        return $this;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function password($password = null)
    {
        if (!is_null($password)) {
            return $this->setPassword($password);
        } else {
            return $this->getPassword();
        }
    }

    private $_host = null;

    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function host($host = null)
    {
        if (!is_null($host)) {
            return $this->setHost($host);
        } else {
            return $this->getHost();
        }
    }

    private $_port = null;

    public function setPort($port)
    {
        $this->_port = $port;
        return $this;
    }

    public function getPort()
    {
        return $this->_port;
    }

    public function port($port = null)
    {
        if (!is_null($port)) {
            return $this->setPort($port);
        } else {
            return $this->getPort();
        }
    }

    private $_path = null;

    public function setPath($path)
    {
        $this->_path = $path;
        return $this;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function path($path = null)
    {
        if (!is_null($path)) {
            return $this->setPath($path);
        } else {
            return $this->getPath();
        }
    }

    private $_query = null;

    public function setQuery($query)
    {
        $this->_query = $query;
        return $this;
    }

    public function getQuery()
    {
        return $this->_query;
    }

    public function query($query = null)
    {
        if (!is_null($query)) {
            return $this->setQuery($query);
        } else {
            return $this->getQuery();
        }
    }

    private $_fragment = null;

    public function setFragment($fragment)
    {
        $this->_fragment = $fragment;
        return $this;
    }

    public function getFragment()
    {
        return $this->_fragment;
    }

    public function fragment($fragment = null)
    {
        if (!is_null($fragment)) {
            return $this->setFragment($fragment);
        } else {
            return $this->getFragment();
        }
    }

    private $_url = null;

    public function setUrl($url)
    {
        $this->_url = $url;
        $parsedUrl = parse_url($url);
        if ($parsedUrl && is_array($parsedUrl)) {
            $this->setScheme(array_key_exists('scheme', $parsedUrl) ? $parsedUrl['scheme'] : null);
            $this->setUser(array_key_exists('user', $parsedUrl) ? $parsedUrl['user'] : null);
            $this->setPassword(array_key_exists('pass', $parsedUrl) ? $parsedUrl['pass'] : null);
            $this->setHost(array_key_exists('host', $parsedUrl) ? $parsedUrl['host'] : null);
            $this->setPort(array_key_exists('port', $parsedUrl) ? (int)$parsedUrl['port'] : null);
            $this->setPath(array_key_exists('path', $parsedUrl) ? $parsedUrl['path'] : null);
            $this->setQuery(array_key_exists('query', $parsedUrl) ? $parsedUrl['query'] : null);
            $this->setFragment(array_key_exists('fragment', $parsedUrl) ? $parsedUrl['fragment'] : null);
        } else {
            $this->setScheme(null);
            $this->setUser(null);
            $this->setPassword(null);
            $this->setHost(null);
            $this->setPort(null);
            $this->setPath(null);
            $this->setQuery(null);
            $this->setFragment(null);
        }
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

    const PROXY_TYPE_HTTP = CURLPROXY_HTTP;
    const PROXY_TYPE_SOCKS5 = CURLPROXY_SOCKS5;

    private $_proxyType = null;

    public function setProxyType($proxyType)
    {
        $this->_proxyType = $proxyType;
        return $this;
    }

    public function getProxyType()
    {
        return $this->_proxyType;
    }

    public function proxyType($proxyType = null)
    {
        if (!is_null($proxyType)) {
            return $this->setProxyType($proxyType);
        } else {
            return $this->getProxyType();
        }
    }

    private $_proxyUser = null;

    public function setProxyUser($proxyUser)
    {
        $this->_proxyUser = $proxyUser;
        return $this;
    }

    public function getProxyUser()
    {
        return $this->_proxyUser;
    }

    public function proxyUser($proxyUser = null)
    {
        if (!is_null($proxyUser)) {
            return $this->setProxyUser($proxyUser);
        } else {
            return $this->getProxyUser();
        }
    }

    private $_proxyPassword = null;

    public function setProxyPassword($proxyPassword)
    {
        $this->_proxyPassword = $proxyPassword;
        return $this;
    }

    public function getProxyPassword()
    {
        return $this->_proxyPassword;
    }

    public function proxyPassword($proxyPassword = null)
    {
        if (!is_null($proxyPassword)) {
            return $this->setProxyPassword($proxyPassword);
        } else {
            return $this->getProxyPassword();
        }
    }

    private $_proxyHost = null;

    public function setProxyHost($proxyHost)
    {
        $this->_proxyHost = $proxyHost;
        return $this;
    }

    public function getProxyHost()
    {
        return $this->_proxyHost;
    }

    public function proxyHost($proxyHost = null)
    {
        if (!is_null($proxyHost)) {
            return $this->setProxyHost($proxyHost);
        } else {
            return $this->getProxyHost();
        }
    }

    private $_proxyPort = null;

    public function setProxyPort($proxyPort)
    {
        $this->_proxyPort = $proxyPort;
        return $this;
    }

    public function getProxyPort()
    {
        return $this->_proxyPort;
    }

    public function proxyPort($proxyPort = null)
    {
        if (!is_null($proxyPort)) {
            return $this->setProxyPort($proxyPort);
        } else {
            return $this->getProxyPort();
        }
    }

    private $_proxyUrl = null;

    public function setProxyUrl($proxyUrl)
    {
        $this->_proxyUrl = $proxyUrl;
        $parsedUrl = parse_url($proxyUrl);
        if ($parsedUrl && is_array($parsedUrl)) {
            if (array_key_exists('scheme', $parsedUrl)) {
                $scheme = strtolower($parsedUrl['scheme']);
                if (strncmp($scheme, 'http', 4) == 0) {
                    $this->setProxyType(self::PROXY_TYPE_HTTP);
                } elseif (strncmp($scheme, 'sock', 4) == 0) {
                    $this->setProxyType(self::PROXY_TYPE_SOCKS5);
                } else {
                    $this->setProxyType(null);
                }
            } else {
                $this->setProxyType(null);
            }
            $this->setProxyUser(array_key_exists('user', $parsedUrl) ? $parsedUrl['user'] : null);
            $this->setProxyPassword(array_key_exists('pass', $parsedUrl) ? $parsedUrl['pass'] : null);
            $this->setProxyHost(array_key_exists('host', $parsedUrl) ? $parsedUrl['host'] : null);
            $this->setProxyPort(array_key_exists('port', $parsedUrl) ? (int)$parsedUrl['port'] : null);
        } else {
            $this->setProxyType(null);
            $this->setProxyUser(null);
            $this->setProxyPassword(null);
            $this->setProxyHost(null);
            $this->setProxyPort(null);
        }
        return $this;
    }

    public function getProxyUrl()
    {
        return $this->_proxyUrl;
    }

    public function proxyUrl($proxyUrl = null)
    {
        if (!is_null($proxyUrl)) {
            return $this->setProxyUrl($proxyUrl);
        } else {
            return $this->getProxyUrl();
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

    protected function buildUrl()
    {
        $scheme = $this->getScheme();
        $host = $this->getHost();
        if ($scheme && is_string($scheme) && $host && is_string($host)) {
            $url = $scheme . '://' . $host;
            $port = $this->getPort();
            if ($port && is_int($port)) {
                $url .= ':' . $port;
            }
            $path = $this->getPath();
            if ($path && is_string($path)) {
                $url .= $path;
            }
            $query = $this->getQuery();
            if (is_string($query)) {
                $url .= '?' . $query;
            }
            $fragment = $this->getFragment();
            if (is_string($fragment)) {
                $url .= '#' . $fragment;
            }
            return $url;
        }
        return false;
    }

    public function getOptions()
    {
        $options = is_array($this->_options) ? $this->_options : [];
        $options[CURLINFO_HEADER_OUT] = true;
        $options[CURLOPT_FOLLOWLOCATION] = true;
        $options[CURLOPT_MAXREDIRS] = 5;
        // url
        $url = $this->buildUrl();
        if (!$url) {
            $url = $this->getUrl();
        }
        if ($url && is_string($url)) {
            $options[CURLOPT_URL] = $url;
        }
        // user password
        $user = $this->getUser();
        if ($user && is_string($user)) {
            $password = $this->getPassword();
            if ($password && is_string($password)) {
                $options[CURLOPT_USERPWD] = $user . ':' . $password;
            } else {
                $options[CURLOPT_USERPWD] = $user;
            }
        }
$proxyType = $this->getProxyType();
$proxyHost = $this->getProxyHost();
$proxyPort = $this->getProxyPort();
if ($proxyPort && is_int($proxyPort)) {
$options[CURLOPT_PROXYPORT] = $proxyPort;
}

        // proxy user password
        $proxyUser = $this->getProxyUser();
        if ($proxyUser && is_string($proxyUser)) {
            $proxyPassword = $this->getProxyPassword();
            if ($proxyPassword && is_string($proxyPassword)) {
                $options[CURLOPT_PROXYUSERPWD] = $proxyUser . ':' . $proxyPassword;
            } else {
                $options[CURLOPT_PROXYUSERPWD] = $proxyUser;
            }
        }
        // post fields
        $postFields = $this->getPostFields();
        $options[CURLOPT_POST] = !is_null($postFields);
        if ($postFields) {
            if (is_string($postFields)) {
                $options[CURLOPT_POSTFIELDS] = $postFields;
            } elseif (is_array($postFields)) {
                $isMultiPartFormData = false;
                $postFields2 = [];
                foreach ($postFields as $key => $value) {
                    if (is_int($key) && is_string($value)) {
                        $postFields2[] = $value;
                    } elseif (is_string($key) && is_scalar($value)) {
                        if (is_string($value) && (strlen($value) > 1) && (substr($value, 0, 1) == '@') && file_exists(substr($value, 1))) {
                            $isMultiPartFormData = true;
                            break;
                        }
                        $postFields2[] = $key . '=' . urlencode($value);
                    }
                }
                $options[CURLOPT_POSTFIELDS] = $isMultiPartFormData ? $postFields : implode('&', $postFields2);
            }
        }
        // cookie
        $cookie = $this->getCookie();
        if ($cookie) {
            if (is_string($cookie)) {
                $options[CURLOPT_COOKIE] = $cookie;
            } elseif (is_array($cookie)) {
                $cookie2 = [];
                foreach ($cookie as $key => $value) {
                    if (is_int($key) && is_string($value)) {
                        $cookie2[] = $value;
                    } elseif (is_string($key) && is_scalar($value)) {
                        $cookie2[] = $key . '=' . urlencode($value);
                    }
                }
                $options[CURLOPT_COOKIE] = implode('; ', $cookie2);
            }
        }
        // referer
        $referer = $this->getReferer();
        if ($referer && is_string($referer)) {
            $options[CURLOPT_REFERER] = $referer;
        }
        // user agent
        $userAgent = $this->getUserAgent();
        if ($userAgent && is_string($userAgent)) {
            $options[CURLOPT_USERAGENT] = $userAgent;
        }
        // http header
        $httpHeader = $this->getHttpHeader();
        if ($httpHeader) {
            if (is_string($httpHeader)) {
                $options[CURLOPT_HTTPHEADER] = preg_split('~[\r\n]+~', $httpHeader, -1, PREG_SPLIT_NO_EMPTY);
            } elseif (is_array($httpHeader)) {
                $httpHeader2 = [];
                foreach ($httpHeader as $key => $value) {
                    if (is_int($key) && is_string($value)) {
                        $httpHeader2[] = $value;
                    } elseif (is_string($key) && is_scalar($value)) {
                        $httpHeader2[] = $key . ': ' . $value;
                    }
                }
                $options[CURLOPT_HTTPHEADER] = $httpHeader2;
            }
        }
        // connect timeout
        $connectTimeout = $this->getConnectTimeout();
        if ($connectTimeout && is_int($connectTimeout)) {
            $options[CURLOPT_CONNECTTIMEOUT] = $connectTimeout;
        }
        // timeout
        $timeout = $this->getTimeout();
        if ($timeout && is_int($timeout)) {
            $options[CURLOPT_TIMEOUT] = $timeout;
        }
        // output file
        $outputFile = $this->getOutputFile();
        if ($outputFile && (is_resource($outputFile) || is_string($outputFile))) {
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
