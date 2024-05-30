<?php

namespace Omaralalwi\Gpdf;

use Omaralalwi\Gpdf\Enums\GpdfStorageDrivers;
use Omaralalwi\Gpdf\Factories\DompdfFactory;
use Omaralalwi\Gpdf\Builders\PdfBuilder;
use Dompdf\Dompdf;
use Omaralalwi\Gpdf\Services\S3Service;
use Omaralalwi\Gpdf\Services\LocalFileService;
use Omaralalwi\Gpdf\Factories\StorageServiceFactory;

class Gpdf
{
    protected GpdfConfig $gpdfConfig;

    public function __construct(GpdfConfig $gpdfConfig)
    {
        $this->gpdfConfig = $gpdfConfig;
    }

    public function generate(string $content): string
    {
        return $this->getPdfBuilder()->build($content);
    }

    public function generateWithStream(string $content, ?string $fileName = null, bool $attachment = false): void
    {
        $this->getPdfBuilder()->buildAndStream($content, DompdfFactory::generateFileName($fileName), $attachment);
    }

    /**
     * Generate and save the PDF to a specified file path.
     *
     * @param string $content
     * @param string $filePath
     * @param string|null $fileName
     */
    public function generateWithStore(string $content, string $filePath, ?string $fileName = null)
    {
        $storageService = $this->getStorageService(GpdfStorageDrivers::LOCAL, $this->gpdfConfig);
        return $this->getPdfBuilder()->buildAndStore($storageService, $content, $filePath, DompdfFactory::generateFileName($fileName));
    }

    public function generateWithStoreToS3(string $content, ?string $bucketName = null, ?string $fileName = null)
    {
        $storageService = $this->getStorageService(GpdfStorageDrivers::S3, $this->gpdfConfig);
        return $this->getPdfBuilder()->buildAndStore($storageService, $content, $bucketName, DompdfFactory::generateFileName($fileName));
    }

    /**
     * Get the appropriate storage service.
     *
     * @param string $driver
     * @param GpdfConfig $gpdfConfig
     * @return S3Service|LocalFileService
     */
    public function getStorageService(string $driver, GpdfConfig $gpdfConfig): S3Service|LocalFileService
    {
        return (new StorageServiceFactory())->getStorageService($driver, $gpdfConfig);
    }

    public function getConfig(): GpdfConfig
    {
        return $this->gpdfConfig;
    }

    /**
     * Get an instance of the PdfBuilder.
     *
     * @return PdfBuilder
     */
    private function getPdfBuilder(): PdfBuilder
    {
        $dompdf = DompdfFactory::create($this->gpdfConfig);
        return new PdfBuilder($dompdf);
    }
}
