<?php

namespace MGModule\ResellersCenter\core;

class MailConfiguration
{
    const STARTTLS_TYPE = 'TLS';
    const SMTPS_TYPE = 'SSL';
    const NONE_TYPE = 'none';

    const STARTTLS_VALUE = 'tls';
    const SMTPS_VALUE = 'ssl';

    const SECURE_TYPES = [0=>self::NONE_TYPE, 1=>self::STARTTLS_TYPE, 2=>self::SMTPS_TYPE];
    const SECURE_VALUES = [0=>'', 1=>self::STARTTLS_VALUE, 2=>self::SMTPS_VALUE];

    protected $hostname;
    protected $username;
    protected $password;
    protected $port;
    protected $secure;

    public function getHostname()
    {
        return $this->hostname;
    }

    public function setHostname($hostname): void
    {
        $this->hostname = $hostname;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username): void
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password): void
    {
        $this->password = $password;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port): void
    {
        $this->port = $port;
    }

    public function getSecure()
    {
        return $this->secure;
    }

    public function setSecure($secure): void
    {
        $this->secure = self::SECURE_VALUES[(int)$secure];
    }

}