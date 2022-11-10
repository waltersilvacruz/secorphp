<?php

namespace TCEMT\Http\Controllers;

use Illuminate\Http\Request;
use RestClient\Client;
use App;
use App\Http\Controllers\Controller;
use Session;
use TCEMT\Helpers\API;
use Validator;
use Illuminate\Support\Facades\Auth;

class AuthLdapController extends Controller
{

    /**
     * Handle an authentication attempt.
     *
     * @return Response
     */
    public function autentica(Request $request) {
        $settings = App::make('config')->get('secorphp');

        $senha = $request->input('senha');
        $login = $request->input('login');

        $validator = Validator::make(
            array('senha' => $senha,'login' => $login),
            array('senha' => 'required|min:3', 'login' => 'required'),
            array('required' => ':attribute é obrigatório','min' => ':attribute deve ter ao menos :min caracteres')
        );
        if ($validator->fails()) {
            return redirect(route($settings['auth_login_route']))->withErrors($validator)->withInput();
        } else {
            // produção ou desenvolvimento?
            $contexto = ($settings['environment'] == 'development' ? $settings['api_development'] : $settings['api_production']);
            $api_url = $settings['api_base_url'] . $contexto;

            try {

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
                $data = json_encode([
                    'host' => '127.0.0.1',
                    'login' => $login,
                    'senha' => $senha,
                    'sistema' => $settings['secorphp_app']
                ]);

                $api_request = $client->newRequest('/sessao/autenticar', 'POST', $data);
                $api_response = $api_request->getResponse();
                $result = $api_response->getInfo();

                if ($result->http_code === 200) {
                    $model = $settings['auth_model'];
                    $login_field = $settings['auth_login_field'];
                    $usuario = $model::where($login_field, strtoupper($login))->first();
                    if(!$usuario) {
                        $validator->errors()->add('senha', 'Usuário não localizado na base de dados do SIGP');
                        return redirect(route($settings['auth_login_route']))->withErrors($validator)->withInput();
                    }
                    Auth::login($usuario);
                    return redirect(route($settings['auth_login_success_route']));
                } else if($result->http_code === 403) {
                    $validator->errors()->add('senha', 'Login ou senha inválida');
                    return redirect(route($settings['auth_login_route']))->withErrors($validator)->withInput();
                } else {
                    $validator->errors()->add('senha', 'Erro desconhecido retornado pela API de Autenticação');
                    return redirect(route($settings['auth_login_route']))->withErrors($validator)->withInput();
                }
            } catch (Exception $ex) {
                $validator->errors()->add('senha', 'Ocorreu um erro durante a autenticação!');
                return redirect(route($settings['auth_login_route']))->withErrors($validator)->withInput();
            }
        }
    }

    public function logout() {
        $settings = App::make('config')->get('secorphp');
        Session::flush();
        return redirect(route($settings['auth_login_route']));
    }
}
