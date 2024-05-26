<?php

namespace Omaralalwi\Gpdf\Factories;

use Dompdf\Dompdf;
use Dompdf\Options;
use Omaralalwi\Gpdf\GpdfConfig;
use DateTime;
use dompdf\FontMetrics;
use Dompdf\Cpdf;
use Omaralalwi\Gpdf\Enums\{GpdfDefaultSettings, GpdfSettingKeys, GpdfDefaultSupportedFonts};

class DompdfFactory
{
    public static function create(GpdfConfig $gpdfConfig): Dompdf
    {
        $enumKeyMap = GpdfSettingKeys::asArray();
        $options = new Options();

        foreach ($gpdfConfig->getAll() as $key => $val) {
            if (array_key_exists($key, $enumKeyMap)) {
                $options->set($enumKeyMap[$key], $val);
            }
        }

        return new Dompdf($options);
    }

    public static function generateFileName($fileName)
    {
        if(!empty($fileName)) {
            return $fileName.'.pdf';
        }

        $dateTime = new DateTime();
        $formattedDate = $dateTime->format('Y-m-d_H-i-s');
        return "gpdf_{$formattedDate}.pdf";
    }

}
