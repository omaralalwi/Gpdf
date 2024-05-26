<?php

$destinationPath = "/config";

function copyDirectory($destination)
{
    $currentWorkingDir = getcwd();

    $source = $currentWorkingDir.'/vendor/omaralalwi/gpdf/config/';
    $destFolderInProjectRoot = $currentWorkingDir.'/'.$destination;

    if (!is_dir($destFolderInProjectRoot)) {
        mkdir($destFolderInProjectRoot, 0755, true);
    }

    $directory = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

    foreach ($iterator as $file) {
        $destPath = $destFolderInProjectRoot. DIRECTORY_SEPARATOR. $iterator->getSubPathName();

        if ($file->isDir()) {
            if (!is_dir($destPath)) {
                mkdir($destPath);
            }
        } else {
            // Check if the file already exists in the destination directory
            if (!file_exists($destPath)) {
                copy($file, $destPath);
            } else {
                echo "the config file already published previously to config directory .\n";
                exit();
            }
        }
    }

    return true;
}

if (copyDirectory($destinationPath)) {
    echo "Config file published successfully to $destinationPath directory.\n";
} else {
    echo "Failed to publish config file .\n";
}
