# GPDF: Multilingual & Arabic PDF Generator for PHP/Laravel Applications

[![GitHub Release](https://img.shields.io/github/v/release/omaralalwi/Gpdf?style=for-the-badge&include_prereleases)](https://github.com/omaralalwi/Gpdf/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/omaralalwi/gpdf?style=for-the-badge)](https://packagist.org/packages/omaralalwi/gpdf)
[![GitHub Stars](https://img.shields.io/github/stars/omaralalwi/Gpdf?style=for-the-badge)](https://github.com/omaralalwi/Gpdf/stargazers)
[![PHP Version](https://img.shields.io/packagist/php-v/omaralalwi/gpdf?style=for-the-badge)](https://php.net)
[![License](https://img.shields.io/github/license/omaralalwi/Gpdf?style=for-the-badge)](https://opensource.org/licenses/MIT)

<p align="center">
  <a href="https://github.com/omaralalwi/Gpdf">
    <img src="https://raw.githubusercontent.com/omaralalwi/Gpdf/master/public/images/gpdf-banner-bg.jpg" alt="GPDF: Arabic PDF Generator with S3 Storage | PHP/Laravel Package">
  </a>
</p>

**GPDF** is a PHP/Laravel package for generating **Arabic, RTL, and multilingual PDFs** with native support for 17 built-in Arabic fonts, S3 cloud storage, and enterprise-ready features. Built as a DomPDF extension, it solves Arabic rendering issues while adding modern capabilities for documents, invoices, and reports.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Important Note](#important-note)
- [Publish Resources](#publish-resources)
- [Publish Config](#publish-config-file)
- [Usage with Laravel](#usage-with-laravel)
  - [Using the Gpdf Facade](#using-the-gpdf-facade)
  - [Using Dependency Injection](#using-dependency-injection)
  - [Stream Generated PDF Files](#stream-generated-pdf-files-1)
  - [Storing Generated PDF Files](#storing-generated-pdf-files)
- [Usage with Native PHP Apps](#usage-with-native-php-apps)
  - [Basic Usage](#basic-usage)
  - [Stream Generated PDF Files](#stream-generated-pdf-files)
  - [Store Files to Local](#store-files-to-local)
  - [Store Files to S3](#store-files-to-s3)
- [Supported Fonts](#supported-fonts)
  - [Support for Arabic](#support-for-arabic)
  - [Installing New Fonts](#installing-new-fonts)
- [Install Custom Fonts](#installing-custom-fonts)
- [Features](#features)
- [Thanks](#thanks)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributors](#contributors-)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

---

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
### IMPORTANT NOTE:

if you have old version with some issues, please Do following :-
- delete old config file.
- delete package from composer file.
- re-install package.
- publish config file again.
- then delete config cache.

## Publish Resources

After installation, publish the config and fonts resources by running the following commands in the root project path:

```bash
php vendor/omaralalwi/gpdf/scripts/publish_fonts.php
```

## Publish Config File

```bash
php vendor/omaralalwi/gpdf/scripts/publish_config.php
```

---

**Note for Publish Issues:** If you encounter any issues while publishing, manually copy the `vendor/omaralalwi/gpdf/assets/fonts` folder to `public/vendor/gpdf` and ensure the fonts are in `public/vendor/gpdf/fonts`. Also, copy `vendor/omaralalwi/gpdf/config/gpdf.php` to the `/config` folder in the root path.

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
// by default it store files to local driver (path should in public path).
public function generateAndStream()
{
    $html = view('pdf.example-2')->render();
    $gpdf = app(Gpdf::class);
    $gpdf->generateWithStream($html, 'test-streamed-pdf', true);
    return response(null, 200, ['Content-Type' => 'application/pdf']);
}
```

### Storing Generated PDF Files

#### Store Files To local

Save a PDF to storage using `generateWithStore`:

**Note** By default it store files to local driver (ensure that: the store path is access able for read and write).

please see [generateWithStore params](#generateWithStore-params) .
```php
public function generateAndStore()
{
    $html = view('pdf.example-2')->render();
    $gpdf = app(Gpdf::class);
    $storePath = storage_path('app/downloads/users/');
    $gpdf->generateWithStore($html, $storePath, 'test-stored-pdf-file', true, false); // ssl verify should be true in production .
    return $file['ObjectURL']; // return file url as string , to store in db or do any action
}
// may be you will face problems with stream in local, so you can disable ssl verify in local, but should enable it in production.
```

#### Store Files To S3
same to store in local, just replace local path with bucket name, and replace `generateWithStore` with `generateWithStoreToS3` .

**Note** Ensure you setup s3 configs in config file.
```php
    public function generateAndStoreToS3()
    {
        $data = $this->getDynamicParams();
        $html = view('pdf.example-2',$data)->render();
        $gpdf = app(Gpdf::class);
        $bucketName = 'your_s3_bucket_name'; // should be read abel and write able .
        $file = $gpdf->generateWithStoreToS3($html, $bucketName, 'test-store-pdf-fle', true, true); // with s36 the ssl verify will work in local or production (always secure).
        return $file['ObjectURL']; // return file url as string , to store in db or do any action
    }
```

#### Generate Advance With Fixed Header
please see [this example](https://github.com/omaralalwi/Gpdf-Laravel-Demo/blob/0f041e7cf9030f48e2a35ce6d22e8fac5db98c48/app/Http/Controllers/GpdfController.php#L132C1-L133C1) if you need to add fixed header to all pages

### [Demo Laravel App](https://github.com/omaralalwi/Gpdf-Laravel-Demo)
this Demo Laravel app contain more detailed examples and cases.

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

#### Store Files To Local

Save a PDF files to local storage using `generateWithStore`:

**Note** By default it store files to local driver.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;
use Omaralalwi\Gpdf\Enums\{GpdfDefaultSettings, GpdfSettingKeys, GpdfDefaultSupportedFonts};

$htmlFile = __DIR__ . '/contents/example-1.html';
$content = file_get_contents($htmlFile);

$gpdfConfigFile = require_once ('config/gpdf.php');
$config = new GpdfConfig($gpdfConfigFile);

$gpdf = new Gpdf($config);
$sslVerify = false;
$file = $gpdf->generateWithStore($content,null,null, false , $sslVerify); // $sslVerify must be true in production
$fileUrl = $file['ObjectURL'];

return $fileUrl;  // get file url as string to store it in db or do any action
```
#### generateWithStore params

| Parameter                           | Type   | Description                                                                                   |
|-------------------------------------|--------|-----------------------------------------------------------------------------------------------|
| `html file`                         | string | The HTML content to be stored.                                                                |
| `store path or bucket name with s3` | string | The path where the file will be stored, with S3 store this should bucket name.                |
| `file name`                         | string | The name of the file.                                                                         |
| `with stream`                       | bool   | If you need to stream the file to the browser after storing, set this to `true`.              |
| `sslVerify`                         | bool   | If `with stream` is set to `true`, you should set this to `true` in production to verify SSL. |

#### Store Files To S3
same to store in local, just replace local path with bucket name, and replace `generateWithStore` with `generateWithStoreToS3` .

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Omaralalwi\Gpdf\Gpdf;
use Omaralalwi\Gpdf\GpdfConfig;
use Omaralalwi\Gpdf\Enums\{GpdfDefaultSettings, GpdfSettingKeys, GpdfDefaultSupportedFonts};

$htmlFile = __DIR__ . '/contents/example-1.html';
$content = file_get_contents($htmlFile);

$gpdfConfigFile = require_once ('config/gpdf.php');
$config = new GpdfConfig($gpdfConfigFile);

$gpdf = new Gpdf($config);
$fileName = "pdf-file-with-store-to-s3";
$sslVerify = true;
$file = $gpdf->generateWithStoreToS3($content,null,$fileName, true, $sslVerify);
$fileUrl = $file['ObjectURL'];
```

### [Demo Native PHP App](https://github.com/omaralalwi/Gpdf-Native-PHP-Demo)
please see this Demo Native PHP app contain more detailed examples and cases like pass dynamic parameters for html file & pass inline configs , .. and another cases.

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

## Installing Custom Fonts

To install custom font, follow these steps:

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
- store pdf files to S3 or local storage directly.
- stream pdf files from urls (local or s3).
- Supports 17 fonts by default, including 7 that support Arabic.
- Allows for the installation of custom fonts.
- Provides easy integration with Laravel applications.
- Offers customizable options for PDF generation.
- Includes detailed documentation.
- provide demo applications for quick start-up [Demo Native PHP App](https://github.com/omaralalwi/Gpdf-Native-PHP-Demo) , [Demo Laravel App](https://github.com/omaralalwi/Gpdf-Laravel-Demo) .
- Unit Tests Includes unit tests.

---

## Thanks
- ### [dompdf](https://github.com/dompdf/dompdf)
- ### [Ar-PHP](https://github.com/khaled-alshamaa/ar-php)


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

## Contributors ✨

Thanks to these wonderful people for contributing to this project! 💖

<table>
  <tr>
    <td align="center">
      <a href="https://github.com/omaralalwi">
        <img src="https://avatars.githubusercontent.com/u/25439498?v=4" width="100px;" alt="Omar Al Alwi"/>
        <br />
        <sub><b>Omar Al Alwi</b></sub>
      </a>
      <br />
      🏆 Owner
    </td>
    <!-- Contributors -->
    <td align="center">
      <a href="https://github.com/smahi">
        <img src="https://avatars.githubusercontent.com/u/1782133?v=4" width="100px;" alt="Contributor Name"/>
        <br />
        <sub><b>Abesse Smahi</b></sub>
      </a>
      <br />
      💻 Contributor
    </td>
  </tr>
</table>

Want to contribute? Check out the [contributing guidelines](./CONTRIBUTING.md) and submit a pull request! 🚀


## Security

If you discover any security-related issues, please email `omaralwi2010@gmail.com`.

## Credits

- [Omar Alalwi](https://github.com/omaralalwi)

## License

The MIT License (MIT). See [LICENSE](LICENSE.md) for more information.

---

# ✨ **GPDF Community** ✨

Click the button bellow or [join here](https://t.me/gpdf_community) to be part of our growing community!

[![Join Telegram](https://img.shields.io/badge/Join-Telegram-blue?style=for-the-badge&logo=telegram)](https://t.me/gpdf_community)


---

## 📚 Helpful Open Source Packages & Projects

### Packages

- <a href="https://github.com/omaralalwi/lexi-translate"><img src="https://raw.githubusercontent.com/omaralalwi/lexi-translate/master/public/images/lexi-translate-banner.jpg" width="26" height="26" style="border-radius:13px;" alt="lexi translate" /> Lexi Translate </a> simplify managing translations for multilingual Eloquent models with power of morph relationships and caching .

- <a href="https://github.com/omaralalwi/Gpdf"><img src="https://raw.githubusercontent.com/omaralalwi/Gpdf/master/public/images/gpdf-banner-bg.jpg" width="26" height="26" style="border-radius:13px;" alt="Gpdf" /> Gpdf </a> Open Source HTML to PDF converter for PHP & Laravel Applications, supports Arabic content out-of-the-box and other languages.

- <a href="https://github.com/omaralalwi/laravel-taxify"><img src="https://raw.githubusercontent.com/omaralalwi/laravel-taxify/master/public/images/taxify.jpg" width="26" height="26" style="border-radius:13px;" alt="laravel Taxify" /> **laravel Taxify** </a> Laravel Taxify provides a set of helper functions and classes to simplify tax (VAT) calculations within Laravel applications.

- <a href="https://github.com/omaralalwi/laravel-deployer"><img src="https://raw.githubusercontent.com/omaralalwi/laravel-deployer/master/public/images/deployer.jpg" width="26" height="26" style="border-radius:13px;" alt="laravel Deployer" /> **laravel Deployer** </a> Streamlined Deployment for Laravel and Node.js apps, with Zero-Downtime and various environments and branches.

- <a href="https://github.com/omaralalwi/laravel-trash-cleaner"><img src="https://raw.githubusercontent.com/omaralalwi/laravel-trash-cleaner/master/public/images/laravel-trash-cleaner.jpg" width="26" height="26" style="border-radius:13px;" alt="laravel Trash Cleaner" /> **laravel Trash Cleaner** </a> clean logs and debug files for debugging packages.

- <a href="https://github.com/omaralalwi/laravel-time-craft"><img src="https://raw.githubusercontent.com/omaralalwi/laravel-time-craft/master/public/images/laravel-time-craft.jpg" width="26" height="26" style="border-radius:13px;" alt="laravel Time Craft" /> **laravel Time Craft** </a> simple trait and helper functions that allow you, Effortlessly manage date and time queries in Laravel apps.

- <a href="https://github.com/omaralalwi/php-builders"><img src="https://repository-images.githubusercontent.com/917404875/c5fbf4c9-d41f-44c6-afc6-0d66cf7f4c4f" width="26" height="26" style="border-radius:13px;" alt="PHP builders" /> **PHP builders** </a> sample php traits to add ability to use builder design patterns with easy in PHP applications.

- <a href="https://github.com/omaralalwi/php-py"> <img src="https://avatars.githubusercontent.com/u/25439498?v=4" width="26" height="26" style="border-radius:13px;" alt="PhpPy - PHP Python" /> **PhpPy - PHP Python** </a> Interact with python in PHP applications.

- <a href="https://github.com/omaralalwi/laravel-py"><img src="https://avatars.githubusercontent.com/u/25439498?v=4" width="26" height="26" style="border-radius:13px;" alt="Laravel Py - Laravel Python" /> **Laravel Py - Laravel Python** </a> interact with python in Laravel applications.

- <a href="https://github.com/deepseek-php/deepseek-php-client"><img src="https://avatars.githubusercontent.com/u/193405629?s=200&v=4" width="26" height="26" style="border-radius:13px;" alt="Deepseek PHP client" /> **deepseek PHP client** </a> robust and community-driven PHP client library for seamless integration with the Deepseek API, offering efficient access to advanced AI and data processing capabilities .

- <a href="https://github.com/deepseek-php/deepseek-laravel"><img src="https://github.com/deepseek-php/deepseek-laravel/blob/master/public/images/laravel%20deepseek%20ai%20banner.jpg?raw=true" width="26" height="26" style="border-radius:13px;" alt="deepseek laravel" /> **deepseek laravel** </a> Laravel wrapper for Deepseek PHP client to seamless deepseek AI API integration with Laravel applications.

- <a href="https://github.com/qwen-php/qwen-php-client"><img src="https://avatars.githubusercontent.com/u/197095442?s=200&v=4" width="26" height="26" style="border-radius:13px;" alt="Qwen PHP client" /> **Qwen PHP client** </a> robust and community-driven PHP client library for seamless integration with the Qwen API .

- <a href="https://github.com/qwen-php/qwen-laravel"><img src="https://github.com/qwen-php/qwen-laravel/blob/master/public/images/laravel%20qwen%20ai%20banner.jpg?raw=true" width="26" height="26" style="border-radius:13px;" alt="qwen laravel" /> **Laravel qwen** </a> wrapper for qwen PHP client to seamless Alibaba qwen AI API integration with Laravel applications..

### Dashboards

- <a href="https://github.com/omaralalwi/laravel-startkit"><img src="https://raw.githubusercontent.com/omaralalwi/laravel-startkit/master/public/screenshots/backend-rtl.png" width="26" height="26" style="border-radius:13px;" alt="Laravel Startkit" /> **Laravel Startkit** </a> Laravel Admin Dashboard, Admin Template with Frontend Template, for scalable Laravel projects.

- <a href="https://github.com/kunafaPlus/kunafa-dashboard-vue"><img src="https://github.com/kunafaPlus/kunafa-dashboard-vue/raw/master/public/screenshots/Home-LTR.png" width="26" height="26" style="border-radius:13px;" alt="Kunafa Dashboard Vue" /> **Kunafa Dashboard Vue** </a> A feature-rich Vue.js 3 dashboard template with multi-language support and full RTL/LTR bidirectional layout capabilities.

### References

- <a href="https://github.com/omaralalwi/clean-code-summary"><img src="https://avatars.githubusercontent.com/u/25439498?v=4" width="26" height="26" style="border-radius:13px;" alt="Clean Code Summary" /> **Clean Code Summary** </a> summarize and notes for books and practices about clean code.

- <a href="https://github.com/omaralalwi/solid-principles-summary"><img src="https://avatars.githubusercontent.com/u/25439498?v=4" width="26" height="26" style="border-radius:13px;" alt="SOLID Principles Summary" /> **SOLID Principles Summary** </a> summarize and notes for books about SOLID Principles.
