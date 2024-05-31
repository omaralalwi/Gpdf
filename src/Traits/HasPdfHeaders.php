<?php

namespace Omaralalwi\Gpdf\Traits;

trait HasPdfHeaders
{
    /**
     * Set headers for streaming PDF files.
     *
     * @param string $fileUrl
     */
    public static function setPdfHeaders(string $fileUrl): void
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fileUrl . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
    }
}
