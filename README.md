<p align="center">
  <a href="https://omaralalwi.github.io/Gpdf" target="_blank">
    <img src="https://raw.githubusercontent.com/omaralalwi/Gpdf/master/public/images/gpdf-banner-bg.jpg" alt="Gpdf">
  </a>
</p>

# [Gpdf](https://github.com/omaralalwi/Gpdf) : HTML to PDF Converter for PHP & Laravel

Open Source PHP Package for converting HTML to PDF in PHP & Laravel applications, with out-of-the-box support for Arabic content and other languages. Extends [dompdf](https://github.com/dompdf/dompdf) to add new features and solve issues like Arabic language support.

## Requirements

- PHP version 8.1 or higher
- DOM extension
- MBString extension
- php-font-lib
- php-svg-lib

## Installation

```bash
composer require omaralalwi/gpdf
```

## Publish Resources

After installation, publish the config and fonts resources by running the following commands in the root project path:

```bash
php vendor/omaralalwi/gpdf/scripts/publish_fonts.php

php vendor/omaralalwi/gpdf/scripts/publish_config.php
```

**Note for Publish Issues:** If you encounter any issues while publishing, manually copy the `vendor/omaralalwi/gpdf/assets/fonts` folder to `public/vendor/gpdf` and ensure the fonts are in `public/vendor/gpdf/fonts`. Also, copy `vendor/omaralalwi/gpdf/config/gpdf.php` to the `/config` folder in the root path.

---

## Usage with Native PHP Apps

After installing the package and publishing resources, include `autoload.php` and use the `Gpdf` class.

### Basic Usage

```php
require_once __DIR__ . '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;

$htmlFile = __DIR__ . '/contents/example-1.html';
$content = file_get_contents($htmlFile);
$gpdfConfigFile = require_once 'config/gpdf.php';

$config = new GpdfConfig($gpdfConfigFile);
$gpdf = new Gpdf($config);
$pdfContent = $gpdf->generate($content);

header('Content-Type: application/pdf');
echo $pdfContent;
```

**Note:** Customize the settings file as needed.

### Stream Generated PDF Files

Stream a PDF directly to the browser using `generateWithStream`:

```php
require_once __DIR__ . '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;

$htmlFile = __DIR__ . '/contents/example-1.html';
$content = file_get_contents($htmlFile);

$gpdfConfigFile = require_once 'config/gpdf.php';
$config = new GpdfConfig($gpdfConfigFile);

$gpdf = new Gpdf($config);
$pdfContent = $gpdf->generateWithStream($content, 'demo-file-name', true);

header('Content-Type: application/pdf');
echo $pdfContent;
```

### Storing Generated PDF Files

Save a PDF to a specific location using `generateWithStore`:

```php
require_once __DIR__ . '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;

$htmlFile = __DIR__ . '/contents/example-1.html';
$content = file_get_contents($htmlFile);

$gpdfConfigFile = require_once 'config/gpdf.php';
$config = new GpdfConfig($gpdfConfigFile);

$gpdf = new Gpdf($config);
$pdfContent = $gpdf->generateWithStore($content, __DIR__ . '/storage/downloads/', 'stored-pdf-file');

header('Content-Type: application/pdf');
echo $pdfContent;
```

### [Demo Native PHP App](https://github.com/omaralalwi/Gpdf-Native-PHP-Demo)
please see this Demo Native PHP app contain more detailed examples and cases like pass dynamic parameters for html file & pass inline configs , .. and another cases.

---

## Usage with Laravel

### Using the Gpdf Facade

```php
use Omaralalwi\Gpdf\Facade\Gpdf as GpdfFacade;

public function generatePdf()
{
    $html = view('pdf.example-1')->render();
    $pdfContent = GpdfFacade::generate($html);
    return response($pdfContent, 200, ['Content-Type' => 'application/pdf']);
}
```

### Using Dependency Injection

```php
use Omaralalwi\Gpdf\Gpdf;

public function generateSecondWayPdf(Gpdf $gpdf)
{
    $html = view('pdf.example-2')->render();
    $pdfFile = $gpdf->generate($html);
    return response($pdfFile, 200, ['Content-Type' => 'application/pdf']);
}
```

### Stream Generated PDF Files

Stream a PDF directly to the browser using `generateWithStream`:

```php
public function generateAndStream()
{
    $html = view('pdf.example-2')->render();
    $gpdf = app(Gpdf::class);
    $gpdf->generateWithStream($html, 'test-streamed-pdf', true);
    return response(null, 200, ['Content-Type' => 'application/pdf']);
}
```

### Storing Generated PDF Files

Save a PDF to storage using `generateWithStore`:

```php
public function generateAndStore()
{
    $html = view('pdf.example-2')->render();
    $gpdf = app(Gpdf::class);
    $storePath = storage_path('app/downloads/users/');
    $gpdf->generateWithStore($html, $storePath, 'test-stored-pdf-file');
    return response(null, 200, ['Content-Type' => 'application/pdf']);
}
```

### [Demo Laravel App](https://github.com/omaralalwi/Gpdf-Laravel-Demo)
this Demo Laravel app contain more detailed examples and cases.

---

## Supported Fonts
Gpdf supports the following installed fonts (ready to use without any additional configurations):

[Supported Fonts](https://github.com/omaralalwi/Gpdf/blob/9e2342d43066169049bff5a72435e421f0b21daa/src/Enums/GpdfDefaultSupportedFonts.php)

## Support for Arabic

Gpdf supports Arabic content out-of-the-box. Simply pass Arabic text within your HTML content. Make sure to use Arabic fonts, which are included by default.

### Supported Arabic Fonts

The following built-in fonts support Arabic:

`DejaVu Sans Mono` , `Tajawal` , `Almarai` , `Cairo` , `Noto Naskh Arabic` , `Markazi Text` .

We Recommended to Use font name from `Omaralalwi\Gpdf\Enums\GpdfDefaultSupportedFonts` Enum class , like `default font name` in config file .
### Examples

- [Native PHP example](https://github.com/omaralalwi/Gpdf-Native-PHP-Demo/blob/master/generateArPdf.php)
- [Laravel example](https://github.com/omaralalwi/Gpdf-Laravel-Demo/blob/c68bfbc84015d7eb0d3f473929cff488dc42ad9f/app/Http/Controllers/GpdfController.php#L74)

---

## Installing New Fonts

To install a new font, follow these steps:

1. Ensure the default fonts are published to `public/vendor/gpdf/fonts`.
2. Prepare at least one font (Normal) for each family `(Normal, Bold, Italic, BoldItalic)`.
3. Copy the fonts to any path (**not the default fonts path**).
4. The font family name must be enclosed in double quotes and written in lowercase.
5. fonts names must be in kebab case with capitalize.
6. Run install font script with the following command:

```bash
php vendor/omaralalwi/gpdf/scripts/install_font.php "family name" ./path_to_font/Font-Normal.ttf ./path_to_font/Font-Bold.ttf ./resources/fonts/Tajawal-Italic.ttf ./path_to_font/Font-BoldItalic.ttf
```

For example, to install the `Tajawal` font family:

```bash
php vendor/omaralalwi/gpdf/scripts/install_font.php "tajawal" ./resources/fonts/Tajawal-Normal.ttf ./resources/fonts/Tajawal-Bold.ttf ./resources/fonts/Tajawal-Italic.ttf ./resources/fonts/Tajawal-BoldItalic.ttf
```

---

## Features

- Compatibility with any Standard PHP application, Or framework.
- Supports 17 fonts by default, including 7 that support Arabic.
- Allows for the installation of custom fonts.
- Provides easy integration with Laravel applications.
- Offers customizable options for PDF generation.
- Includes detailed documentation.
- provide demo applications for quick start-up [Demo Native PHP App](https://github.com/omaralalwi/Gpdf-Native-PHP-Demo) , [Demo Laravel App](https://github.com/omaralalwi/Gpdf-Laravel-Demo) .
- Unit Tests Includes unit tests.

---

## Thanks
- ### [dompdf](https://github.com/dompdf/dompdf) .
- ### [Ar-PHP](https://github.com/khaled-alshamaa/ar-php).


## Testing

```bash
composer test
```
or
```bash
php run-tests.php
```

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email `omaralwi2010@gmail.com`.

## Credits

- [Omar Alalwi](https://omaralalwi.info)

## License

The MIT License (MIT). See [LICENSE](LICENSE.md) for more information.

---
