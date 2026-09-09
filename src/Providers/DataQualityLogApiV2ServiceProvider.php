<?php

namespace DataQualityLogApiV2\Providers;

use Plenty\Plugin\ServiceProvider;

class DataQualityLogApiV2ServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->getApplication()->register(
            DataQualityLogApiV2RouteServiceProvider::class
        );
    }
}
