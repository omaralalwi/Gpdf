<?php

namespace Omaralalwi\Gpdf;

use Omaralalwi\Gpdf\Enums\GpdfStorageDrivers as Driver;
use Omaralalwi\Gpdf\Factories\DompdfFactory;
use Omaralalwi\Gpdf\Builders\PdfBuilder;
use Dompdf\Dompdf;
use Omaralalwi\Gpdf\Services\S3Service;
use Omaralalwi\Gpdf\Services\LocalFileService;
use Omaralalwi\Gpdf\Factories\StorageServiceFactory;
use Omaralalwi\Gpdf\Enums\{GpdfDefaultSettings as GpdfDefault, GpdfSettingKeys as GpdfSet,
    GpdfStorageDrivers, GpdfDefaultSupportedFonts};

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

    public function generateWithStream(string $content,?string $fileName = null, bool $attachment = false): void
    {
        $this->getPdfBuilder()->buildAndStream($content, DompdfFactory::generateFileName($fileName), $attachment);
    }

    /**
     * Generate and save the PDF to a specified location.
     *
     * Generates a PDF from the given HTML content and saves it to the specified file path or S3 bucket.
     * Optionally, streams the PDF to the client instead of saving it.
     *
     * @param string $content The HTML content to convert to PDF.
     * @param string $destinationPath The file path or S3 bucket name where the PDF should be saved.
     * @param string|null $fileName The name of the file to save the PDF as. If not provided, a default filename will be generated.
     * @param bool $withStream Whether to stream the PDF to the client instead of saving it. Default is false.
     * @param bool $verifySsl Whether to verify SSL certificates when connecting to S3. Default is true.
     * @return array Returns the result of the storage operation.
     */
    public function generateWithStore(string $content, ?string $destinationPath,?string $fileName = null, bool $withStream = false, bool $verifySsl=true): array
    {
        $destinationPath = $destinationPath ?: $this->gpdfConfig->get(GpdfSet::STORAGE_PATH );
        return $this->getPdfBuilder()->buildAndStore($this->getStorageService($this->gpdfConfig), $content, $destinationPath, DompdfFactory::generateFileName($fileName), $withStream, $verifySsl);
    }

    /**
     * Generate and save the PDF to an S3 bucket.
     *
     * A specialized version of generateWithStore that specifically targets saving the PDF to an S3 bucket.
     * It allows for specifying whether to stream the PDF to the client instead of saving it.
     *
     * @param string $content The HTML content to convert to PDF.
     * @param string|null $bucketName The name of the S3 bucket where the PDF should be saved.
     * @param string|null $fileName The name of the file to save the PDF as. If not provided, a default filename will be generated.
     * @param bool $withStream Whether to stream the PDF to the client instead of saving it. Default is false.
     * @param bool $verifySsl Whether to verify SSL certificates when connecting to S3. Default is true.
     * @return array Returns the result of the storage operation.
     */
    public function generateWithStoreToS3(string $content,?string $bucketName = null,?string $fileName = null, bool $withStream = false, bool $verifySsl=true): array
    {
        $bucketName = $bucketName ?: $this->gpdfConfig->get(GpdfSet::AWS_BUCKET);
        return $this->getPdfBuilder()->buildAndStore($this->getStorageService($this->gpdfConfig, Driver::S3), $content, $bucketName, DompdfFactory::generateFileName($fileName), $withStream, $verifySsl);
    }

    /**
     * Get the appropriate storage service.
     *
     * Retrieves the storage service instance based on the specified driver and configuration.
     *
     * @param GpdfConfig $gpdfConfig The configuration object containing settings for the storage service.
     * @param string $driver The storage driver to use ('local' or 's3'). Defaults to 'local'.
     * @return S3Service|LocalFileService An instance of the requested storage service.
     */
    public function getStorageService(GpdfConfig $gpdfConfig, string $driver = Driver::LOCAL): S3Service|LocalFileService
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
     * Creates and returns an instance of the PdfBuilder, configured with the current GpdfConfig.
     *
     * @return PdfBuilder An instance of PdfBuilder ready to generate PDFs.
     */
    private function getPdfBuilder(): PdfBuilder
    {
        $dompdf = DompdfFactory::create($this->gpdfConfig);
        return new PdfBuilder($dompdf);
    }
}
