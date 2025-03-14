# جي بي دي اف : مولد ملفات بي دي اف لتطبيقات بي اتش بي و لارافيل يدعم جميع اللغات واللغة العربية بشكل كامل


[![GitHub Release](https://img.shields.io/github/v/release/omaralalwi/Gpdf?style=for-the-badge&include_prereleases)](https://github.com/omaralalwi/Gpdf/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/omaralalwi/gpdf?style=for-the-badge)](https://packagist.org/packages/omaralalwi/gpdf)
[![GitHub Stars](https://img.shields.io/github/stars/omaralalwi/Gpdf?style=for-the-badge)](https://github.com/omaralalwi/Gpdf/stargazers)
[![PHP Version](https://img.shields.io/packagist/php-v/omaralalwi/gpdf?style=for-the-badge)](https://php.net)
[![License](https://img.shields.io/github/license/omaralalwi/Gpdf?style=for-the-badge)](https://opensource.org/licenses/MIT)
[![Maintained](https://img.shields.io/badge/Maintained-Yes-success?style=for-the-badge)](https://github.com/omaralalwi/Gpdf/graphs/commit-activity)

<p align="center">
  <a href="https://github.com/omaralalwi/Gpdf">
    <img src="https://raw.githubusercontent.com/omaralalwi/Gpdf/master/public/images/gpdf-banner-bg.jpg" alt="GPDF: مولد PDF بالعربي مع تخزين S3 | حزمة PHP/Laravel">
  </a>
</p>

**GPDF** هي حزمة PHP/Laravel لإنشاء **ملفات PDF متعددة اللغات مع دعم اللغة العربية والكتابة من اليمين إلى اليسار (RTL)**. تتضمن الحزمة دعمًا أصليًا لـ 17 خط عربي، وتخزين سحابي على S3، وميزات متقدمة تناسب احتياجات المؤسسات. تم تصميمها كامتداد لـ DomPDF لحل مشاكل عرض اللغة العربية مع إضافة إمكانيات حديثة لإنشاء الوثائق والفواتير والتقارير.

---

## الفهرس

- [المتطلبات](#المتطلبات)
- [التثبيت](#التثبيت)
- [ملاحظات هامة](#ملاحظات-هامة)
- [نشر الموارد](#نشر-الموارد)
_- [نشر الإعدادات](#نشر-الإعدادات)_
- [الاستخدام مع Laravel](#الاستخدام-مع-laravel)
  - [استخدام Facade الخاص بـ Gpdf](#استخدام-facade-الخاص-بـ-gpdf)
  - [استخدام الحقن الاعتمادي](#استخدام-الحقن-الاعتمادي)
  - [عرض ملفات PDF مباشرة](#عرض-ملفات-pdf-مباشرة)
  - [تخزين ملفات PDF](#تخزين-ملفات-pdf)
- [الاستخدام مع تطبيقات PHP التقليدية](#الاستخدام-مع-تطبيقات-php-التقليدية)
  - [الاستخدام الأساسي](#الاستخدام-الأساسي)
  - [عرض ملفات PDF مباشرة](#عرض-ملفات-pdf-مباشرة-1)
  - [تخزين الملفات محلياً](#تخزين-الملفات-محلياً)
  - [تخزين الملفات على S3](#تخزين-الملفات-على-s3)
- [الخطوط المدعومة](#الخطوط-المدعومة)
  - [الدعم للغة العربية](#الدعم-للغة-العربية)
  - [تثبيت خطوط جديدة](#تثبيت-خطوط-جديدة)
- [تثبيت الخطوط المخصصة](#تثبيت-الخطوط-المخصصة)
- [الميزات](#الميزات)
- [الشكر](#الشكر)
- [الاختبارات](#الاختبارات)
- [سجل التغييرات](#سجل-التغييرات)
- [المساهمون](#المساهمون)
- [الأمان](#الأمان)
- [الاعتمادات](#الاعتمادات)
- [الترخيص](#الترخيص)

---

## المتطلبات

- إصدار PHP 8.1 أو أعلى
- امتداد DOM
- امتداد MBString
- php-font-lib
- php-svg-lib

## التثبيت

```bash
composer require omaralalwi/gpdf
```

### ملاحظات هامة:

لو بتستخدم نسخة أقدم من الباكج وفيها مشاكل او بجز , الرجاء القيام بالخطوات التالية :-
- امسح ملف الإعدادات من المشروع.
- احذف الباكج من ملف ا لكمبوزر.
- أعد تنصيب الباكج.
- قم بنشر ملف الإعدادات.
- امسح كاش الإعدادات.


## نشر الموارد

بعد التثبيت، قم بنشر ملفات الإعدادات والخطوط بتنفيذ الأوامر التالية في جذر المشروع:

```bash
php vendor/omaralalwi/gpdf/scripts/publish_fonts.php
```

## نشر الإعدادات

```bash
php vendor/omaralalwi/gpdf/scripts/publish_config.php
```

**ملاحظة بخصوص مشاكل النشر:** إذا واجهت أي مشاكل أثناء النشر، انسخ يدويًا مجلد `vendor/omaralalwi/gpdf/assets/fonts` إلى `public/vendor/gpdf` وتأكد من وجود الخطوط في `public/vendor/gpdf/fonts`. كما يجب نسخ `vendor/omaralalwi/gpdf/config/gpdf.php` إلى مجلد `/config` في جذر المشروع.

---

## الاستخدام مع Laravel

### استخدام Facade الخاص بـ Gpdf

```php
use Omaralalwi\Gpdf\Facade\Gpdf as GpdfFacade;

public function generatePdf()
{
    $html = view('pdf.example-1')->render();
    $pdfContent = GpdfFacade::generate($html);
    return response($pdfContent, 200, ['Content-Type' => 'application/pdf']);
}
```

### استخدام الحقن الاعتمادي

```php
use Omaralalwi\Gpdf\Gpdf;

public function generateSecondWayPdf(Gpdf $gpdf)
{
    $html = view('pdf.example-2')->render();
    $pdfFile = $gpdf->generate($html);
    return response($pdfFile, 200, ['Content-Type' => 'application/pdf']);
}
```

### عرض ملفات PDF مباشرة

لعرض ملف PDF مباشرة في المتصفح باستخدام `generateWithStream`:

```php
// بشكل افتراضي، يتم تخزين الملفات محلياً (يجب أن يكون المسار ضمن public).
public function generateAndStream()
{
    $html = view('pdf.example-2')->render();
    $gpdf = app(Gpdf::class);
    $gpdf->generateWithStream($html, 'test-streamed-pdf', true);
    return response(null, 200, ['Content-Type' => 'application/pdf']);
}
```

### تخزين ملفات PDF

#### تخزين الملفات محلياً

لحفظ ملف PDF على التخزين باستخدام `generateWithStore`:

**ملاحظة:** بشكل افتراضي، يتم تخزين الملفات محلياً (تأكد من أن مسار التخزين متاح للقراءة والكتابة).

يرجى مراجعة [معايير generateWithStore](#generatewithstore-params).
```php
public function generateAndStore()
{
    $html = view('pdf.example-2')->render();
    $gpdf = app(Gpdf::class);
    $storePath = storage_path('app/downloads/users/');
    $gpdf->generateWithStore($html, $storePath, 'test-stored-pdf-file', true, false); // يجب تفعيل sslVerify في بيئة الإنتاج.
    return $file['ObjectURL']; // يعيد عنوان URL للملف كسلسلة نصية لتخزينه أو استخدامه.
}
// قد تواجه مشاكل مع stream في البيئة المحلية، لذا يمكنك تعطيل sslVerify محليًا، مع ضرورة تفعيله في الإنتاج.
```

#### تخزين الملفات على S3

بنفس طريقة التخزين المحلي، فقط استبدل مسار التخزين المحلي باسم الدلو (bucket name)، واستبدل `generateWithStore` بـ `generateWithStoreToS3`.

**ملاحظة:** تأكد من إعداد تكوينات S3 في ملف الإعدادات.
```php
public function generateAndStoreToS3()
{
    $data = $this->getDynamicParams();
    $html = view('pdf.example-2', $data)->render();
    $gpdf = app(Gpdf::class);
    $bucketName = 'your_s3_bucket_name'; // يجب أن يكون الدلو قابلًا للقراءة والكتابة.
    $file = $gpdf->generateWithStoreToS3($html, $bucketName, 'test-store-pdf-file', true, true); // مع S3 يتم تفعيل sslVerify دائمًا للأمان.
    return $file['ObjectURL']; // يعيد عنوان URL للملف كسلسلة نصية.
}
```

#### مثال لتوليد PDF مع رأس ثابت

يرجى مراجعة [هذا المثال](https://github.com/omaralalwi/Gpdf-Laravel-Demo/blob/0f041e7cf9030f48e2a35ce6d22e8fac5db98c48/app/Http/Controllers/GpdfController.php#L132C1-L133C1) إذا كنت بحاجة لإضافة رأس ثابت لجميع الصفحات.

### [تطبيق Laravel توضيحي](https://github.com/omaralalwi/Gpdf-Laravel-Demo)
يحتوي هذا التطبيق التوضيحي على أمثلة وحالات استخدام مفصلة.

---

## الاستخدام مع تطبيقات PHP التقليدية

بعد تثبيت الحزمة ونشر الموارد، قم بتضمين `autoload.php` واستخدم فئة `Gpdf`.

### الاستخدام الأساسي

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

**ملاحظة:** قم بتخصيص ملف الإعدادات حسب الحاجة.

### عرض ملفات PDF مباشرة

لعرض ملف PDF مباشرة باستخدام `generateWithStream`:

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

#### تخزين الملفات محلياً

لحفظ ملفات PDF على التخزين المحلي باستخدام `generateWithStore`:

**ملاحظة:** يتم تخزين الملفات محلياً بشكل افتراضي.
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
$file = $gpdf->generateWithStore($content, null, null, false, $sslVerify); // يجب تفعيل sslVerify في الإنتاج.
$fileUrl = $file['ObjectURL'];

return $fileUrl;  // يعيد عنوان URL للملف كسلسلة نصية لتخزينه أو استخدامه.
```

#### معايير generateWithStore

| المعامل                              | النوع   | الوصف                                                                                         |
|--------------------------------------|---------|-----------------------------------------------------------------------------------------------|
| `html file`                          | string  | المحتوى HTML الذي سيتم تحويله وتخزينه.                                                          |
| `مسار التخزين أو اسم الدلو لـ S3`     | string  | المسار الذي سيتم تخزين الملف فيه، ومع S3 يجب أن يكون اسم الدلو (bucket name).                  |
| `اسم الملف`                          | string  | اسم الملف المراد حفظه.                                                                         |
| `with stream`                        | bool    | إذا كنت ترغب في عرض الملف مباشرة بعد التخزين، قم بتعيين القيمة إلى `true`.                    |
| `sslVerify`                          | bool    | إذا تم تعيين `with stream` إلى `true`، يجب تعيين هذا إلى `true` في الإنتاج للتحقق من SSL.       |

#### تخزين الملفات على S3

بنفس طريقة التخزين المحلي، فقط استبدل مسار التخزين المحلي باسم الدلو، واستبدل `generateWithStore` بـ `generateWithStoreToS3`.

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
$file = $gpdf->generateWithStoreToS3($content, null, $fileName, true, $sslVerify);
$fileUrl = $file['ObjectURL'];
```

### [تطبيق PHP توضيحي](https://github.com/omaralalwi/Gpdf-Native-PHP-Demo)
يمكنك مراجعة هذا التطبيق التوضيحي للحصول على أمثلة مفصلة حول تمرير المعاملات الديناميكية للملفات HTML وتمرير الإعدادات مدمجة وغيرها من الحالات.

---

## الخطوط المدعومة

يدعم Gpdf الخطوط التالية المثبتة (جاهزة للاستخدام دون إعداد إضافي):

[الخطوط المدعومة](https://github.com/omaralalwi/Gpdf/blob/9e2342d43066169049bff5a72435e421f0b21daa/src/Enums/GpdfDefaultSupportedFonts.php)

## الدعم للغة العربية

يدعم Gpdf المحتوى العربي بشكل افتراضي. قم بتمرير النصوص العربية ضمن محتوى HTML الخاص بك. تأكد من استخدام الخطوط العربية، حيث أنها مدمجة بشكل افتراضي.

### الخطوط العربية المدعومة

تشمل الخطوط المدمجة التي تدعم اللغة العربية:

`DejaVu Sans Mono`، `Tajawal`، `Almarai`، `Cairo`، `Noto Naskh Arabic`، `Markazi Text`.

ننصح باستخدام اسم الخط من فئة `Omaralalwi\Gpdf\Enums\GpdfDefaultSupportedFonts` مثل "default font name" في ملف الإعدادات.

### أمثلة

- [مثال باستخدام PHP التقليدي](https://github.com/omaralalwi/Gpdf-Native-PHP-Demo/blob/master/generateArPdf.php)
- [مثال باستخدام Laravel](https://github.com/omaralalwi/Gpdf-Laravel-Demo/blob/c68bfbc84015d7eb0d3f473929cff488dc42ad9f/app/Http/Controllers/GpdfController.php#L74)

---

## تثبيت الخطوط المخصصة

لتثبيت خط مخصص، اتبع الخطوات التالية:

1. تأكد من نشر الخطوط الافتراضية إلى `public/vendor/gpdf/fonts`.
2. جهز على الأقل خطًا (Normal) لكل نمط من الأنماط (Normal, Bold, Italic, BoldItalic).
3. انسخ الخطوط إلى أي مسار **غير مسار الخطوط الافتراضي**.
4. يجب أن يكون اسم عائلة الخط محاطًا بعلامات اقتباس مزدوجة ومكتوبًا بأحرف صغيرة.
5. يجب أن تكون أسماء الخطوط بصيغة kebab case مع كتابة أول حرف كبير.
6. شغّل سكربت تثبيت الخط باستخدام الأمر التالي:

```bash
php vendor/omaralalwi/gpdf/scripts/install_font.php "اسم العائلة" ./path_to_font/Font-Normal.ttf ./path_to_font/Font-Bold.ttf ./resources/fonts/Tajawal-Italic.ttf ./path_to_font/Font-BoldItalic.ttf
```

على سبيل المثال، لتثبيت عائلة خط `Tajawal`:

```bash
php vendor/omaralalwi/gpdf/scripts/install_font.php "tajawal" ./resources/fonts/Tajawal-Normal.ttf ./resources/fonts/Tajawal-Bold.ttf ./resources/fonts/Tajawal-Italic.ttf ./resources/fonts/Tajawal-BoldItalic.ttf
```

---

## الميزات

- التوافق مع أي تطبيق PHP قياسي أو إطار عمل.
- إمكانية تخزين ملفات PDF على S3 أو على التخزين المحلي مباشرة.
- عرض ملفات PDF مباشرة من عناوين URL (محلي أو S3).
- دعم 17 خطاً بشكل افتراضي، بما في ذلك 7 خطوط تدعم اللغة العربية.
- إمكانية تثبيت الخطوط المخصصة.
- تكامل سهل مع تطبيقات Laravel.
- خيارات قابلة للتخصيص لتوليد ملفات PDF.
- وثائق مفصلة.
- تطبيقات توضيحية للبدء السريع: [تطبيق PHP توضيحي](https://github.com/omaralalwi/Gpdf-Native-PHP-Demo) و [تطبيق Laravel توضيحي](https://github.com/omaralalwi/Gpdf-Laravel-Demo).
- يحتوي على اختبارات وحدية.

---

## الشكر

- ### [dompdf](https://github.com/dompdf/dompdf)
- ### [Ar-PHP](https://github.com/khaled-alshamaa/ar-php)

## الاختبارات

```bash
composer test
```
أو
```bash
php run-tests.php
```

## سجل التغييرات

راجع [CHANGELOG](CHANGELOG.md) لمعرفة التغييرات الأخيرة.

## المساهمون ✨

شكر وتقدير لهؤلاء الرائعين الذين ساهموا في هذا المشروع! 💖

<table>
  <tr>
    <td align="center">
      <a href="https://github.com/omaralalwi">
        <img src="https://avatars.githubusercontent.com/u/25439498?v=4" width="100px;" alt="Omar Al Alwi"/>
        <br />
        <sub><b>Omar Al Alwi</b></sub>
      </a>
      <br />
      🏆 المالك
    </td>
    <!-- Contributors -->
    <td align="center">
      <a href="https://github.com/smahi">
        <img src="https://avatars.githubusercontent.com/u/1782133?v=4" width="100px;" alt="Contributor Name"/>
        <br />
        <sub><b>Abesse Smahi</b></sub>
      </a>
      <br />
      💻 المساهم
    </td>
  </tr>
</table>

هل ترغب في المساهمة؟ يرجى مراجعة [إرشادات المساهمة](./CONTRIBUTING.md) وإرسال طلب سحب! 🚀

## الأمان

إذا اكتشفت أي مشكلات تتعلق بالأمان، يرجى إرسال بريد إلكتروني إلى `omaralwi2010@gmail.com`.

## الاعتمادات

- [Omar Alalwi](https://github.com/omaralalwi)

## الترخيص

رخصة MIT. راجع [LICENSE](LICENSE.md) لمزيد من المعلومات.

---
# ✨ **GPDF Community** ✨

Click the button bellow or [join here](https://t.me/gpdf_community) to be part of our growing community!

[![Join Telegram](https://img.shields.io/badge/Join-Telegram-blue?style=for-the-badge&logo=telegram)](https://t.me/deepseek_php_community)
