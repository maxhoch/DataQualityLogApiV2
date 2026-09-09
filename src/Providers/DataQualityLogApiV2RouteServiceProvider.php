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

                /*
                 * Health check
                 */
                $api->get(
                    'dq-v2/ping',
                    'PingResource@index'
                );

                /*
                 * Plenty log access
                 */
                $api->get(
                    'dq-v2/logs',
                    'LogResource@index'
                );

                $api->post(
                    'dq-v2/logs/search',
                    'LogResource@search'
                );

                $api->get(
                    'dq-v2/logs/{id}',
                    'LogResource@show'
                );
            }
        );
    }
}
