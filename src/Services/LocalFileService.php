<?php

namespace Omaralalwi\Gpdf\Services;

use Exception;
use Omaralalwi\Gpdf\Traits\HasPdfHeaders;
use Omaralalwi\Gpdf\Traits\{HasFile, HasGpdfLog};
use Omaralalwi\Gpdf\Utilities\Helpers;

class LocalFileService
{
    use HasPdfHeaders, HasFile, HasGpdfLog;

    /**
     * Store a file in the local file system.
     *
     * @param string $pdfFile The content of the file.
     * @param string $path The path to store the file under.
     * @param string $fileName The name of the file.
     * @return object|array An object representing the stored file.
     * @throws Exception If the file could not be stored.
     */
    public function store(string $pdfFile, string $path, string $fileName): array
    {
        // Define the base directory
        $baseDir = __DIR__; // or any other base directory you need

        // TODO: update this before publish updates, change the level to 6 , depend on package path in vendor
//        $rootPath = dirname(__DIR__, 6);
        $rootPath = dirname(__DIR__, 3).'/Gpdf-Laravel-Demo/';
//        $rootPath = dirname(__DIR__, 3).'/Gpdf-Native-PHP-Demo/';

//        $this->logUpload('this is $rootPath', $rootPath);

        $fullDirPath = $this->getRealDirectoryPath($path);
        $fullFilePath = $this->getFullFilePath($fullDirPath, $fileName);

        // Remove the file if it already exists
        if (file_exists($fullFilePath)) {
            unlink($fullFilePath);
        }

        // Write the PDF file content to the specified path
        if (file_put_contents($fullFilePath, $pdfFile) === false) {
            throw new Exception("Failed to write file to $fullFilePath");
        }

        // Open the file in read mode
        $fileObject = fopen($fullFilePath, 'r');
        if ($fileObject === false) {
            throw new Exception("Failed to open file $fullFilePath");
        }

        try {
            // Read the file content
            $fileContent = file_get_contents($fullFilePath);
            if ($fileContent === false) {
                throw new Exception("Failed to read file $fullFilePath");
            }
        } finally {
            // Close the file resource
            fclose($fileObject);
        }

        // Build and return the response array
        return $this->buildResponseArray($fileObject, $fullDirPath, $path, $fileName);
    }

    /**
     * Ensure that the directory exists and is writable.
     *
     * @param string $directory The directory path.
     * @throws Exception If the directory does not exist and cannot be created, or is not writable.
     */
    protected function ensurePathExists(string $directory): mixed
    {
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new Exception("Directory `$directory` was not found and could not be created.");
        }

        if (!is_writable($directory)) {
            throw new Exception("Directory `$directory` is not writable.");
        }

        return true;
    }

    /**
     * Build a response array representing the stored file.
     *
     * @param string $path The path to the directory.
     * @param string $fileName The name of the file.
     * @return array An array representing the stored file.
     */
    protected function buildResponseArray($fileObject, string $fullFilePath, string $path, string $fileName): array
    {
        return [
            'fileObject' => $fileObject,
            'name' => (string) $fileName,
            'path' => (string) $path,
            'fullPath' => (string) $fullFilePath,
            'ObjectURL' => null,
        ];
    }

    public function getFileUrl($generatedFile): string
    {
        $fullFilePath = $this->getFullFilePath($generatedFile['fullPath'], $generatedFile['name']);

        if (!file_exists($fullFilePath)) {
            throw new Exception('File not found: ' . $fullFilePath, 404);
        }

        $baseUrl = rtrim(Helpers::getBaseAppUrl(),'/');
        $cleanPath = $this->removePublicPrefix($generatedFile['path']);
        return $baseUrl . '/' . trim($cleanPath, '/') . '/' . $generatedFile['name'];
    }

    /*
     * we need to remove public world to ger clean file url .
     */
    public function removePublicPrefix(string $path): string {
        $path = ltrim($path, '/');
        $directories = explode('/', $path);
        if ($directories[0] === 'public') {
            array_shift($directories);
        }
        return implode('/', $directories);
    }

    /**
     * Stream a PDF from a local file URL.
     *
     * @param string $fileUrl The URL of the file to stream.
     * @return bool True if the file was streamed successfully, otherwise false.
     * @throws Exception If the file does not exist.
     */
    public static function streamFromUrl(string $fileUrl)
    {
        $logFilePath = realpath(__DIR__ . '/../../logs/upload_log.txt');
        file_put_contents($logFilePath, 'fileUrl' . PHP_EOL . $fileUrl . PHP_EOL, FILE_APPEND);

        // Check if the file exists at the URL
        $headers = @get_headers($fileUrl);
        if ($headers && strpos($headers[0], '200') === false) {
            throw new Exception('File not found: ' . $fileUrl, 404);
        }

        // Set PDF headers
        self::setPdfHeaders($fileUrl);

        // TODO: make this it optional , let user decide if check about ssl or not
        $contextOptions = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];
        $context = stream_context_create($contextOptions);

        // Read and output the file content
        $fileContent = file_get_contents($fileUrl, false, $context);
        if ($fileContent === false) {
            throw new Exception('Failed to read file: ' . $fileUrl);
        }

        echo $fileContent;
        return true;
    }

//    public static function streamFromUrl(string $fileUrl)
//    {  // no need to check about file existence in this url, we checked when generate this url
//        self::setPdfHeaders($fileUrl);
//        return readfile($fileUrl) !== false;
//    }

}
