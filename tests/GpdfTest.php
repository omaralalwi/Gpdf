<?php

namespace Omaralalwi\Gpdf\Tests;

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;
use PHPUnit\Framework\TestCase;
use Omaralalwi\Gpdf\Enums\{GpdfDefaultSettings, GpdfSettingKeys, GpdfDefaultSupportedFonts};

class GpdfTest extends TestCase
{
    protected $config;

    protected function setUp(): void
    {
        $this->config = new GpdfConfig([
            GpdfSettingKeys::FONT_DIR => realpath(__DIR__ . '/assets/fonts/'),
            GpdfSettingKeys::FONT_CACHE => realpath(__DIR__ . '/assets/fonts/'),
            GpdfSettingKeys::DEFAULT_FONT => GpdfDefaultSupportedFonts::DEJAVU_SANS,
            GpdfSettingKeys::IS_JAVASCRIPT_ENABLED => true,
            GpdfSettingKeys::UTF8GLYPHS_MAX_CHARS => 50,
            GpdfSettingKeys::UTF8GLYPHS_HINDO => true,
            GpdfSettingKeys::UTF8GLYPHS_FORCERTL => false
        ]);
    }

    public function testConfigFileExists()
    {
        $configFile = $this->config->getDefaultConfigFile();
        $configFileExists = file_exists($configFile);
        $configFileContent = $configFileExists ? file_get_contents($configFile) : null;

        echo PHP_EOL;
        echo "Config file exists : " . ($configFileExists ? "Yes" : "No") . PHP_EOL;
        echo "Config file is available : " . ($configFileContent ? "Yes it is available" : "Not available") . PHP_EOL;

        $this->assertTrue($configFileExists);
        $this->assertTrue(!is_null($configFileContent));
    }

    public function testConfigFileKeys()
    {
        $gpdf = new Gpdf($this->config);
        $fontPath = realpath(__DIR__ . $gpdf->getConfig()->get(GpdfSettingKeys::FONT_DIR));
        $defaultFont = $gpdf->getConfig()->get(GpdfSettingKeys::DEFAULT_FONT);

        $this->assertDirectoryExists($fontPath, "Font directory does not exist: $fontPath");
        $this->assertTrue(!is_null($fontPath));
        $this->assertTrue(!is_null($defaultFont));
    }

    public function testCreatePdf()
    {
        $gpdf = new Gpdf($this->config);
        $pdfContent = "<h1> Hello World </h1>";
        $pdf = $gpdf->generate($pdfContent);

        $this->assertNotEmpty($pdf);
    }

    public function testArabicContent()
    {
        $gpdf = new Gpdf($this->config);
        $pdfContent = "<h1>مرحبا بكم</h1>";
        $pdf = $gpdf->generate($pdfContent);

        $this->assertNotEmpty($pdf, "PDF content should not be empty");
        $this->assertStringContainsString('%PDF', $pdf, "PDF content does not contain valid PDF header");
    }

}
