# Gpdf

<p align="center">
  <a href="https://omaralalwi.github.io/Gpdf" target="_blank">
    <img src="https://raw.githubusercontent.com/omaralalwi/Gpdf/master/public/images/gpdf-banner-bg.jpg" alt="Gpdf">
  </a>
</p>

Open Source HTML to PDF converter for PHP & Laravel Applications, supports Arabic content out-of-the-box and other languages.

Extends [dompdf](https://github.com/dompdf/dompdf) to add new features and solve problem like Arabic language support.

## [Documentation](https://omaralalwi.github.io/Gpdf)


## Introduction

Open Source HTML to PDF converter for PHP & Laravel Applications

Extends dompdf to add new features and solve problem like Arabic language support.

`Gpdf` is a PHP package designed to generate PDF documents from HTML content, supporting Arabic text and other languages.

## Getting Started

### Installation with Native PHP apps

```bash
composer require omaralalwi/gpdf
```

Then publish config and fonts resources , run following commands in root project path.

```json
php vendor/omaralalwi/gpdf/scripts/publish_fonts.php
```

```json
php vendor/omaralalwi/gpdf/scripts/publish_config.php
```

see usage section for more details.

### Installation with Laravel Apps

```bash
composer require omaralalwi/gpdf
```

Then publish config and fonts resources , run following commands in root project path.

```json
php vendor/omaralalwi/gpdf/scripts/publish_fonts.php
```

```json
php artisan vendor:publish --tag=gpdf 
```

see usage section for more details.


## Compatibility

it is require at least `PHP 8.1` .

---

## Gpdf Native PHP Integration

Below is a documentation guide for using the `Omaralalwi\Gpdf` package in a native PHP environment. This guide assumes basic familiarity with PHP and Composer.

### Installation

First, install the package via Composer:

```bash
composer require omaralalwi/gpdf
```

### Publish Config and fonts

after install you must publish config and fonts resources , run following commands in root project path.

```json
php vendor/omaralalwi/gpdf/scripts/publish_fonts.php
```

```json
php vendor/omaralalwi/gpdf/scripts/publish_config.php
```

**Note** if you facing any problems while publish, please make it manually by copy `vendor/omaralalwi/gpdf/assets/fonts` folder to `/public/vendor/gpdf`, to ensure the fonts in `public/vendor/gpdf/fonts` path.

and compy `vendor/omaralalwi/gpdf/config/gpdf.php` file to `/config` folder in root path.

### Usage with Native PHP Apps

To generate a PDF, you can use the `Gpdf` class with either default settings or customized configurations.

##### Default Settings

```php
require_once __DIR__. '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;

$htmlFile = __DIR__. '/contents/example-1.html';
$content = file_get_contents($htmlFile);

$gpdfConfigFile = require_once ('config/gpdf.php');
$config = new GpdfConfig($gpdfConfigFile);

$gpdf = new Gpdf($config);
$pdfContent = $gpdf->generate($content);

header('Content-Type: application/pdf');
echo $pdfContent;
```

##### Customized Settings In Settings File
 
feel free to customize settings file as you need.


##### Inline Customized Settings

if you need to pass custom settings inline, you can pass array to GpdfConfig class as following:

```php
require_once __DIR__. '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;
use Omaralalwi\Gpdf\Enums\{GpdfDefaultSettings, GpdfSettingKeys, GpdfDefaultSupportedFonts};

$htmlFile = __DIR__. '/contents/example-1.html';
$content = file_get_contents($htmlFile);

$config = new GpdfConfig([
    GpdfSettingKeys::FONT_DIR => __DIR__. '/public/vendor/gpdf/fonts/',
    GpdfSettingKeys::FONT_CACHE => GpdfDefaultSettings::FONT_CACHE,
    GpdfSettingKeys::DEFAULT_FONT => GpdfDefaultSupportedFonts::DEJAVU_SANS,
    GpdfSettingKeys::IS_JAVASCRIPT_ENABLED => true,
]);

$gpdf = new Gpdf($config);
$pdfContent = $gpdf->generate($content);

header('Content-Type: application/pdf');
echo $pdfContent;
```

#### Generating PDFs with Arabic Content

`Omaralalwi\Gpdf` supports Arabic content out-of-the-box. Simply pass Arabic text within your HTML content:

**Note:** by Default it support more than 7 built in fonts that support arabic .

**NOTE**: No need to add font name or any customization for fonts in html css file (with html arabic content), to avoid conflict and problems , we recommended to use built in arabic fonts, and do  not add any customization related to font in css styles.
if you need to add new font , install it and go .

```php
require_once __DIR__. '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;

$html = file_get_contents(__DIR__. '/contents/example-3-arabic-content.html');

$gpdfConfigFile = require_once 'config/gpdf.php';
$config = new GpdfConfig($gpdfConfigFile);

$gpdf = new Gpdf($config);
$pdfContent = $gpdf->generate($html);

header('Content-Type: application/pdf');
echo $pdfContent;
```

#### Creating PDFs with Dynamic Parameters

To create a PDF with dynamic parameters, you can use a simple function to replace placeholders in your HTML template:

```php
require_once __DIR__. '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;

function populateDynamicParams($html, $params) {
    foreach ($params as $key => $value) {
        $html = str_replace("{{$key}}", $value, $html);
    }
    return $html;
}

$params = [
    'param1' => 'first dynamic param',
    'param2' => 'second dynamic param',
    'param3' => 'third dynamic param',
    // Add more parameters as needed
];

$html = file_get_contents(__DIR__. '/contents/example-4-dynamic-content.html');
$html = populateDynamicParams($html, $params);

$gpdfConfigFile = require_once ('config/gpdf.php');
$config = new GpdfConfig($gpdfConfigFile);

$gpdf = new Gpdf($config);
$pdfContent = $gpdf->generate($html);

header('Content-Type: application/pdf');
echo $pdfContent;
```

#### Streaming PDFs Directly to the Browser

To stream a PDF directly to the browser, use the `generateWithStream` method:

```php
require_once __DIR__. '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;

$htmlFile = __DIR__. '/contents/example-1.html';
$content = file_get_contents($htmlFile);

$gpdfConfigFile = require_once ('config/gpdf.php');
$config = new GpdfConfig($gpdfConfigFile);

$gpdf = new Gpdf($config);
$pdfContent = $gpdf->generateWithStream($content, 'demo-file-name', true);

header('Content-Type: application/pdf');
echo $pdfContent;
```

#### Storing Generated PDFs

To save a PDF to a specific location, use the `generateWithStore` method:

```php
require_once __DIR__. '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;

$htmlFile = __DIR__. '/contents/example-1.html';
$content = file_get_contents($htmlFile);

$gpdfConfigFile = require_once ('config/gpdf.php');
$config = new GpdfConfig($gpdfConfigFile);

$gpdf = new Gpdf($config);
$pdfContent = $gpdf->generateWithStore($content, __DIR__. '/storage/downloads/', 'stored-pdf-file');

header('Content-Type: application/pdf');
echo $pdfContent;
```
---

#### [demo Native PHP app](https://github.com/omaralalwi/Gpdf-Native-PHP-Demo)  for using Gpdf with native PHP apps.

---

## Gpdf Laravel Integration Guide

`Gpdf` is a PHP package designed to generate PDF documents from HTML content with php native apps or with laravel apps,
This guide demonstrates how to integrate `Omaralalwi\Gpdf` into a Laravel applications.

### Installation

First, install the package via Composer:

```bash
composer require omaralalwi/gpdf
```

### Publish Config and fonts

after install you must publish config and fonts resources , run following commands in root project path.

```json
php vendor/omaralalwi/gpdf/scripts/publish_fonts.php
```

```json
php artisan vendor:publish --tag=gpdf 
```

After installation, the package is auto-discovered by Laravel. No further configuration is required.


### Usage With Laravel apps

#### Basic PDF Generation

To generate a PDF, you can use the `Gpdf` facade or dependency injection. Here are two methods demonstrated in a Laravel controller:

#### Method 1: Using the Gpdf Facade

```php
use Omaralalwi\Gpdf\Facade\Gpdf as GpdfFacAde;

public function generatePdf()
{
    $html = view('pdf.example-1')->render();
    $pdfContent = GpdfFacAde::generate($html);
    return response($pdfContent, 200, ['Content-Type' => 'application/pdf']);
}
```

#### Method 2: Dependency Injection

```php
use Omaralalwi\Gpdf\Gpdf;

public function generateSecondWayPdf(Gpdf $gpdf)
{
    $html = view('pdf.example-2')->render();
    $pdfFile = $gpdf->generate($html);
    return response($pdfFile, 200, ['Content-Type' => 'application/pdf']);
}
```

#### Streaming PDFs Directly to the Browser

To stream a PDF directly to the browser, use the `generateWithStream` method:

```php
public function generateAndStream()
{
    $html = view('pdf.example-2')->render();
    $gpdf = app(Gpdf::class);
    $gpdf->generateWithStream($html, 'test-streamed-pdf', true);
    return response(null, 200, ['Content-Type' => 'application/pdf']);
}
```

#### Storing Generated PDFs

To save a PDF to a specific location, use the `generateWithStore` method:

```php
public function generateAndStore()
{
    $html = view('pdf.example-2')->render();
    $gpdf = app(Gpdf::class);
    $storePath = storage_path('app/downloads/users/');
    $gpdf->generateWithStore($html, $storePath, 'test-stored-pdf-file'); // Filename is optional
    return response(null, 200, ['Content-Type' => 'application/pdf']);
}
```

### Support Arabic

`Gpdf` supports Arabic content out-of-the-box. Simply pass Arabic text within your HTML content:
make sure to use arabic fonts with arabic content.
By default , the default font in settings support all languages (with arabic).

```php
public function generatePdfWithArabicContent()
{
    $html = view('pdf.example-2-with-arabic')->render();
    $pdfContent = Gpdf::generate($html);
    return response($pdfContent, 200, ['Content-Type' => 'application/pdf']);
}
```

### supported Arabic Fonts:

- dejavu sans mono
- tajawal
- almarai
- cairo
- noto naskh arabic
- markazi text

we recommended to use font name from `Omaralalwi\Gpdf\Enums\GpdfDefaultSupportedFonts` .

**feel free to install new font, see install fonts section**.

---

#### [demo Laravel app](https://github.com/omaralalwi/Gpdf-Laravel-Demo)  for using Gpdf with laravel apps.

---

Integrating `Gpdf` into PHP Native applications or  Laravel application is straightforward, thanks to Laravel's service container and facades. Whether you need to generate, stream, or store PDFs, `Gpdf` provides a flexible solution for handling PDF generation tasks in your Laravel projects.


## Install New Font family

while the package support many of famouse fonts that support all languages (with arabic), also we provide you script to install any new font, please see following instructions when install new font to avoid and issues.

- make sure you published default built it fonts to default path , 'public/vendor/gpdf/fonts'.
- make sure you published config file.
- prepare at least one font with every font family , while we recommended to provide 4 fonts :-
```json
- Normal 
- Bold
- Italic
- BoldItalic
```
- copy all 4 fonts to any path (Not the fonts default fonts path) , because when you will run install font script it will move them to default fonts path and add font to installed_fonts.json file.
- font family must be in double contagions , with small letters.
- fonts names must be in kebab case with capitalize.

let's see example to install new font 

install font command

```json
php vendor/omaralalwi/gpdf/scripts/install_font.php "family name" ./path_to_font/Font-Normal.ttf ./path_to_font/Font-Bold.ttf ./resources/fonts/Tajawal-Italic.ttf ./path_to_font/Font-BoldItalic.ttf
```

for example to install font family named `tajawal` .

- move the fonts to any path , for example `resources/fonts` folder in root path.

- then run following command :-

```json
php vendor/omaralalwi/gpdf/scripts/install_font.php "tajawal" ./resources/fonts/Tajawal-Normal.ttf ./resources/fonts/Tajawal-Bold.ttf ./resources/fonts/Tajawal-Italic.ttf ./resources/fonts/Tajawal-BoldItalic.ttf
```

That's it.

## Features

- **Support All PHP Apps** : it is support all PHP application or Framworks like laravel.
- **Support 17 Fonts**. by default it support 17 fonts (7 fonts support arabic).
- **Arabic Language Support**: Seamlessly handle Arabic text out of the box, no need to any additional steps to generate arabic pdf files.
- **Easy Integration**: Designed to integrate smoothly with Laravel applications, leveraging the full power of the framework.
- **Customizable Options**: Offers a range of customization options to tailor the PDF output to your specific needs.
- **Comprehensive Documentation**: Detailed guides and examples to help you get started quickly and easily, with Demo apps to use it with native php apps or with laravel apps.
- **Unit Tests**: include unit tests .

---

Feel free to modify it further based on your specific features and enhancements!


### Testing

```bash
composer test
```
OR
```bash
php run-tests.php
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email `omaralwi2010@gmail.com`.

## Credits

-   [omar alalwi](https://omaralalwi.info)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

