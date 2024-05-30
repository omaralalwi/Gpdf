<?php

namespace Omaralalwi\Gpdf\Utilities;

class Helpers
{
    public static function getBaseAppUrl(): string
    {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $scheme . '://' . $host;

        if (substr($baseUrl, -1) !== '/') {
            $baseUrl .= '/';
        }
        return $baseUrl;
    }
}
