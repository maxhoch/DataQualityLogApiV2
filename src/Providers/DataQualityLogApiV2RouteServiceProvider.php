<?php

namespace DataQualityLogApiV2\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\ApiRouter;
use Plenty\Plugin\Routing\Router;

class DataQualityLogApiV2RouteServiceProvider extends RouteServiceProvider
{
    public function map(Router $router, ApiRouter $api)
    {
        $api->version(
            ['v1'],
            [
                'middleware' => ['oauth'],
                'namespace' => 'DataQualityLogApiV2\\Api\\Resources'
            ],
            function (ApiRouter $api) {
                $api->get(
                    'dq-v2/ping',
                    'PingResource@index'
                );
            }
        );
    }
}
