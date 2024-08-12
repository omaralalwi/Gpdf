<?php

namespace Omaralalwi\Gpdf\Enums;

class GpdfSettingKeys
{
    const TEMP_DIR = 'tempDir';
    const FONT_DIR = 'fontDir';
    const FONT_CACHE = 'fontCache';
    const CHROOT = 'chroot';
    const STORAGE_PATH = 'storage_path';
    const AWS_BUCKET = 'aws_storage_bucket';
    const AWS_REGION = 'aws_storage_region';
    const AWS_KEY = 'aws_storage_key';
    const AWS_SECRET = 'aws_storage_secret';
    const CONVERT_ENTITIES = 'convert_entities';
    const ALLOWED_PROTOCOLS = 'allowedProtocols';
    const ARTIFACT_PATH_VALIDATION = 'artifactPathValidation';
    const LOG_OUTPUT_FILE = 'logOutputFile';
    const DEFAULT_MEDIA_TYPE = 'defaultMediaType';
    const DEFAULT_PAPER_SIZE = 'defaultPaperSize';
    const DEFAULT_PAPER_ORIENTATION = 'defaultPaperOrientation';
    const DEFAULT_FONT = 'defaultFont';
    const DPI = 'dpi';
    const ENABLE_PHP = 'enable_php';
    const FONT_HEIGHT_RATIO = 'fontHeightRatio';
    const IS_PHP_ENABLED = 'isPhpEnabled';
    const IS_REMOTE_ENABLED = 'isRemoteEnabled';
    const ALLOWED_REMOTE_HOSTS = 'allowedRemoteHosts';
    const IS_JAVASCRIPT_ENABLED = 'isJavascriptEnabled';
    const IS_HTML5_PARSER_ENABLED = 'isHtml5ParserEnabled';
    const IS_FONT_SUB_SETTING_ENABLED = 'isFontSubsettingEnabled';
    const DEBUG_PNG = 'debugPng';
    const DEBUG_KEEP_TEMP = 'debugKeepTemp';
    const DEBUG_CSS = 'debugCss';
    const DEBUG_LAYOUT = 'debugLayout';
    const DEBUG_LAYOUT_LINES = 'debugLayoutLines';
    const DEBUG_LAYOUT_BLOCKS = 'debugLayoutBlocks';
    const DEBUG_LAYOUT_INLINE = 'debugLayoutInline';
    const DEBUG_LAYOUT_PADDING_BOX = 'debugLayoutPaddingBox';
    const PDF_BACKEND = 'pdfBackend';
    const PDF_LIB_LICENSE = 'pdflibLicense';
    const HTTP_CONTEXT = 'httpContext';

    const UTF8GLYPHS_MAX_CHARS = 'utf8GlyphsMaxChars';
    const UTF8GLYPHS_HINDO = 'utf8GlyphsHindo';
    const UTF8GLYPHS_FORCERTL = 'utf8GlyphsForceRtl';

    /*
     * output of the following function must be array like this
        'tempDir' => 'tempDir',
        'fontDir' => 'fontDir',
     *
    */
    public static function asArray(): array
    {
        $reflectionClass = new \ReflectionClass(__CLASS__);
        $constants = $reflectionClass->getConstants();
        $result = [];

        foreach ($constants as $key => $value) {
            $result[$value] = $value;
        }

        return $result;
    }

    public static function asObject(): \stdClass
    {
        $keysObject = new \stdClass();
        $reflectionClass = new \ReflectionClass(__CLASS__);
        foreach ($reflectionClass->getConstants() as $key => $value) {
            $keysObject->$key = $value;
        }
        return $keysObject;
    }

}
