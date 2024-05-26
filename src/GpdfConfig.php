<?php

namespace Omaralalwi\Gpdf;

class GpdfConfig
{
    protected $config = [];

    /*
     * allow user to pass his options and override defaults in config/config.php file.
    */

    public function __construct(array $config = [])
    {
        $defaultConfig = require __DIR__ . '/../config/gpdf.php'; // this will override by user
        $this->config = array_merge($defaultConfig, $config);
    }

    public function getDefaultConfigFile(): string|null
    {
        return __DIR__ . '/../config/gpdf.php';
    }

    public function get($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function getAll(): array
    {
        return $this->config;
    }
}
