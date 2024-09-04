<?php

namespace Omaralalwi\Gpdf\Builders;

use ArPHP\I18N\Arabic;
use Dompdf\Dompdf;
use Aws\Exception\AwsException;
use Omaralalwi\Gpdf\Clients\S3Client;
use Omaralalwi\Gpdf\Services\{S3Service, LocalFileService};
use Omaralalwi\Gpdf\Enums\GpdfSettingKeys;
use Omaralalwi\Gpdf\Traits\HasGpdfLog;
use Omaralalwi\Gpdf\Traits\HasFile;
use Omaralalwi\Gpdf\GpdfConfig;

class PdfBuilder
{
    use HasGpdfLog, HasFile;

    protected Dompdf $dompdf;
    protected GpdfConfig $gpdfConfig;
    public function __construct(Dompdf $dompdf, GpdfConfig $gpdfConfig)
    {
        $this->dompdf = $dompdf;
        $this->gpdfConfig = $gpdfConfig;
    }

    /**
     * Load HTML content into the Dompdf instance.
     *
     * @param string $htmlContent
     * @return void
     */
    public function load(string $htmlContent): void
    {
        $this->dompdf->loadHtml($htmlContent);
    }

    /**
     * Render the PDF from the loaded HTML content.
     *
     * @return void
     */
    public function render(): void
    {
        $this->dompdf->render();
    }

    /**
     * Generate the PDF content as a string.
     *
     * @return string
     */
    public function output(): string
    {
        return $this->dompdf->output();
    }

    /**
     * Stream the generated PDF directly to the browser.
     *
     * @param string $fileName
     * @param bool $attachment
     * @param bool $newTab
     * @return void
     */
    public function stream(string $fileName, bool $attachment = false, bool $newTab = false): void
    {
        $options = [];
        if ($attachment) {
            $options['Attachment'] = true;
        }
        if ($newTab) {
            $options['newtab'] = true;
        }
        $this->dompdf->stream($fileName, $options);
    }

    /**
     * Load, render, and return the PDF content as a string.
     *
     * @param string $htmlContent
     * @return string
     */
    public function build(string $htmlContent): string
    {
        try {
            $this->preparePdf($htmlContent);
            return $this->output();
        } catch (\Exception $e) {
            return 'An error occurred while creating the PDF: ' . $e->getMessage();
        }
    }

    /**
     * Load, render, and stream the PDF directly to the browser.
     *
     * @param string $htmlContent
     * @param string $fileName
     * @param bool $attachment
     * @return void
     */
    public function buildAndStream(string $htmlContent, string $fileName, bool $attachment = false): void
    {
        try {
            $this->preparePdf($htmlContent);
            $this->stream($fileName, $attachment);
        } catch (\Exception $e) {
            echo 'An error occurred while streaming the PDF: ' . $e->getMessage();
        }
    }

    public function buildAndStore(S3Service|LocalFileService $storageService, string $htmlContent, string $filePath, string $fileName, bool $withStream = false, bool $verifySsl = true)
    {
        try {
            $this->preparePdf($htmlContent);
            $pdfContent = $this->dompdf->output();
            $generatedFile = $this->storeFile($storageService, $pdfContent, $filePath, $fileName);
            $formattedGeneratedFile = $this->appendObjectURLToGeneratedFile($storageService, $generatedFile);

            if ($withStream) {
                $storageService->streamFromUrl($formattedGeneratedFile['ObjectURL'], $verifySsl);
            }

            return $formattedGeneratedFile;
            // stream from url not available with local storage driver.
        } catch (\Exception $e) {
            echo 'An error occurred while saving the PDF: ' . $e->getMessage();
        }
    }

    public function appendObjectURLToGeneratedFile($storageService, $generatedFile)
    {
        $fileUrl = $storageService->getFileUrl($generatedFile);
        $generatedFile['ObjectURL'] = $fileUrl;
        return $generatedFile;
    }

    /**
     * Prepare the PDF by formatting Arabic content, loading it into Dompdf, and rendering it.
     *
     * @param string $htmlContent
     * @return void
     */
    private function preparePdf(string $htmlContent): void
    {
        $formattedContent = $this->formatArabic($htmlContent);
        $this->load($formattedContent);
        $this->render();
    }

    public function formatArabic($htmlContent)
    {
        $Arabic = new Arabic();
        $p = $Arabic->arIdentify($htmlContent);

        $max_chars = $this->gpdfConfig->get(GpdfSettingKeys::MAX_CHARS_PER_LINE);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $Arabic->utf8Glyphs(substr($htmlContent, $p[$i - 1], $p[$i] - $p[$i - 1]), $max_chars);
            //$utf8ar = nl2br($utf8ar);

            $htmlContent   = substr_replace($htmlContent, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        if (!$this->gpdfConfig->get(GpdfSettingKeys::SHOW_NUMBERS_AS_HINDI)) {
            $htmlContent = $this->convertArabicNumbers($htmlContent);
        }

        return $this->convertEntities($htmlContent);
    }

    private function convertArabicNumbers($text)
    {
        $easternArabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $standardArabicNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($easternArabicNumerals, $standardArabicNumerals, $text);
    }

    protected function convertEntities(string $subject): string
    {
        //        if (false === $this->config->get('convert_entities', true)) {
        //            return $subject;
        //        }

        $entities = [
            '€' => '&euro;',
            '£' => '&pound;',
        ];

        foreach ($entities as $search => $replace) {
            $subject = str_replace($search, $replace, $subject);
        }
        return $subject;
    }

    protected function storeFile(S3Service|LocalFileService $storageService, $pdfFile, $filePath, $fileName)
    {
        try {
            return $storageService->store($pdfFile, $filePath, $fileName);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }

    protected function streamFromUrl(S3Service|LocalFileService $storageService, $fileUrl)
    {
        try {
            return $storageService->streamFromUrl($fileUrl);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}
