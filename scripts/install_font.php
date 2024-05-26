<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Exception;
use Dompdf\Options;
use FontLib\Font;
use Dompdf\FontMetrics;
use Omaralalwi\Gpdf\Enums\{GpdfDefaultSettings, GpdfSettingKeys, GpdfDefaultSupportedFonts};

$fontsPath = "/public/vendor/gpdf/fonts/";

/*
 * this is not official script, it is customized .
 * the official package here
 * https://github.com/dompdf/php-font-lib
 * and this official script
 * https://github.com/dompdf/utils/blob/master/load_font.php
 *
 * Installs a new font family.
 *
 * @param string $fontDir     The directory where fonts are stored.
 * @param string $fontName    The font-family name.
 * @param string $normal      The filename of the normal face font subtype.
 * @param string $bold        The filename of the bold face font subtype.
 * @param string $italic      The filename of the italic face font subtype.
 * @param string $bold_italic The filename of the bold italic face font subtype.
 *
 * @throws Exception
 */
function install_font_family($fontDir, $fontName, $normal, $bold = null, $italic = null, $bold_italic = null) {
    $options = new Options();
    $options->set('fontDir', $fontDir);
    $dompdf = new Dompdf($options);
    $canvas = $dompdf->getCanvas();
    $fontMetrics = new FontMetrics($canvas, $options);

    if (!is_readable($normal)) {
        throw new Exception("Unable to read '$normal'.");
    }

    $dir = dirname($normal);
    $basename = basename($normal);
    $last_dot = strrpos($basename, '.');
    $file = substr($basename, 0, $last_dot);
    $ext = strtolower(substr($basename, $last_dot));

    if (!in_array($ext, [".ttf", ".otf"])) {
        throw new Exception("Unable to process fonts of type '$ext'.");
    }

    $patterns = [
        "bold" => ["_Bold", "Bold", "b", "B", "bd", "BD"],
        "italic" => ["_Italic", "Italic", "i", "I"],
        "bold_italic" => ["_Bold_Italic", "BoldItalic", "bi", "BI", "ib", "IB"],
    ];

    foreach ($patterns as $type => $_patterns) {
        if (!isset($$type) || !is_readable($$type)) {
            foreach ($_patterns as $_pattern) {
                if (is_readable("$dir/{$file}{$_pattern}{$ext}")) {
                    $$type = "$dir/{$file}{$_pattern}{$ext}";
                    break;
                }
            }
        }
    }

    $fonts = compact("normal", "bold", "italic", "bold_italic");
    $entry = [];

    foreach ($fonts as $var => $src) {
        if (is_null($src)) {
            $entry[$var] = $fontDir . '/' . mb_substr(basename($normal), 0, -4);
            continue;
        }

        if (!is_readable($src)) {
            throw new Exception("Requested font '$src' is not readable");
        }

        $dest = $fontDir . '/' . basename($src);

        if (!is_writable(dirname($dest))) {
            throw new Exception("Unable to write to destination '$dest'.");
        }

        echo "Copying $src to $dest...\n"; // Debug statement

        if (!copy($src, $dest)) {
            throw new Exception("Unable to copy '$src' to '$dest'");
        }

        echo "Copied $src to $dest\n"; // Debug statement

        $entry_name = mb_substr($dest, 0, -4);
        echo "Generating Adobe Font Metrics for $entry_name...\n"; // Debug statement

        $font_obj = Font::load($dest);
        $font_obj->saveAdobeFontMetrics("$entry_name.ufm");
        $font_obj->close();

        echo "Generated AFM for $entry_name\n"; // Debug statement

        $entry[$var] = str_replace($fontDir . '/', '', $entry_name);
    }

    $fontMetrics->setFontFamily($fontName, $entry);
    echo "Set font family for $fontName with entry: " . json_encode($entry) . "\n"; // Debug statement
    $fontMetrics->saveFontFamilies();
}

function saveFontFamilies($fontDir, $fontName) {
    $installedFontsFile = $fontDir . '/installed-fonts.json';
    $options = new Options();
    $options->set('fontDir', $fontDir);
    $dompdf = new Dompdf($options);
    $canvas = $dompdf->getCanvas();
    $fontMetrics = new FontMetrics($canvas, $options);
    $fontFamilies = $fontMetrics->getFontFamilies();

    // Read existing content
    $existingFontFamilies = [];
    if (file_exists($installedFontsFile)) {
        $existingContent = file_get_contents($installedFontsFile);
        $existingFontFamilies = json_decode($existingContent, true) ?: [];
    }

    // Filter out unrelated fonts
    $filteredFontFamilies = array_filter($fontFamilies, function ($key) use ($fontName) {
        return $key == $fontName;
    }, ARRAY_FILTER_USE_KEY);


    // Strip the directory path from the font entries
    foreach ($filteredFontFamilies as &$family) {
        foreach ($family as &$font) {
            $font = basename($font);
        }
    }

    $mergedFontFamilies = array_merge($existingFontFamilies, $filteredFontFamilies);

    // Save merged font families to the JSON file
    file_put_contents($installedFontsFile, json_encode($mergedFontFamilies, JSON_PRETTY_PRINT));
    echo "Updated installed-fonts.json\n"; // Debug statement
}

try {
    if ($_SERVER["argc"] < 3) {
        throw new Exception("Usage: {$_SERVER["argv"][0]} font_family font_file");
    }

    $fontName = $_SERVER["argv"][1];
    $normal = $_SERVER["argv"][2];
    $bold = $_SERVER["argv"][3] ?? null;
    $italic = $_SERVER["argv"][4] ?? null;
    $bold_italic = $_SERVER["argv"][5] ?? null;

    $fontDir = getcwd().$fontsPath; //  get the base path relative to where you run the script
//    $fontDir = (string) GpdfDefaultSettings::FONT_DIR;
    install_font_family($fontDir, $fontName, $normal, $bold, $italic, $bold_italic);
    saveFontFamilies($fontDir, $fontName);
    echo "Fonts for '$fontName' family have been registered successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
