<?php

namespace Forlabs\DockerIntegration;

use Forlabs\DockerIntegration\Exception\MissingEnvException;
use Forlabs\DockerIntegration\Exception\MissingSecretException;

class Loader {

    protected $secretsDirectory;

    /**
     * Loader constructor.
     * @param string $secretsDirectory
     */
    public function __construct($secretsDirectory = '/run/secrets')
    {
        $this->secretsDirectory = $secretsDirectory;
    }

    public function secretDirectoryExists()
    {
        return \is_dir($this->secretsDirectory);
    }

    /**
     * Get Docker secret value
     * @param string $secretName
     * @param mixed $default
     * @return string
     */
    public function getSecret($secretName, $default=null)
    {
        $secretPath = $this->secretsDirectory . '/' . $secretName;

        if (!\file_exists($secretPath)) {
            if ($default === null) {
                throw new MissingSecretException();
            } else {
                return $default;
            }
        }

        return \rtrim(\file_get_contents($secretPath));
    }

    /**
     * Get (docker) env var value
     *
     * @param string $envName
     * @param mixed $default
     * @return string
     */
    public function getEnv($envName, $default=null)
    {
        $value = getenv($envName);
        if ($value === false) {
            if ($default === null) {
                throw new MissingEnvException();
            }
            return $default;
        }
        return $value;
    }

    /**
     * @param string $secretName
     * @param mixed $default
     * @return mixed|string
     */
    public function getSecretOrEnv($secretName, $default=null)
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

        if ($default === null) {
            throw new \RuntimeException();
        }

        return $default;
    }
}
