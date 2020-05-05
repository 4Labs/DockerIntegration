<?php

namespace Forlabs\DockerIntegration;

use Forlabs\DockerIntegration\Exception\MissingEnvException;
use Forlabs\DockerIntegration\Exception\MissingSecretException;
use Forlabs\DockerIntegration\Exception\MissingSecretsDirectoryException;

class Loader {

    protected $secretsDirectory;

    /**
     * Loader constructor.
     * @param string $secretsDirectory
     */
    public function __construct($secretsDirectory = '/var/run/secrets')
    {
        $this->secretsDirectory = $secretsDirectory;

        if (!is_dir($secretsDirectory)) {
            throw new MissingSecretsDirectoryException();
        }
    }

    /**
     * Get Docker secret value
     * @param $secretName
     * @return string
     */
    public function getSecret($secretName)
    {
        $secretPath = $this->secretsDirectory . '/' . $secretName;

        if (!file_exists($secretPath)) {
            throw new MissingSecretException();
        }

        return rtrim(file_get_contents($secretPath));
    }

    /**
     * Get (docker) env var value
     *
     * @param $envName
     * @return string
     */
    public function getEnv($envName)
    {
        if (!isset($_ENV[$envName])) {
            throw new MissingEnvException();
        }

        return $_ENV[$envName];
    }

    /**
     * @param $secretName
     * @return mixed|string
     */
    public function getSecretOrEnv($secretName)
    {
        try {
            return $this->getSecret($secretName);
        } catch (MissingSecretException $e) {
            // Try next
            ;
        }

        try {
            return $this->getEnv($secretName);
        } catch (MissingEnvException $e) {
            // Try next
            ;
        }

        throw new \RuntimeException();
    }
}
