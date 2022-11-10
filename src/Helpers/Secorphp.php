<?php

namespace TCEMT\Helpers;

use RestClient\Client;
use App;
use Auth;
use Cache;

class Secorphp {


    private $logon;
    private $settings;

    public function __construct($logon = null) {
        $login_field = env('AUTH_USER_LOGIN_FIELD', 'logon');
        $this->logon = $logon ? $logon : Auth::user()->$login_field;
    }

    public function user($logon) {
        $this->logon = $logon;
        return $this;
    }

    public function allow($recurso, $acao = null) {

        $this->settings = App::make('config')->get('secorphp');

        if (!$this->settings['secorphp_enabled']) {
            return true;
        }

        $cache_key = 'secorphp_' . strtolower($this->logon);
        if(!Cache::has($cache_key)) {
            Cache::put($cache_key, $this->getCredenciais(), $this->settings['secorphp_cache_timeout']);
        }

        $perms = Cache::get($cache_key);

        if(!is_array($perms)) {
            return false;
        }

        if($acao) {
            return in_array($recurso . '|' . $acao, $perms['acoes']);
        } else {
            return in_array($recurso, $perms['recursos']);
        }
    }

    private function getCredenciais() {
        try {
            // produção ou desenvolvimento?
            $contexto = ($this->settings['environment'] == 'development' ? $this->settings['api_development'] : $this->settings['api_production']);
            $api_url = $this->settings['api_base_url'] . $contexto;

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

            $path = '/usuario/' . $this->logon . '/' . $this->settings['secorphp_app'] . '/permissoes';

            $api_request = $client->newRequest($path, 'GET');
            $api_response = $api_request->getResponse();
            $result = json_decode($api_response->getParsedResponse());
            $info = $api_response->getInfo();

            if($info->http_code == 200) {
                $acoes = [];
                $recursos = [];
                if(!isset($result->perfis)) {
                    return false;
                }
                foreach($result->perfis as $perfil) {
                    if(isset($perfil->grupos)) {
                        foreach($perfil->grupos as $grupo) {
                            foreach($grupo->recursos as $recurso) {
                                if(!in_array($recurso->nome, $recursos)) {
                                    array_push($recursos, $recurso->nome);
                                }
                                if(isset($recurso->acoes)) {
                                    foreach($recurso->acoes as $acao) {
                                        if(!in_array($recurso->nome . '|' . $acao->nome, $acoes)) {
                                            array_push($acoes, $recurso->nome . '|' . $acao->nome);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if(isset($perfil->recursos)) {
                        foreach($perfil->recursos as $recurso) {
                            if(!in_array($recurso->nome, $recursos)) {
                                array_push($recursos, $recurso->nome);
                            }
                            if(isset($recurso->acoes)) {
                                foreach($recurso->acoes as $acao) {
                                    if(!in_array($recurso->nome . '|' . $acao->nome, $acoes)) {
                                        array_push($acoes, $recurso->nome . '|' . $acao->nome);
                                    }
                                }
                            }
                        }
                    }
                }
                return ['recursos' => $recursos, 'acoes' => $acoes];
            } else {
                return false;
            }
        } catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
