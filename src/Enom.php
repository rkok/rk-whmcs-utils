<?php

namespace RKWhmcsUtils;

use Mtownsend\XmlToArray\XmlToArray;
use RKWhmcsUtils\Config;

class Enom
{
    private $baseUrl = '';
    /**
     * @var string|null
     */
    private $proxy;

    public static function buildInstance()
    {
        $config = Config::getInstance();
        return new self($config->enomUser, $config->enomKey, false, $config->proxy);
    }

    /**
     * @param string $username
     * @param string $password
     * @param boolean $testMode
     */
    public function __construct($username, $password, $testMode = false, $proxy = null)
    {
        $mode = $testMode ? 'test' : 'live';
        $this->baseUrl .= "https://reseller.enom.com/interface.asp?uid=$username&pw=$password&mode=$mode&response=xml&responsetype=xml";
        $this->proxy = $proxy;
    }

    /**
     * @param string $cmd
     * @param array $args
     * @return array
     */
    public function call($cmd, $args = [])
    {
        $path = "&command=$cmd";
        foreach ($args as $key => $value) {
            $path .= '&' . urlencode($key) . '=' . urlencode($value);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }

        $res = curl_exec($ch);

        if (($code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) !== 200) {
            throw new \Exception("Unexpected response code $code for $cmd");
        }

        curl_close($ch);

        $arrRes = XmlToArray::convert($res);

        if (isset($arrRes['errors']) && !empty($arrRes['errors'])) {
            throw new \Exception(implode(', ', $arrRes['errors']));
        }

        return $arrRes[$cmd];
    }
}

