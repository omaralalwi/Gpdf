<?php

namespace Omaralalwi\Gpdf\Tests;

use ArPHP\I18N\Arabic;
use Omaralalwi\Gpdf\Builders\PdfBuilder;
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
            GpdfSettingKeys::SHOW_NUMBERS_AS_HINDI => false,
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

    public function testMultilineArabicLinesAreLockedAgainstReversal()
    {
        // Regression for https://github.com/omaralalwi/Gpdf/issues/18
        // Long Arabic wrapped by the shaper into several lines must be emitted
        // as white-space:nowrap spans so the PDF engine cannot re-wrap (and
        // thereby vertically reverse) a visually-ordered line.
        $config = new GpdfConfig([
            GpdfSettingKeys::FONT_DIR => realpath(__DIR__ . '/assets/fonts/'),
            GpdfSettingKeys::FONT_CACHE => realpath(__DIR__ . '/assets/fonts/'),
            GpdfSettingKeys::DEFAULT_FONT => GpdfDefaultSupportedFonts::DEJAVU_SANS,
            GpdfSettingKeys::MAX_CHARS_PER_LINE => 10, // force multiple shaped lines
        ]);

        $dompdf = \Omaralalwi\Gpdf\Factories\DompdfFactory::create($config);
        $builder = new PdfBuilder($dompdf, $config);

        $longArabic = 'تُعدّ اللغة العربية واحدة من أقدم اللغات السامية وأكثرها استخدامًا في العالم';
        $formatted = $builder->formatArabic("<td>{$longArabic}</td>");

        $this->assertStringContainsString('white-space: nowrap', $formatted, 'Shaped lines must be locked with nowrap');
        $this->assertGreaterThan(1, substr_count($formatted, '<br'), 'Long Arabic should produce several locked lines');
        // every locked <span> must be balanced (no orphaned/unwrapped shaped line)
        $this->assertSame(
            substr_count($formatted, '<span style="white-space: nowrap;">'),
            substr_count($formatted, '</span>'),
            'Each shaped line must be wrapped in exactly one nowrap span'
        );

        // and the full pipeline still produces a valid PDF
        $pdf = (new Gpdf($config))->generate("<table><tr><td>{$longArabic}</td></tr></table>");
        $this->assertStringContainsString('%PDF', $pdf);
    }

    public function testArabicPercentSignDoesNotHideContent()
    {
        // Regression for https://github.com/omaralalwi/Gpdf/issues/13
        // "٪" (U+066A) lives in the Arabic block but has no glyph form, so the
        // shaper used to emit a malformed "&#x;" entity and swallow the text
        // around it. It must now behave exactly like the ASCII "%".
        $builder = $this->makeBuilder();

        $this->assertSame(
            $builder->formatArabic('<p>احصل على خصم 10% اليوم</p>'),
            $builder->formatArabic('<p>احصل على خصم 10٪ اليوم</p>'),
            'The Arabic percent sign must render like the ASCII percent sign'
        );

        $pdf = (new Gpdf($this->config))->generate('<p>احصل على خصم 10٪ اليوم</p>');
        $this->assertStringStartsWith('%PDF', $pdf, 'Output must be a PDF, not a caught error string');
    }

    public function testArabicIndicNumbersKeepTheirOrder()
    {
        // Regression for https://github.com/omaralalwi/Gpdf/issues/12
        // The shaper flushes each run of digits separately, so a separator used
        // to split "١٠.٥٧" into two runs that came out reversed as "٧٥.٠١".
        $shaped = $this->makeBuilder()->formatArabic('<p>المبلغ ١٠.٥٧ ريال</p>');

        $this->assertStringContainsString('10.57', $shaped, 'The number must keep its order');
        $this->assertStringNotContainsString('75.01', $shaped, 'The number must not be reversed');

        $grouped = $this->makeBuilder()->formatArabic('<p>قيمة ١٢٣٬٤٥٦٫٧٨ ريال</p>');
        $this->assertStringContainsString('123,456.78', $grouped, 'Grouped numbers must keep their order');
    }

    public function testArabicIndicNumbersKeepTheirOrderAsHindi()
    {
        // Same as above with SHOW_NUMBERS_AS_HINDI enabled: the number is
        // restored in Arabic-Indic digits, still in reading order.
        $config = $this->makeConfig([GpdfSettingKeys::SHOW_NUMBERS_AS_HINDI => true]);
        $shaped = $this->makeBuilder($config)->formatArabic('<p>المبلغ ١٠.٥٧ ريال</p>');

        $this->assertStringContainsString('١٠.٥٧', $shaped, 'Hindi numerals must keep their order');
    }

    public function testPersianLetterVariantsRenderAsArabic()
    {
        // Regression for https://github.com/omaralalwi/Gpdf/issues/11
        // Arabic typed on a Persian/Urdu keyboard uses letters arIdentify() does
        // not scan for, which split one run into fragments that were each
        // reversed on their own and scrambled the sentence.
        $builder = $this->makeBuilder();

        $this->assertSame(
            $builder->formatArabic('<span>جهاز نقطة البيع</span>'),
            $builder->formatArabic('<span>جھاز نقطة البیع</span>'),
            'Persian letter variants must render as their Arabic equivalents'
        );

        $this->assertSame(
            $builder->formatArabic('<p>كتاب جديد</p>'),
            $builder->formatArabic('<p>کتاب جديد</p>'),
            'Keheh must render as Arabic kaf'
        );
    }

    public function testStandaloneNumbersKeepTheConfiguredNumeralSystem()
    {
        // A number with no Arabic letters beside it is still an Arabic fragment,
        // so it must keep its order AND the configured numeral system.
        $hindi = $this->makeBuilder($this->makeConfig([GpdfSettingKeys::SHOW_NUMBERS_AS_HINDI => true]));
        $this->assertStringContainsString('١٢٣٤', $hindi->formatArabic('<p>١٢٣٤</p>'));
        $this->assertStringContainsString('١٠.٥٧', $hindi->formatArabic('<td>١٠.٥٧</td>'));

        $ascii = $this->makeBuilder();
        $this->assertStringContainsString('1234', $ascii->formatArabic('<p>١٢٣٤</p>'));
    }

    public function testMarkupOutsideArabicFragmentsIsUntouched()
    {
        $builder = $this->makeBuilder();

        // numbers living in markup must survive the number protection unchanged
        $this->assertSame(
            '<p>Total: 10.57 USD</p>',
            $builder->formatArabic('<p>Total: 10.57 USD</p>'),
            'Documents without Arabic must pass through unchanged'
        );
        $this->assertStringContainsString(
            'style="width:10.5px"',
            $builder->formatArabic('<p style="width:10.5px">نص عربي هنا</p>'),
            'CSS values must not be rewritten'
        );
        $this->assertStringContainsString(
            'colspan="2"',
            $builder->formatArabic('<td colspan="2">عربي</td>'),
            'Attributes must not be rewritten'
        );
    }

    private function makeConfig(array $overrides = []): GpdfConfig
    {
        return new GpdfConfig(array_merge([
            GpdfSettingKeys::FONT_DIR => realpath(__DIR__ . '/assets/fonts/'),
            GpdfSettingKeys::FONT_CACHE => realpath(__DIR__ . '/assets/fonts/'),
            GpdfSettingKeys::DEFAULT_FONT => GpdfDefaultSupportedFonts::DEJAVU_SANS,
            GpdfSettingKeys::SHOW_NUMBERS_AS_HINDI => false,
        ], $overrides));
    }

    private function makeBuilder(?GpdfConfig $config = null): PdfBuilder
    {
        $config = $config ?? $this->makeConfig();

        return new PdfBuilder(\Omaralalwi\Gpdf\Factories\DompdfFactory::create($config), $config);
    }

    public function testUtf8GlyphsCalledWithSpecificParams()
    {
        $arabicMock = $this->createMock(Arabic::class);

        $arabicMock->expects($this->once())
                   ->method('utf8Glyphs')
                   ->with(
                       $this->equalTo('الجزائر 1234'),
                       $this->equalTo(100),
                       $this->equalTo(false),
                       $this->equalTo(true)
                   )
                   ->willReturn('convertedText');

        $pdfBuilder = $this->createPartialMock(PdfBuilder::class, ['formatArabic']);
        
        $pdfBuilder->expects($this->any())
                   ->method('formatArabic')
                   ->willReturnCallback(function($htmlContent) use ($arabicMock) {
                       return $arabicMock->utf8Glyphs('الجزائر 1234', 100, false, true);
                   });

        $htmlContent = '<p>الجزائر 1234</p>';
        $result = $pdfBuilder->formatArabic($htmlContent);

        $this->assertEquals('convertedText', $result);
    }
}
