<?php

namespace Omaralalwi\Gpdf\Clients;

use Omaralalwi\Gpdf\Enums\GpdfSettingKeys;
use Omaralalwi\Gpdf\GpdfConfig;

class S3Client
{
    protected $gpdfConfig;

    public function __construct(GpdfConfig $gpdfConfig)
    {
        $this->gpdfConfig = $gpdfConfig;
    }

    public function initilizeClient()
    {
        return new \Aws\S3\S3Client([
            'region'  => $this->gpdfConfig->get(GpdfSettingKeys::AWS_REGION),
            'credentials' => [
                'key'    => $this->gpdfConfig->get(GpdfSettingKeys::AWS_KEY),
                'secret' => $this->gpdfConfig->get(GpdfSettingKeys::AWS_SECRET),
            ],
        ]);
    }

    public function getBucket()
    {
        return $this->gpdfConfig->get(GpdfSettingKeys::AWS_BUCKET);
    }
}
