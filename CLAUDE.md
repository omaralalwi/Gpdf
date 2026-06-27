# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

GPDF is a PHP/Laravel library that wraps [dompdf](https://github.com/dompdf/dompdf) to convert HTML to PDF, with first-class support for **Arabic / RTL / multilingual** content (via [Ar-PHP](https://github.com/khaled-alshamaa/ar-php)) and built-in storage to local disk or S3. It works both as a standalone PHP library and as an auto-discovered Laravel package. Package namespace: `Omaralalwi\Gpdf\` → `src/`.

## Commands

```bash
composer test                       # run the PHPUnit suite (logs junit.xml)
php run-tests.php                    # equivalent test runner without composer scripts
composer test-coverage              # HTML coverage report into coverage/
vendor/bin/phpunit --filter testArabicContent   # run a single test by method name
```

There is no linter/static-analysis config in this repo. Tests use plain `PHPUnit\Framework\TestCase` (not Orchestra Testbench), so they exercise the library directly without booting Laravel.

## Architecture

The public entry point is `Gpdf` (`src/Gpdf.php`). It exposes four generation methods that all funnel through a freshly-built `PdfBuilder`:

- `generate()` → returns PDF bytes as a string
- `generateWithStream()` → streams PDF to the browser
- `generateWithStore()` → stores to local disk (default driver)
- `generateWithStoreToS3()` → stores to S3 bucket

**Request flow:** `Gpdf` → `DompdfFactory::create()` builds a configured `Dompdf` instance → `PdfBuilder` loads/renders/outputs. The critical step is `PdfBuilder::preparePdf()` → `formatArabic()`, which runs HTML through Ar-PHP's `utf8Glyphs` (reshaping Arabic glyphs for correct RTL rendering) **before** handing it to dompdf. dompdf alone renders Arabic incorrectly; this preprocessing is the core value of the package. `formatArabic()` also normalizes Eastern-Arabic numerals to standard digits unless `showNumbersAsHindi` is enabled, and converts a few currency entities.

**Configuration** flows through `GpdfConfig` (`src/GpdfConfig.php`), which merges user overrides on top of the defaults in `config/gpdf.php`. The config array is a flat key→value map. Keys are defined as constants in `src/Enums/GpdfSettingKeys.php` and defaults in `src/Enums/GpdfDefaultSettings.php`. Most keys map 1:1 onto dompdf `Options` — `DompdfFactory::create()` iterates the config and forwards any key present in `GpdfSettingKeys::asArray()` to `$options->set()`. A handful of keys (`storage_path`, `aws_storage_*`, `maxCharsPerLine`, `showNumbersAsHindi`) are GPDF-specific and consumed directly, not passed to dompdf.

**Storage** uses a factory + service split. `StorageServiceFactory::getStorageService($driver, $config)` returns either `LocalFileService` or `S3Service` (backed by `S3Client`, which wraps the AWS SDK). Both services implement `store()`, `getFileUrl()`, and `streamFromUrl()`. Drivers are named by constants in `src/Enums/GpdfStorageDrivers.php`. After storing, `PdfBuilder::buildAndStore()` appends an `ObjectURL` to the returned array — callers typically persist that URL.

**Laravel integration:** `GpdfServiceProvider` is auto-discovered (declared in `composer.json` `extra.laravel`). It registers `Gpdf` as a **singleton** (built from `config('gpdf')`), aliases it to `gpdf`, and publishes `config/gpdf.php` under the `gpdf` tag. The `Gpdf` facade (`src/Facade/Gpdf.php`) resolves that binding. Note the singleton caches one `GpdfConfig`, but each generate call builds a fresh `Dompdf`/`PdfBuilder`, so per-call options are not supported through the facade without re-binding.

## Fonts (important)

Fonts are the most common source of runtime failures. dompdf can only use fonts that have been "installed" (registered into a font cache directory). Source fonts live in `assets/fonts/`; the consuming app expects them published to `public/vendor/gpdf/fonts/`. `config/gpdf.php` points both `fontDir` and `fontCache` at the same directory deliberately, to avoid cache desync. The default font is `Tajawal`. Arabic-capable fonts: DejaVu Sans Mono, Tajawal, Almarai, Cairo, Noto Naskh Arabic, Markazi Text. Supported font names are enumerated in `src/Enums/GpdfDefaultSupportedFonts.php`.

The `scripts/` directory holds standalone CLI tools meant to be run from the **consuming app's** vendor path (they `require` `../../../autoload.php`, i.e. they assume the package sits in `vendor/omaralalwi/gpdf/`):

- `scripts/publish_fonts.php` — copy fonts into `public/vendor/gpdf`
- `scripts/publish_config.php` — copy `config/gpdf.php` into the app
- `scripts/install_font.php "family" Normal.ttf Bold.ttf Italic.ttf BoldItalic.ttf` — register a custom font family

`HasFile::getRootPath()` uses `dirname(__DIR__, 5)` to climb from `vendor/omaralalwi/gpdf/src/Traits/` back to the app root — this hardcoded depth is why the package assumes a standard vendor install location.

## Conventions

- Setting keys are referenced via the `GpdfSettingKeys` (`GpdfSet`) constants, never raw strings — when adding a config option, add the key constant, a default in `GpdfDefaultSettings`, and an entry in `config/gpdf.php`.
- `PdfBuilder` swallows exceptions and returns/echoes an error string rather than throwing — keep this behavior in mind when changing error handling; failures surface as PDF-body text or stdout, not exceptions.
- There are two READMEs kept in parallel: `README.md` (English) and `README-AR.md` (Arabic). Update both when changing user-facing docs.
