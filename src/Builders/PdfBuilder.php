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
        $htmlContent = $this->normalizeArabicSymbols($htmlContent);
        $htmlContent = $this->normalizeArabicLetters($htmlContent);

        $Arabic = new Arabic();
        $p = $Arabic->arIdentify($htmlContent);

        $max_chars = $this->gpdfConfig->get(GpdfSettingKeys::MAX_CHARS_PER_LINE);
        $showNumbersAsHindi = (bool) $this->gpdfConfig->get(GpdfSettingKeys::SHOW_NUMBERS_AS_HINDI);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $fragment = $this->normalizeArabicDigits(substr($htmlContent, $p[$i - 1], $p[$i] - $p[$i - 1]));

            $numericTokens = [];
            $fragment = $this->protectNumericTokens($fragment, $numericTokens);

            $utf8ar = $Arabic->utf8Glyphs($fragment, $max_chars, $showNumbersAsHindi);
            $utf8ar = $this->restoreNumericTokens($utf8ar, $numericTokens, $showNumbersAsHindi);
            $utf8ar = $this->lockShapedLines($utf8ar);

            $htmlContent   = substr_replace($htmlContent, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        if (!$showNumbersAsHindi) {
            $htmlContent = $this->convertArabicNumbers($htmlContent);
        }

        return $this->convertEntities($htmlContent);
    }

    /**
     * Lock each shaped line so dompdf renders it as exactly one physical row.
     *
     * Ar-PHP's utf8Glyphs() reverses every line into right-to-left *visual*
     * order, assuming each line stays a single rendered line, and inserts a
     * "\n" wherever it wraps at maxCharsPerLine. If dompdf then re-wraps such a
     * line — which happens whenever the container (e.g. a table cell) is
     * narrower than maxCharsPerLine — the resulting rows come out vertically
     * reversed, i.e. bottom-to-top.
     *
     * Wrapping each shaped line in a white-space:nowrap span prevents that
     * re-wrap, so one shaped line always maps to one row and the reading order
     * is preserved. Keep maxCharsPerLine close to the character capacity of the
     * narrowest container the text is rendered in, otherwise a locked line can
     * overflow instead of wrapping. See
     * https://github.com/omaralalwi/Gpdf/issues/18
     *
     * @param string $shaped Glyph-joined, visually ordered text from utf8Glyphs()
     * @return string
     */
    private function lockShapedLines(string $shaped): string
    {
        $lines = array_map(
            static fn ($line) => '<span style="white-space: nowrap;">' . $line . '</span>',
            explode("\n", str_replace("\r", '', $shaped))
        );

        return implode('<br />', $lines);
    }

    /**
     * Normalize Arabic-Indic symbols (U+066A..U+066D) to their ASCII equivalents
     * before glyph shaping.
     *
     * These characters sit inside the Arabic Unicode block, so the shaper treats
     * them as Arabic letters, but it has no glyph form for them. It then emits a
     * malformed "&#x;" entity, which silently swallows the character and can hide
     * the surrounding text in the rendered PDF (e.g. the Arabic percent sign "٪").
     * The ASCII equivalents render correctly and keep the content intact.
     * See https://github.com/omaralalwi/Gpdf/issues/13
     *
     * @param string $text
     * @return string
     */
    private function normalizeArabicSymbols(string $text): string
    {
        $arabicSymbols = [
            '٪', // U+066A ARABIC PERCENT SIGN
            '٫', // U+066B ARABIC DECIMAL SEPARATOR
            '٬', // U+066C ARABIC THOUSANDS SEPARATOR
            '٭', // U+066D ARABIC FIVE POINTED STAR
        ];
        $standardSymbols = ['%', '.', ',', '*'];

        return str_replace($arabicSymbols, $standardSymbols, $text);
    }

    /**
     * Normalize Persian/Urdu letter variants to their Arabic equivalents.
     *
     * Arabic text is often typed on a Persian or Urdu keyboard, which produces
     * letters the shaper has no glyph form for (e.g. "ھ" U+06BE instead of "ه").
     * Those letters also fall outside the range arIdentify() scans, so they split
     * one Arabic run into several fragments that each get reversed on their own —
     * scrambling the whole sentence. Mapping them onto the Arabic letters they
     * stand for keeps the run intact and renders correctly.
     * See https://github.com/omaralalwi/Gpdf/issues/11
     *
     * Letters that carry a sound Arabic has no equivalent for ("پ", "چ", "ژ",
     * "گ") are deliberately left alone, so genuine Persian words keep their
     * meaning. The shaper has no glyph form for most of them, so Persian text
     * still renders unshaped — Gpdf targets Arabic.
     *
     * @param string $text
     * @return string
     */
    private function normalizeArabicLetters(string $text): string
    {
        $letterVariants = [
            'ک' => 'ك', // U+06A9 KEHEH
            'ی' => 'ي', // U+06CC FARSI YEH
            'ھ' => 'ه', // U+06BE HEH DOACHASHMEE
            'ہ' => 'ه', // U+06C1 HEH GOAL
            'ۀ' => 'ة', // U+06C0 HEH WITH YEH ABOVE
            'ۃ' => 'ة', // U+06C3 TEH MARBUTA GOAL
            'ے' => 'ي', // U+06D2 YEH BARREE
            'ۓ' => 'ي', // U+06D3 YEH BARREE WITH HAMZA ABOVE
            'ۍ' => 'ي', // U+06CD YEH WITH TAIL
            'ې' => 'ي', // U+06D0 E
            'ٱ' => 'ا', // U+0671 ALEF WASLA
            'ٲ' => 'أ', // U+0672 ALEF WITH WAVY HAMZA ABOVE
            'ٳ' => 'إ', // U+0673 ALEF WITH WAVY HAMZA BELOW
            "\xE2\x80\x8C" => '', // U+200C ZERO WIDTH NON-JOINER
            "\xE2\x80\x8D" => '', // U+200D ZERO WIDTH JOINER
        ];

        return strtr($text, $letterVariants);
    }

    /**
     * Normalize Arabic-Indic digits to ASCII digits before glyph shaping.
     *
     * The shaper only recognises ASCII digits as a number, and keeps those in
     * left-to-right order while it reverses the Arabic around them. Arabic-Indic
     * digits are treated as ordinary letters instead, so "١٠.٥٧" came out of the
     * shaper reversed as "٧٥.٠١". Feeding the shaper ASCII digits keeps the
     * number readable; utf8Glyphs() renders them back as Arabic-Indic when
     * SHOW_NUMBERS_AS_HINDI is enabled. This runs per Arabic fragment, so digits
     * elsewhere in the document — markup, CSS values — are never touched.
     * See https://github.com/omaralalwi/Gpdf/issues/12
     *
     * @param string $text
     * @return string
     */
    private function normalizeArabicDigits(string $text): string
    {
        $easternArabicNumerals  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $extendedArabicNumerals = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $standardArabicNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace(
            array_merge($easternArabicNumerals, $extendedArabicNumerals),
            array_merge($standardArabicNumerals, $standardArabicNumerals),
            $text
        );
    }

    /**
     * Replace every multi-group number with a digit-only placeholder of the same
     * length, so glyph shaping cannot reorder it.
     *
     * arGlyphsPreConvert() walks a fragment backwards and flushes each run of
     * digits the moment it hits a non-digit. A separator inside a number is such
     * a non-digit, so "10.57" is emitted as two runs in reverse order — "57.10".
     * A placeholder holds no separator, so it survives as a single run and lands
     * where the number belongs; restoreNumericTokens() then puts the real number
     * back. See https://github.com/omaralalwi/Gpdf/issues/12
     *
     * @param string $text An Arabic fragment, not the whole document
     * @param array<string,string> $numericTokens Filled with placeholder => number
     * @return string
     */
    private function protectNumericTokens(string $text, array &$numericTokens): string
    {
        $numericTokens = [];
        $source = $text;

        // no /u modifier: match ASCII digits only, and stay safe on odd byte sequences
        $protected = preg_replace_callback(
            '/\d+(?:[.,]\d+)+/',
            function (array $match) use (&$numericTokens, $source) {
                $placeholder = $this->buildNumericPlaceholder(strlen($match[0]), $source, $numericTokens);

                if ($placeholder === null) {
                    return $match[0];
                }

                $numericTokens[$placeholder] = $match[0];

                return $placeholder;
            },
            $text
        );

        return $protected ?? $text;
    }

    /**
     * Build a digit-only placeholder of an exact length that appears nowhere in
     * the fragment and has not been handed out yet.
     *
     * @param int $length
     * @param string $text
     * @param array<string,string> $used
     * @return string|null Null when no free placeholder of that length exists
     */
    private function buildNumericPlaceholder(int $length, string $text, array $used): ?string
    {
        // a free candidate is normally found on the first try, so cap the scan
        // instead of walking the full 10^length space for very long numbers
        $limit = min(10 ** $length, 100000);

        for ($candidate = 0; $candidate < $limit; $candidate++) {
            $placeholder = str_pad((string) $candidate, $length, '0', STR_PAD_LEFT);

            if (!isset($used[$placeholder]) && strpos($text, $placeholder) === false) {
                return $placeholder;
            }
        }

        return null;
    }

    /**
     * Put the real numbers back in place of their placeholders.
     *
     * utf8Glyphs() rewrites digits inside Arabic fragments to Arabic-Indic when
     * Hindi numerals are enabled, so a placeholder is looked up in both numeral
     * systems and restored in whichever one it was found.
     *
     * @param string $text
     * @param array<string,string> $numericTokens
     * @param bool $showNumbersAsHindi
     * @return string
     */
    private function restoreNumericTokens(string $text, array $numericTokens, bool $showNumbersAsHindi): string
    {
        if (empty($numericTokens)) {
            return $text;
        }

        $search  = [];
        $replace = [];

        foreach ($numericTokens as $placeholder => $number) {
            $search[]  = $placeholder;
            $replace[] = $number;

            if ($showNumbersAsHindi) {
                $search[]  = $this->convertToArabicIndicNumbers($placeholder);
                $replace[] = $this->convertToArabicIndicNumbers($number);
            }
        }

        return str_replace($search, $replace, $text);
    }

    /**
     * Render Arabic-Indic digits as ASCII digits.
     *
     * @param string $text
     * @return string
     */
    private function convertArabicNumbers($text)
    {
        $easternArabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $standardArabicNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($easternArabicNumerals, $standardArabicNumerals, $text);
    }

    /**
     * Render ASCII digits as Arabic-Indic digits.
     *
     * @param string $text
     * @return string
     */
    private function convertToArabicIndicNumbers(string $text): string
    {
        $standardArabicNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $easternArabicNumerals  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return str_replace($standardArabicNumerals, $easternArabicNumerals, $text);
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
