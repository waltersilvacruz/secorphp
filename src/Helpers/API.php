<?php

namespace TCEMT\Helpers;


use Illuminate\Support\Facades\App;
use RestClient\Client;

class API
{

    public function recuperarToken() {
        $settings = App::make('config')->get('secorphp');
        $base64 = "Basic " . base64_encode($settings['api_consumer_key'] . ':' . $settings['api_consumer_secret']);
        $client = new Client([
            'base_url' => $settings['api_base_url'],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => $base64,
                'Cache-Control' => 'no-cache'
            ]
        ]);

        $data = 'grant_type=client_credentials';

        $api_request = $client->newRequest('/token', 'POST', $data);
        $api_response = $api_request->getResponse();
        $result = $api_response->getInfo();
        if ($result->http_code === 200) {
            $json = json_decode($api_response->getParsedResponse());
            return $json->access_token;
        } else {
            return null;
        }
    }
}
