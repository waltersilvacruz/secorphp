<?php

namespace TCEMT\Http\Middleware;

use RestClient\Client;
use App;
use Closure;
use Route;
use TCEMT\Helpers\API;

class Secorphp {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        $rota = Route::currentRouteName();
        $processo = null;

        $settings = App::make('config')->get('secorphp');

        if (!$request->user() || !$settings['secorphp_enabled'] || in_array($rota, $settings['secorphp_ignore_routes'])) {
            return $next($request);
        }

        if (array_key_exists($rota, $settings['secorphp_rules'])) {
            $processo = $settings['secorphp_rules'][$rota];
        } else {
            foreach ($settings['secorphp_rules'] as $route => $proc) {
                if (substr($route, -2) == '.*') {
                    $tmp_route = explode('.*', $route);
                    if (strstr($rota, $tmp_route[0])) {
                        $processo = $proc;
                    }
                }
            }
        }

        if(!$processo) {
            App::abort(403, 'Acesso negado (rota de acesso não configurada)!');
        }

        try {
            $login = strtolower($request->user()->logon);

            // produção ou desenvolvimento?
            $contexto = ($settings['environment'] == 'development' ? $settings['api_development'] : $settings['api_production']);
            $api_url = $settings['api_base_url'] . $contexto;

            $API = new API();
            $token = $API->recuperarToken();

            $client = new Client([
                'base_url' => $api_url,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            $path = '/usuario/' . $login . '/' . $settings['secorphp_app'] . '/' . $processo['recurso'];
            if(isset($processo['acao'])) {
                $path .= '/' . $processo['acao'] . '/podeAcessarAcao';
            } else {
                $path .= '/podeAcessarRecurso';
            }

            $api_request = $client->newRequest($path, 'GET');
            $api_response = $api_request->getResponse();
            $result = $api_response->getInfo();

            switch($result->http_code) {
                case 200:
                    if($api_response->getParsedResponse() == 'true') {
                        return $next($request);
                    } else {
                        App::abort(403, 'Você não tem permissão para acessar este recurso ou ação!');
                    }
                    break;
                case 403:
                    App::abort(403, 'Acesso negado! ');
                    break;
                default:
                    App::abort($result->http_code);
                    break;
            }
        } catch(Exception $ex) {
            App::abort(500, $ex->getMessage());
        }
    }
}
