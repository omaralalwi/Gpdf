<?php

namespace Omaralalwi\Gpdf\Traits;

trait HasFile
{
    public function getRootPath()
    {
        // TODO: update this before publish updates, change the level to 6 , depend on package path in vendor
        // get root app path, depend on package path in vendor
        // $rootPath = dirname(__DIR__, 6);
        return dirname(__DIR__, 3).'/Gpdf-Laravel-Demo/';
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
