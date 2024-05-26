<?php

namespace Omaralalwi\Gpdf\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed generate(string $content)
 * @method static mixed generateWithStream(string $content, string $fileName = null, bool $attachment = false)
 * @method static mixed generateWithStore(string $content, string $filePath = null, string $fileName = null)
 */
class Gpdf extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return \Omaralalwi\Gpdf\Gpdf::class;
    }
}
