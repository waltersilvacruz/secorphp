TCEMT SECORPHP
==============

# Instalação

### Requerimentos
- PHP 8.2+
- Laravel 11.0+


Instale o componente via comando do composer:
```
composer require waltersilvacruz/secorphp
```

### Compatibilidade com versões antigas do Laravel:
para versões anteriores do Laravel (versões 7, 8, 9 ou 10), utilize a versão 3 do SECORPHP:

```
composer require waltersilvacruz/secorphp:^3
```

# Configuração

``` 
*ATENÇÃO*: este tutorial de configuração é específico para a versão 4 do SECORPHP, 
que é compatível com a versão 11 ou superior do Laravel. Para versões anteriores, é 
recomendado utilizar as instruções no README da versão.
```
Abra o arquivo `bootstrap/providers.php` e adicione na lista de providers:
```
TCEMT\Providers\SecorphpServiceProvider::class
```

No arquivo `bootstrap/app.php` adicione à lista de aliases:
```
'Secorphp'  => TCEMT\Facades\Secorphp::class

// exemplo:
...
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'Secorphp'  => TCEMT\Facades\Secorphp::class
    ]);
})
...
```

limpe o cache de configurações
```
php artisan config:cache
```

Execute o comando abaixo para criar o arquivo de configuração:
```
php artisan vendor:publish --tag="secorphp"
```

Edite o arquivo .env e adicione a configuração básicas para o componente de segurança:
```
# configurações de autenticação
AUTH_LOGIN_ROUTE=login # rota da página de logon na aplicação
AUTH_LOGIN_SUCCESS_ROUTE=home # rota de redirecionamento  quando o logon for bem sucedido
AUTH_USER_MODEL=App\User # modelo do usuário
AUTH_USER_LOGIN_FIELD=logon # campo referente ao logon do usuário

#configuração de autorização do Secorp
SECORPHP_ENVIRONMENT=production/development # define o ambiente
SECORPHP_API_BASE_URL=https://am.tce.mt.gov.br # url base do API Manager
SECORPHP_API_PROD_CONTEXT=/secorp/authorization/producao #contexto no ambiente de produção
SECORPHP_API_DEV_CONTEXT=/secorp/authorization/desenvolvimento # contexto no ambiente de desenvolvimento
SECORPHP_API_CONSUMER_KEY=chave # chave pública do sistema (fornecida pelo API Manager)
SECORPHP_API_CONSUMER_SECRET=chave # chave secreta do sistema (fornecida pelo API Manager)
SECORPHP_ENABLED=true/false # habilita ou desabilita a verificação de segurança
SECORPHP_APP=app_name # nome da aplicação cadastrada no Secorp
SECORPHP_CACHE_ENABLED=true/false # habilita ou desabilita o cache das credenciais de acesso
SECORPHP_CACHE_TIMEOUT=30 # tempo em minutos
```

limpe o cache de configurações novamente
```
php artisan config:cache
```

Edite seu arquivo app/Http/routes.php e utilize o novo controller para lidar com o precesso de autenticação. Exemplo:
```
Route::post('/login', '\TCEMT\Http\Controllers\AuthLdapController@autentica')->name('autentica');
```

Execute os comandos abaixo:
```
php artisan clear-compiled && composer dumpautoload && php artisan optimize
```

# Utilização

## Dentro de um Controller

Utilize o facade `Secorphp` para verificar as permissões dos usuários no controller.
O método `Secorphp::allow($recurso[,$acao])` se encarrega de fazer a verificação e retorna verdadeiro ou falso.
O primeiro parâmetro é o RECURSO, e o segundo a AÇÃO (opcional):
```
<?php
...
use Secorphp;

class MeuController extends Controller {

    public function index() {
        // verifica acesso do usuário que está logado ao recurso
        if(Secorphp::allow('USUARIO') {
            // tem acesso ao recurso "USUARIO"...
        }

        // verifica acesso do usuário que está logado à uma ação
        if(Secorphp::allow('USUARIO', 'PODE_EDITAR') {
            // tem acesso à ação "EDITAR" no recurso "USUARIO" ...
        }
    }
}
```

Por padrão o `Secorphp` verifica as permissões de acesso do usuário autenticado. Caso necessite verificar a permissão de um outro usuário você pode fazer isso:
```
<?php
...
use Secorphp;

class MeuController extends Controller {

    public function index() {
        // ou utilize o método user()
        if(Secorphp::user('mary')->allow('USUARIO') {
            // Mary tem acesso ao recurso "USUARIO"...
        }
    }
}
```

## Uso em templates do Blade

O `Secorphp` conta com implementa duas diretivas para fazer a verificação de permissão: `@recurso`, `@if_recurso`, `@acao` e `@if_acao`. Veja os exemplos:
```
<h1>Pode acessar video?</h1>
@if_recurso("VIDEO")
<strong>Pode sim!</strong>
@else
<strong>ACESSO NEGADO!!</strong>
@endif_recurso

<h1>Pode incluir materia?</h1>
@if_acao("MATERIA", "PODE_INCLUIR")
<strong>Claro que pode!</strong>
@else
<strong>ACESSO NEGADO!!</strong>
@endif_acao

<button type="submit" @acao("MATERIA","PODE_INCLUIR")>Incluir</button>
<button type="button" @recurso("WEBDISCO")>Ir para webdisco</button>
```

## Verificação por rota (routes)

É possível mapear as rotas da sua aplicação com os um recursos e ações do sistema Secorp. As configurações ficam no arquivo `config/secorphp.php`. Veja um exemplo de como fazer o mapeamento:
```
'secorphp_rules' => [
    'home' => ['recurso' => 'HOME'],
    'dashboard.*' => ['recurso' => 'DASHBOARD'],
    'usuario.index' => ['recurso' => 'USUARIO', 'acao' => 'PODE_ACESSAR'],
    'usuario.incluir' => ['recurso' => 'USUARIO', 'acao' => 'PODE_INCLUIR'],
    'usuario.editar' => ['recurso' => 'USUARIO', 'acao' => 'PODE_ALTERAR'],
    'usuario.excluir' => ['recurso' => 'USUARIO', 'acao' => 'PODE_EXCLUIR']
],
```

Como você pode notar é possível utilizar `*` no mapeamento das rotas:
```
// a rota exata 'home' está mapeada para o recurso 'HOME' independente do usuário ter ou não acesso às ações do recurso
'home' => ['recurso' => 'HOME']

// todas as rotas que começam com 'dashboard.' estão mapeadas para o recurso 'DASHBOARD'
'dashboard.*' => ['recurso' => 'DASHBOARD'],
```
