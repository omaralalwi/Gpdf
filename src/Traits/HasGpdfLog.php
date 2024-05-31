<?php

namespace Omaralalwi\Gpdf\Traits;

trait HasGpdfLog
{
    public function logUpload($msg='', $fileUrl=null,  $data=null)
    {
        $logFilePath = realpath( __DIR__ . '/../../logs/upload_log.txt');
        file_put_contents($logFilePath, $msg . PHP_EOL . $fileUrl . PHP_EOL, FILE_APPEND);
    }
}
