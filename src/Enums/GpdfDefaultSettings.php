<?php

namespace Omaralalwi\Gpdf\Enums;

class GpdfDefaultSettings
{
    const TEMP_DIR = null;
    // note: last slash after fonts cleared in config file because real{ath function , so you must add it in config file
    const FONT_DIR = '/../public/vendor/gpdf/fonts';
    const FONT_CACHE = '/../public/vendor/gpdf/fonts'; // make it same FONT_DIR to avoid cache problems
    const CHROOT = null;
    const CONVERT_ENTITIES = true;
    const ALLOWED_PROTOCOLS = ['http', 'https'];
    const ARTIFACT_PATH_VALIDATION = true;
    const LOG_OUTPUT_FILE = null;
    const STORAGE_PATH = '/public/downloads/pdfs/';
    const DEFAULT_MEDIA_TYPE = 'screen';
    const DEFAULT_PAPER_SIZE = 'letter';
    const DEFAULT_PAPER_ORIENTATION = 'portrait';
    const DEFAULT_FONT = 'tajawal';
    const DPI = 96;
    const FONT_HEIGHT_RATIO = 1.1;
    const IS_PHP_ENABLED = false;
    const IS_REMOTE_ENABLED = true;
    const ALLOWED_REMOTE_HOSTS = [];
    const IS_JAVASCRIPT_ENABLED = false;
    const IS_HTML5_PARSER_ENABLED = false;
    const IS_FONT_SUB_SETTING_ENABLED = true;
    const DEBUG_PNG = false;
    const DEBUG_KEEP_TEMP = false;
    const DEBUG_CSS = false;
    const DEBUG_LAYOUT = false;
    const DEBUG_LAYOUT_LINES = false;
    const DEBUG_LAYOUT_BLOCKS = false;
    const DEBUG_LAYOUT_INLINE = false;
    const DEBUG_LAYOUT_PADDING_BOX = false;
    const PDF_BACKEND = 'CPDF';
    const PDF_LIB_LICENSE = '';
    const HTTP_CONTEXT = null;

    const UTF8GLYPHS_MAX_CHARS = 50;
    const UTF8GLYPHS_HINDO = true;
    const UTF8GLYPHS_FORCERTL = false;
}
