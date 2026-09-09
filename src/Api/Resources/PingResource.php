<?php

namespace DataQualityLogApiV2\Api\Resources;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Response;

class PingResource extends Controller
{
    /**
     * @var Response
     */
    private $response;

    public function __construct(
        Response $response
    ) {
        $this->response = $response;
    }

    /**
     * GET /rest/dq-v2/ping
     */
    public function index(): Response
    {
        return $this->response->make(
            json_encode(
                [
                    'ok' => true,
                    'plugin' => 'DataQualityLogApiV2',
                    'version' => '1.0.0',
                    'message' => 'pong'
                ]
            ),
            200,
            [
                'Content-Type' =>
                    'application/json; charset=UTF-8'
            ]
        );
    }
}
