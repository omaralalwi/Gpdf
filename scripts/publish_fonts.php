<?php

$destinationPath = "/public/vendor/gpdf/fonts";

function copyDirectory($destination)
{    // current path related to user, when run command
    // always must run script in project root directory
    $currentWorkingDir = getcwd();

    $source = $currentWorkingDir.'/vendor/omaralalwi/gpdf/assets/fonts/';

    if (!is_dir($source)) {
        echo "The source directory does not exist.\n" . $source;
        return false;
    }

    $destFolderInProjectRoot = $currentWorkingDir.'/'.$destination;

    if (!is_dir($destFolderInProjectRoot)) {
        mkdir($destFolderInProjectRoot, 0755, true);
    }

    $directory = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

    foreach ($iterator as $file) {
        $destPath = $destFolderInProjectRoot. DIRECTORY_SEPARATOR. $iterator->getSubPathName();

        if ($file->isDir()) {
            mkdir($destPath);
        } else {
            copy($file, $destPath);
        }
    }

    return true;
}

if (copyDirectory($destinationPath)) {
    echo "Directory published successfully to $destinationPath\n";
} else {
    echo "Failed to publish the fonts directory.\n";
}
