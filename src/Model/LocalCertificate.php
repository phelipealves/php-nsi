<?php

namespace NSI\Model;

class LocalCertificate
{
    private $certPath;
    private $certPassphrase;

    /**
     * LocalCertificate constructor.
     *
     * @param $certPath
     * @param $certPassphrase
     */
    public function __construct($certPath, $certPassphrase)
    {
        $this->certPath = $certPath;
        $this->certPassphrase = $certPassphrase;
    }

    /**
     * @return mixed
     */
    public function getCertPath()
    {
        return $this->certPath;
    }

    /**
     * @return mixed
     */
    public function getCertPassphrase()
    {
        return $this->certPassphrase;
    }
}
