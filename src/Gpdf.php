<?php

namespace Omaralalwi\Gpdf;

use Omaralalwi\Gpdf\Factories\DompdfFactory;
use Omaralalwi\Gpdf\Builders\PdfBuilder;
use Omaralalwi\Gpdf\Enums\GpdfSettingKeys;

class Gpdf
{
    protected $gpdfConfig;

    public function __construct(GpdfConfig $gpdfConfig)
    {
        $this->gpdfConfig = $gpdfConfig;
    }

    public function generate($content)
    {
        return $this->getPdfBuilder()->build($content);
    }

    public function generateWithStream($content, $fileName=null, $attachment = false)
    {
        $this->getPdfBuilder()->buildAndStream($content, DompdfFactory::generateFileName($fileName), $attachment);
    }

    /**
     * Generate and save the PDF to a specified file path.
     *
     * @param string $content
     * @param string $filePath
     * @return void
     */
    public function generateWithStore($content, $filePath, $fileName=null): void
    {
        $this->getPdfBuilder()->buildAndStore($content, $filePath, DompdfFactory::generateFileName($fileName));
    }

    public function getConfig(): GpdfConfig
    {
        return $this->gpdfConfig;
    }

    private function getPdfBuilder(): PdfBuilder
    {
        $dompdf = DompdfFactory::create($this->gpdfConfig);
        return new PdfBuilder($dompdf);
    }
}
