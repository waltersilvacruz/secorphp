<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API environment
    |--------------------------------------------------------------------------
    | Informa qual é o ambiente da API
    */
    'environment' => env('SECORPHP_ENVIRONMENT', null),

    /*
    |--------------------------------------------------------------------------
    | API base URL
    |--------------------------------------------------------------------------
    | URL base da api
    */
    'api_base_url' => env('SECORPHP_API_BASE_URL', null),

    /*
    |--------------------------------------------------------------------------
    | API development Context
    |--------------------------------------------------------------------------
    | Contexto da api no ambiente de desenvolvimento
    */
    'api_development' => env('SECORPHP_API_DEV_CONTEXT', null),

    /*
    |--------------------------------------------------------------------------
    | API production Context
    |--------------------------------------------------------------------------
    | Contexto da api no ambiente de produção
    */
    'api_production' => env('SECORPHP_API_PROD_CONTEXT', null),

    /*
    |--------------------------------------------------------------------------
    | API Consumer Key
    |--------------------------------------------------------------------------
    | Chave de acesso pública da aplicação
    */
    'api_consumer_key' => env('SECORPHP_API_CONSUMER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | API Consumer Secret
    |--------------------------------------------------------------------------
    | Chave de acesso privada da aplicação
    */
    'api_consumer_secret' => env('SECORPHP_API_CONSUMER_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Auth User Model
    |--------------------------------------------------------------------------
    | Informa qual é o Model da tabela de usuários para autenticação.
    */
    'auth_model' => env('AUTH_USER_MODEL', null),

    /*
    |--------------------------------------------------------------------------
    | Auth User Login Field
    |--------------------------------------------------------------------------
    | Informa qual é o campo da tabela que contém o logon do usuário
    */
    'auth_login_field' => env('AUTH_USER_LOGIN_FIELD', 'login'),

    /*
    |--------------------------------------------------------------------------
    | Auth login route
    |--------------------------------------------------------------------------
    | Informa qual é a rota para a tela de login
    */
    'auth_login_route' => env('AUTH_LOGIN_ROUTE'),

    /*
    |--------------------------------------------------------------------------
    | Auth success route
    |--------------------------------------------------------------------------
    | Informa qual é a rota para redirecionamento quando o login for bem sucedido
    */
    'auth_login_success_route' => env('AUTH_LOGIN_SUCCESS_ROUTE'),

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    | Habilita/desabilita o middleware de verificação de credenciais
    */
    'secorphp_enabled' => env('SECORPHP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | App
    |--------------------------------------------------------------------------
    | Informe aqui o nome do sistema cadastrado no campo SIS_IDENTIFICACAO do
    | sistema de segurança do TCE
    */
    'secorphp_app' => env('SECORPHP_APP', null),


    /*
    |--------------------------------------------------------------------------
    | Cache Enabled
    |--------------------------------------------------------------------------
    | Determina se deve fazer o cache das permissões do Secorp
    */
    'secorphp_cache_enabled' => env('SECORPHP_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Timeout
    |--------------------------------------------------------------------------
    | Determina quanto tempo de duração do cache contendo as credenciais
    | recuperadas do banco. Esta ação visa reduzir o acesso ao banco de dados
    | de credenciais. Valores em MINUTOS.
    */
    'secorphp_cache_timeout' => env('SECORPHP_CACHE_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Mapeamento das rotas e processos
    |--------------------------------------------------------------------------
    | A chave do cabeçalho é o nome da rota e o valor é o nome do processo
    | (campo TABELA_PROCESSO do sistema de segurança). É possível utilizar "*"
    | para que o sistema mapeie várias rotas para o mesmo processo
    */
    'secorphp_rules' => [
        'home' => ['recurso' => 'HOME'],
        'dashboard.*' => ['recurso' => 'DASHBOARD'],
        'usuario.index' => ['recurso' => 'USUARIO', 'acao' => 'PODE_ACESSAR'],
        'usuario.incluir' => ['recurso' => 'USUARIO', 'acao' => 'PODE_INCLUIR'],
        'usuario.editar' => ['recurso' => 'USUARIO', 'acao' => 'PODE_ALTERAR'],
        'usuario.excluir' => ['recurso' => 'USUARIO', 'acao' => 'PODE_EXCLUIR']
    ],

    /*
    |--------------------------------------------------------------------------
    | Rotas ignoradas
    |--------------------------------------------------------------------------
    | Informe aqui as rotas que deseja ignorar (bypass). Não é possível colocar
    | "*" no nome das rotas.
    */
    'secorphp_ignore_routes' => [
        'logout',
    ],
];
