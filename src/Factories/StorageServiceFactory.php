<?php

namespace Omaralalwi\Gpdf\Factories;

use Omaralalwi\Gpdf\Enums\GpdfSettingKeys;
use Omaralalwi\Gpdf\Enums\GpdfStorageDrivers;
use Omaralalwi\Gpdf\Services\{S3Service, LocalFileService};
use Omaralalwi\Gpdf\Clients\S3Client;

class StorageServiceFactory
{
    public function getStorageService($driver, $gpdfConfig)
    {
        switch ($driver) {
            case GpdfStorageDrivers::S3:
                return new S3Service(new S3Client($gpdfConfig));
            case GpdfStorageDrivers::LOCAL:
            default:
                return new LocalFileService();
        }
    }
}
