<?php

namespace ivanchkv\kladovka\net;


class Curl
{

    public static function init($config = null)
    {
        return new self($config);
    }

    public function __construct($config = null)
    {
        $handle = curl_init();
        if (!$handle) {
            throw new \Exception('Unable to init cURL handle.');
        }
        $this->setHandle($handle);
        if ($config) {
            if (is_string($config)) {
                $this->setUrl($config);
            } elseif (is_array($config)) {
                foreach ($config as $key => $value) {
                    if ($key && is_string($key)) {
                        $methodName = 'set' . ucfirst($key);
                        if (method_exists($this, $methodName)) {
                            $this->{$methodName}($value);
                        }
                    }
                }
            }
        }
    }

    public function __clone()
    {
        $this->clearFile();
        $handle = curl_copy_handle($this->getHandle());
        if (!$handle) {
            throw new \Exception('Unable to copy cURL handle.');
        }
        $this->setHandle($handle);
    }

    public function __destruct()
    {
        $this->closeFile();
        curl_close($this->getHandle());
    }

    private $_handle = null;

    protected function setHandle($handle)
    {
        $this->_handle = $handle;
        return $this;
    }

    public function getHandle()
    {
        return $this->_handle;
    }

    public function handle()
    {
        return $this->getHandle();
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

    protected function buildQuery()
    {
        $query = $this->getQuery();
        if ($query) {
            if (is_string($query)) {
                return $query;
            } elseif (is_array($query)) {
                $query2 = [];
                foreach ($query as $key => $value) {
                    if (is_int($key) && $value && is_string($value)) {
                        $query2[] = $value;
                    } elseif ($key && is_string($key) && is_scalar($value)) {
                        $query2[] = $key . '=' . urlencode($value);
                    }
                }
                return implode('&', $query2);
            }
        }
        return false;
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
        if ($url && is_string($url)) {
            $url = parse_url($url);
        }
        if ($url && is_array($url)) {
            $this->setScheme(array_key_exists('scheme', $url) ? $url['scheme'] : null);
            $this->setUser(array_key_exists('user', $url) ? $url['user'] : null);
            $this->setPassword(array_key_exists('pass', $url) ? $url['pass'] : null);
            $this->setHost(array_key_exists('host', $url) ? $url['host'] : null);
            $this->setPort(array_key_exists('port', $url) ? (int)$url['port'] : null);
            $this->setPath(array_key_exists('path', $url) ? $url['path'] : null);
            $this->setQuery(array_key_exists('query', $url) ? $url['query'] : null);
            $this->setFragment(array_key_exists('fragment', $url) ? $url['fragment'] : null);
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

    protected function buildUrl()
    {
        $scheme = $this->getScheme();
        $host = $this->getHost();
        if ($scheme && is_string($scheme) && $host && is_string($host)) {
            $url = $scheme . '://' . $host;
            $path = $this->getPath();
            if ($path && is_string($path)) {
                $url .= $path;
            }
            $query = $this->buildQuery();
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

    protected function buildPostFields()
    {
        $postFields = $this->getPostFields();
        if ($postFields) {
            if (is_string($postFields)) {
                return $postFields;
            } elseif (is_array($postFields)) {
                $multipartFormData = false;
                $postFields2 = [];
                foreach ($postFields as $key => $value) {
                    if (is_int($key) && $value && is_string($value)) {
                        $postFields2[] = $value;
                    } elseif ($key && is_string($key) && is_scalar($value)) {
                        if (is_string($value) && (strlen($value) > 1) && (substr($value, 0, 1) == '@') && file_exists(substr($value, 1))) {
                            $multipartFormData = true;
                            break;
                        }
                        $postFields2[] = $key . '=' . urlencode($value);
                    }
                }
                return $multipartFormData ? $postFields : implode('&', $postFields2);
            }
        }
        return false;
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

    protected function buildCookie()
    {
        $cookie = $this->getCookie();
        if ($cookie) {
            if (is_string($cookie)) {
                return $cookie;
            } elseif (is_array($cookie)) {
                $cookie2 = [];
                foreach ($cookie as $key => $value) {
                    if (is_int($key) && $value && is_string($value)) {
                        $cookie2[] = $value;
                    } elseif ($key && is_string($key) && is_scalar($value)) {
                        $cookie2[] = $key . '=' . urlencode($value);
                    }
                }
                return implode('; ', $cookie2);
            }
        }
        return false;
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

    protected function buildReferer()
    {
        $scheme = $this->getScheme();
        $host = $this->getHost();
        if ($scheme && is_string($scheme) && $host && is_string($host)) {
            return $scheme . '://' . $host;
        }
        return false;
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

    protected function buildHttpHeader()
    {
        $httpHeader = $this->getHttpHeader();
        if ($httpHeader) {
            if (is_string($httpHeader)) {
                return preg_split('~[\r\n]+~', $httpHeader, -1, PREG_SPLIT_NO_EMPTY);
            } elseif (is_array($httpHeader)) {
                $httpHeader2 = [];
                foreach ($httpHeader as $key => $value) {
                    if (is_int($key) && $value && is_string($value)) {
                        $httpHeader2[] = $value;
                    } elseif ($key && is_string($key) && is_scalar($value)) {
                        $httpHeader2[] = $key . ': ' . $value;
                    }
                }
                return $httpHeader2;
            }
        }
        return false;
    }

    private $_maxRedirs = null;

    public function setMaxRedirs($maxRedirs)
    {
        $this->_maxRedirs = $maxRedirs;
        return $this;
    }

    public function getMaxRedirs()
    {
        return $this->_maxRedirs;
    }

    public function maxRedirs($maxRedirs = null)
    {
        if (!is_null($maxRedirs)) {
            return $this->setMaxRedirs($maxRedirs);
        } else {
            return $this->getMaxRedirs();
        }
    }

    private $_connectTimeout = null;

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

    private $_isTempFilename = null;

    public function setIsTempFilename($isTempFilename)
    {
        $this->_isTempFilename = $isTempFilename;
        return $this;
    }

    public function getIsTempFilename()
    {
        return $this->_isTempFilename;
    }

    public function isTempFilename($isTempFilename = null)
    {
        if (!is_null($isTempFilename)) {
            return $this->setIsTempFilename($isTempFilename);
        } else {
            return $this->getIsTempFilename();
        }
    }

    private $_filename = null;

    public function setFilename($filename)
    {
        $this->_filename = $filename;
        return $this;
    }

    public function getFilename()
    {
        return $this->_filename;
    }

    public function filename($filename = null)
    {
        if (!is_null($filename)) {
            return $this->setFilename($filename);
        } else {
            return $this->getFilename();
        }
    }

    private $_isTempFile = null;

    public function setIsTempFile($isTempFile)
    {
        $this->_isTempFile = $isTempFile;
        return $this;
    }

    public function getIsTempFile()
    {
        return $this->_isTempFile;
    }

    public function isTempFile($isTempFile = null)
    {
        if (!is_null($isTempFile)) {
            return $this->setIsTempFile($isTempFile);
        } else {
            return $this->getIsTempFile();
        }
    }

    private $_file = null;

    public function setFile($file)
    {
        $this->_file = $file;
        return $this;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function file($file = null)
    {
        if (!is_null($file)) {
            return $this->setFile($file);
        } else {
            return $this->getFile();
        }
    }

    protected function openFile()
    {
        $this->closeFile();
        if ($this->getIsTempFilename()) {
            $dir = sys_get_temp_dir();
            $filename = tempnam($dir, uniqid(time()));
            if (!$filename) {
                throw new \Exception('Unable to create temporary file in "' . $dir . '".');
            }
            $this->setFilename($filename);
        } else {
            $filename = $this->getFilename();
        }
        if ($filename && is_string($filename)) {
            $file = fopen($filename, 'w');
            if (!$file) {
                throw new \Exception('Unable to open file "' . $filename . '".');
            }
            $this->setIsTempFile(true);
            $this->setFile($file);
        } elseif ($this->getIsTempFile()) {
            $file = tmpfile();
            $this->setFile($file);
        } else {
            $file = $this->getFile();
        }
        return $file;
    }

    protected function clearFile()
    {
        if ($this->getIsTempFile()) {
            $this->setFile(null);
            $filename = $this->getFilename();
            if ($filename && is_string($filename)) {
                $this->setIsTempFile(false);
                if ($this->getIsTempFilename()) {
                    $this->setFilename(null);
                }
            }
        }
        return $this;
    }

    protected function closeFile()
    {
        if ($this->getIsTempFile()) {
            $file = $this->getFile();
            $this->setFile(null);
            if ($file && is_resource($file)) {
                fclose($file);
            }
            $filename = $this->getFilename();
            if ($filename && is_string($filename)) {
                $this->setIsTempFile(false);
                if ($this->getIsTempFilename()) {
                    $this->setFilename(null);
                    if (file_exists($filename)) {
                        unlink($filename);
                    }
                }
            }
        }
        return $this;
    }

    private $_proxy = null;

    public function setProxy($proxy)
    {
        $this->_proxy = $proxy;
        return $this;
    }

    public function getProxy()
    {
        return $this->_proxy;
    }

    public function proxy($proxy = null)
    {
        if (!is_null($proxy)) {
            return $this->setProxy($proxy);
        } else {
            return $this->getProxy();
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
        if ($proxyUrl && is_string($proxyUrl)) {
            $proxyUrl = parse_url($proxyUrl);
        }
        if ($proxyUrl && is_array($proxyUrl)) {
            if (array_key_exists('scheme', $proxyUrl)) {
                if (strncasecmp($proxyUrl['scheme'], 'http', 4) == 0) {
                    $this->setProxyType(self::PROXY_TYPE_HTTP);
                } elseif (strncasecmp($proxyUrl['scheme'], 'sock', 4) == 0) {
                    $this->setProxyType(self::PROXY_TYPE_SOCKS5);
                } else {
                    $this->setProxyType(null);
                }
            } else {
                $this->setProxyType(null);
            }
            $this->setProxyUser(array_key_exists('user', $proxyUrl) ? $proxyUrl['user'] : null);
            $this->setProxyPassword(array_key_exists('pass', $proxyUrl) ? $proxyUrl['pass'] : null);
            $this->setProxyHost(array_key_exists('host', $proxyUrl) ? $proxyUrl['host'] : null);
            $this->setProxyPort(array_key_exists('port', $proxyUrl) ? (int)$proxyUrl['port'] : null);
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

    private $_options = null;

    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function options($options = null)
    {
        if (!is_null($options)) {
            return $this->setOptions($options);
        } else {
            return $this->getOptions();
        }
    }

    protected function buildOptions()
    {
        $options = $this->getOptions();
        if (!is_array($options)) {
            $options = [];
        }
        $options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS | CURLPROTO_FTP;
        $options[CURLINFO_HEADER_OUT] = true;
        // url
        $url = $this->buildUrl();
        if (!$url) {
            $url = $this->getUrl();
        }
        if ($url && is_string($url)) {
            $options[CURLOPT_URL] = $url;
        } else {
            $options[CURLOPT_URL] = null;
        }
        // port
        $port = $this->getPort();
        if ($port && is_int($port)) {
            $options[CURLOPT_PORT] = $port;
        } else {
            $options[CURLOPT_PORT] = null;
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
        } else {
            $options[CURLOPT_USERPWD] = null;
        }
        // post fields
        $postFields = $this->buildPostFields();
        if ($postFields && (is_string($postFields) || is_array($postFields))) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $postFields;
        } else {
            $options[CURLOPT_POSTFIELDS] = null;
            $options[CURLOPT_POST] = false;
        }
        // cookie
        $cookie = $this->buildCookie();
        if ($cookie && is_string($cookie)) {
            $options[CURLOPT_COOKIE] = $cookie;
        } else {
            $options[CURLOPT_COOKIE] = null;
        }
        // referer
        $referer = $this->getReferer();
        if (!$referer) {
            $referer = $this->buildReferer();
        }
        if ($referer && is_string($referer)) {
            $options[CURLOPT_REFERER] = $referer;
        } else {
            $options[CURLOPT_REFERER] = null;
        }
        // user agent
        $userAgent = $this->getUserAgent();
        if ($userAgent && is_string($userAgent)) {
            $options[CURLOPT_USERAGENT] = $userAgent;
        } else {
            $options[CURLOPT_USERAGENT] = null;
        }
        // http header
        $httpHeader = $this->buildHttpHeader();
        if ($httpHeader && is_array($httpHeader)) {
            $options[CURLOPT_HTTPHEADER] = $httpHeader;
        } else {
            $options[CURLOPT_HTTPHEADER] = [];
        }
        // max redirs
        $maxRedirs = $this->getMaxRedirs();
        if ($maxRedirs && is_int($maxRedirs)) {
            $options[CURLOPT_FOLLOWLOCATION] = true;
            $options[CURLOPT_MAXREDIRS] = $maxRedirs;
        } else {
            $options[CURLOPT_MAXREDIRS] = null;
            $options[CURLOPT_FOLLOWLOCATION] = false;
        }
        // connect timeout
        $connectTimeout = $this->getConnectTimeout();
        if ($connectTimeout && is_int($connectTimeout)) {
            $options[CURLOPT_CONNECTTIMEOUT] = $connectTimeout;
        } else {
            $options[CURLOPT_CONNECTTIMEOUT] = null;
        }
        // timeout
        $timeout = $this->getTimeout();
        if ($timeout && is_int($timeout)) {
            $options[CURLOPT_TIMEOUT] = $timeout;
        } else {
            $options[CURLOPT_TIMEOUT] = null;
        }
        // file
        $file = $this->openFile();
        if ($file && is_resource($file)) {
            $options[CURLOPT_RETURNTRANSFER] = false;
            $options[CURLOPT_FILE] = $file;
        } else {
            $options[CURLOPT_FILE] = STDOUT;
            $options[CURLOPT_RETURNTRANSFER] = true;
        }
        // proxy
        $proxy = $this->getProxy();
        if ($proxy && is_object($proxy) && method_exists($proxy, 'getUrl')) {
            $this->setProxyUrl($proxy->getUrl());
        }
        // proxy type
        $proxyType = $this->getProxyType();
        if ($proxyType && is_int($proxyType)) {
            $options[CURLOPT_PROXYTYPE] = $proxyType;
        } else {
            $options[CURLOPT_PROXYTYPE] = null;
        }
        // proxy host
        $proxyHost = $this->getProxyHost();
        if ($proxyHost && is_string($proxyHost)) {
            $options[CURLOPT_PROXY] = $proxyHost;
        } else {
            $options[CURLOPT_PROXY] = null;
        }
        // proxy port
        $proxyPort = $this->getProxyPort();
        if ($proxyPort && is_int($proxyPort)) {
            $options[CURLOPT_PROXYPORT] = $proxyPort;
        } else {
            $options[CURLOPT_PROXYPORT] = null;
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
        } else {
            $options[CURLOPT_PROXYUSERPWD] = null;
        }
        return $options;
    }

    public function getOptionsDump()
    {
        $options = [];
        $constants = get_defined_constants(true)['curl'];
        foreach ($this->buildOptions() as $key => $value) {
            $options[array_search($key, $constants)] = $value;
        }
        return $options;
    }

    public function optionsDump()
    {
        return $this->getOptionsDump();
    }

    private $_result = null;

    protected function setResult($result)
    {
        $this->_result = $result;
        return $this;
    }

    public function getResult()
    {
        return $this->_result;
    }

    public function result()
    {
        return $this->getResult();
    }

    private $_errno = null;

    protected function setErrno($errno)
    {
        $this->_errno = $errno;
        return $this;
    }

    public function getErrno()
    {
        return $this->_errno;
    }

    public function errno()
    {
        return $this->getErrno();
    }

    private $_error = null;

    protected function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    public function getError()
    {
        return $this->_error;
    }

    public function error()
    {
        return $this->getError();
    }

    private $_info = null;

    protected function setInfo($info)
    {
        if ($info && is_array($info) && array_key_exists('http_code', $info) && array_key_exists('download_content_length', $info) && ($info['http_code'] == 200) && !$info['download_content_length']) {
            $info['http_code'] = 204; // No Content
        }
        $this->_info = $info;
        return $this;
    }

    public function getInfo($key = null)
    {
        if ($key && is_string($key)) {
            return ($this->_info && is_array($this->_info) && array_key_exists($key, $this->_info)) ? $this->_info[$key] : null;
        } else {
            return $this->_info;
        }
    }

    public function info($key = null)
    {
        return $this->getInfo($key);
    }

    public function getConnectTime()
    {
        return $this->getInfo('connect_time');
    }

    public function connectTime()
    {
        return $this->getConnectTime();
    }

    public function getTotalTime()
    {
        return $this->getInfo('total_time');
    }

    public function totalTime()
    {
        return $this->getTotalTime();
    }

    public function getHttpCode()
    {
        return $this->getInfo('http_code');
    }

    public function httpCode()
    {
        return $this->getHttpCode();
    }

    public function getContentType()
    {
        return $this->getInfo('content_type');
    }

    public function contentType()
    {
        return $this->getContentType();
    }

    public function getContentLength()
    {
        return $this->getInfo('download_content_length');
    }

    public function contentLength()
    {
        return $this->getContentLength();
    }

    private $_beforeExecute = null;

    public function setBeforeExecute($beforeExecute)
    {
        $this->_beforeExecute = $beforeExecute;
        return $this;
    }

    public function getBeforeExecute()
    {
        return $this->_beforeExecute;
    }

    public function beforeExecute($beforeExecute = null)
    {
        if (!is_null($beforeExecute)) {
            return $this->setBeforeExecute($beforeExecute);
        } else {
            return $this->getBeforeExecute();
        }
    }

    protected function invokeBeforeExecute()
    {
        $this->setResult(null);
        $this->setErrno(null);
        $this->setError(null);
        $this->setInfo(null);
        $beforeExecute = $this->getBeforeExecute();
        if ($beforeExecute && is_callable($beforeExecute)) {
            if (!call_user_func($beforeExecute, $this)) {
                return false;
            }
        }
        if (!curl_setopt_array($this->getHandle(), $this->buildOptions())) {
            throw new \Exception('Unable to set cURL options.');
        }
        return true;
    }

    private $_afterExecute = null;

    public function setAfterExecute($afterExecute)
    {
        $this->_afterExecute = $afterExecute;
        return $this;
    }

    public function getAfterExecute()
    {
        return $this->_afterExecute;
    }

    public function afterExecute($afterExecute = null)
    {
        if (!is_null($afterExecute)) {
            return $this->setAfterExecute($afterExecute);
        } else {
            return $this->getAfterExecute();
        }
    }

    protected function invokeAfterExecute()
    {
        $this->closeFile();
        $handle = $this->getHandle();
        $this->setResult(curl_multi_getcontent($handle));
        $this->setErrno(curl_errno($handle));
        $this->setError(curl_error($handle));
        $this->setInfo(curl_getinfo($handle));
        $afterExecute = $this->getAfterExecute();
        if ($afterExecute && is_callable($afterExecute)) {
            if (!call_user_func($afterExecute, $this, $this->getResult(), $this->getHttpCode())) {
                return false;
            }
        }
        return true;
    }

    public function executeOnce()
    {
        if ($this->invokeBeforeExecute()) {
            $result = curl_exec($this->getHandle());
            if ($this->invokeAfterExecute()) {
                return $result;
            }
        }
        return false;
    }

    private $_retryCount = null;

    protected function setRetryCount($retryCount)
    {
        $this->_retryCount = $retryCount;
        return $this;
    }

    public function getRetryCount()
    {
        return $this->_retryCount;
    }

    public function retryCount()
    {
        return $this->getRetryCount();
    }

    private $_maxRetries = null;

    public function setMaxRetries($maxRetries)
    {
        $this->_maxRetries = $maxRetries;
        return $this;
    }

    public function getMaxRetries()
    {
        return $this->_maxRetries;
    }

    public function maxRetries($maxRetries = null)
    {
        if (!is_null($maxRetries)) {
            return $this->setMaxRetries($maxRetries);
        } else {
            return $this->getMaxRetries();
        }
    }

    private $_retryDelay = null;

    public function setRetryDelay($retryDelay)
    {
        $this->_retryDelay = $retryDelay;
        return $this;
    }

    public function getRetryDelay()
    {
        return $this->_retryDelay;
    }

    public function retryDelay($retryDelay = null)
    {
        if (!is_null($retryDelay)) {
            return $this->setRetryDelay($retryDelay);
        } else {
            return $this->getRetryDelay();
        }
    }

    public function execute()
    {
        $retryCount = 0;
        $this->setRetryCount($retryCount);
        $result = $this->executeOnce();
        $maxRetries = $this->getMaxRetries();
        while (!$result && !$this->getHttpCode() && $maxRetries && is_int($maxRetries) && (++ $retryCount <= $maxRetries)) {
            $retryDelay = $this->getRetryDelay();
            if ($retryDelay && is_int($retryDelay)) {
                sleep($retryDelay);
            }
            $this->setRetryCount($retryCount);
            $result = $this->executeOnce();
            $maxRetries = $this->getMaxRetries();
        }
        return $result;
    }
}
