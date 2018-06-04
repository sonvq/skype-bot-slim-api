<?php

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

/**
 * Class SkypeController
 * @package App\Controller
 */
class SkypeController
{
    protected $table;
    private $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response, $args)
    {
        $this->logger->info('index action of SkypeController called');
        $params = $request->getParsedBody();
        $text = $params['text'];
        $conversation = $params['conversation'];
        $bot = $params['recipient'];
        $user = $params['from'];
        $activity_id = $params['id'];
        $service_url = $params['serviceUrl'];

        $client_request_token = new Client();
        $access_token = json_decode($client_request_token->request(
            'POST',
            'https://login.microsoftonline.com/botframework.com/oauth2/v2.0/token',
            [
                'form_params' =>
                    [
                        'grant_type' => 'client_credentials',
                        'client_id' => 'bba0a69a-933c-498e-9771-c52176dd6ec0',
                        'client_secret' => 'zguxATJ92*|ftvQBPV865?)',
                        'scope' => 'https://api.botframework.com/.default',
                    ],
                'headers' =>
                    [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ]
            ]
        )->getBody()->getContents())->access_token;

        if ($text == "Hello" || $text == "hello") {
            $client_reply_message = new Client();
            $url = $service_url . '/v3/conversations/' . $conversation['id'] . '/activities/' . $activity_id;
            try {
                $client_reply_message->request(
                    'POST',
                    $url,
                    [
                        'json' =>
                            [
                                'type' => 'message',
                                'from' => [
                                    'id' => $bot['id'],
                                    'name' => $bot['name'],
                                ],
                                'conversation' => [
                                    'id' => $conversation['id'],
                                ],
                                'recipient' => [
                                    'id' => $user['id'],
                                    'name' => $user['name'],
                                ],
                                'text' => "Bye",
                                'replyToId' => $activity_id,
                            ],
                        'headers' =>
                            [
                                'Content-Type' => 'application/json',
                                'Authorization' => 'Bearer ' . $access_token
                            ]
                    ]
                );
            } catch (TransferException $e) {
                $this->logger->info(Psr7\str($e->getRequest()));
                if ($e->hasResponse()) {
                    $this->logger->info(Psr7\str($e->getResponse()));
                }
            }
        }
        $result = $response->withJson(['message' => 'ok'], 200);

        return $result;

    }
}
