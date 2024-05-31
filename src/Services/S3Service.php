<?php

namespace Omaralalwi\Gpdf\Services;

use Omaralalwi\Gpdf\Clients\S3Client;
use Aws\Exception\AwsException;
use Omaralalwi\Gpdf\Traits\HasPdfHeaders;
use Omaralalwi\Gpdf\Traits\{HasFile, HasGpdfLog};

class S3Service
{
    use HasPdfHeaders, HasFile, HasGpdfLog;

    protected $s3Client;

    public function __construct(S3Client $s3Client)
    {
        $this->s3Client = $s3Client;
    }

    public function store($pdfFile,string $path , string $fileName): mixed
    {
        try {
            $fullFilePath = $this->getFullFilePath($path, $fileName);
            $bucket = $this->s3Client->getBucket();
            return $this->s3Client->initilizeClient()->putObject([
                'Bucket' => $bucket,
                'Key'    => $fullFilePath,
                'Body'   => $pdfFile,
            ]);
        } catch (AwsException $e) {
            throw new \Exception('Error uploading PDF to S3: ' . $e->getMessage());
        }
    }

    /*
     * check if bucket exists
     */
    public function ensurePathExists($bucketName)
    {
        try {
            return $this->s3Client->initilizeClient()->doesBucketExist($bucketName);
        } catch (AwsException $e) {
            throw new \Exception('Bucket Does Not Exists: ' . $e->getMessage());
        }
    }

    public function getFileUrl($generatedFile): string
    {
        try {
            if(is_object($generatedFile)) {
                return $generatedFile['ObjectURL'];
            }
        } catch (AwsException $e) {
            throw new \Exception('Error the provider file format is Not True S3 file Structure: ' . $e->getMessage());
        }
    }

    /**
     * Stream a PDF from an S3 file URL.
     *
     * @param string $fileUrl
     * @return bool
     */
    public static function streamFromUrl(string $fileUrl, bool $verifySsl=true): string
    {  // TODO: add way to check if file exists in S3
        self::setPdfHeaders($fileUrl);
        return readfile($fileUrl) !== false;
    }
}
