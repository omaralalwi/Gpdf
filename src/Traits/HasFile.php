<?php

namespace Omaralalwi\Gpdf\Traits;

trait HasFile
{
    public function getRootPath()
    {
        // get root app path, depend on package path in vendor
        return dirname(__DIR__, 5);
    }

    public function getRealDirectoryPath($path)
    {
        $fullDirPath = rtrim($this->getRootPath(), '/') . '/' . ltrim($path, '/');

        if($this->ensurePathExists($fullDirPath)){
            return $fullDirPath;
        }
    }

    protected function getFullFilePath(string $path, string $fileName): string
    {
        return rtrim($path, '/') . '/' . $fileName;
    }
}
