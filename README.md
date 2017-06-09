# Deploy automatizado BFT Desenvolvimento

## Instalação
- Adicionar no composer.json o pacote `deployer/deployer`
```
"require": {
    "deployer/deployer": "*"
}
```
- Instalar o composer ou atualizar
```
php composer.phar install
```
```
php composer.phar update
```
- Depois dentro do diretório raiz executar o seguinte comando para iniciar o `dep`
```
dep init
```
## Configuração
- Criar um arquivo dentro do diretório raiz chamado `deploy.php`
-- Nesse arquivo serão criadas as `tasks` (tarefas) que serão executadas com o dep;
- Para começar o desenvolvimento, dentro do arquivo `deploy.php` é preciso importar a biblioteca do `deployer`, com o seguinte comando:

```
	<?php
	use function Deployer\{host, task, run, set, get, writeln, cd};

	...
```
- Criando uma `task`:
```
	<?php
	task('deploy:test', function () {
	    writeln('Hello world');
	});
```
- Executando uma `task` pelo terminal:
```
	dep deploy:test
```
- A saída será algo:
```
	➤ Executing task test
	Hello world
	✔ Ok
```
- Criando `hosts`
```
	<?php
	host('domain.com')
	    ->stage('production')
	    ->set('deploy_path', '/var/www/domain.com');
```
- Criação de variáveis globais
```
	<?php
	set('nome_variavel', 'valor_variavel');
```
- Pegar o conteúdo das variáveis globais
```
	<?php
	get('nome_variavel');
```
- Tipos de saida de conteudo -v|-vv|-vvv
```
	dep deploy:teste -v
```
- Executando apenas um host
```
	dep deploy:teste --hosts 199.99.199.99
```
- Conectando via Ssh
```
	dep ssh
```
## Aplicando
- Criação da estrutura geral
```
<?php
use function Deployer\{host, task, run, set, get, writeln, cd};

/**
 * Define configurations general
 */
set('port', 222); // default
set('git_tty', true); // default
set('branch', 'master'); // branch pull default
set('ssh_type', 'native'); // default
set('ssh_multiplexing', true); // default
set('senha', 'senha_id_rsa_local'); // senha rsa
set('path_rsa', '~/.ssh/id_rsa'); // diretorio chave privada
set('path_rsa_pub', '~/.ssh/id_rsa.pub'); // diretorio chave publica
set('composer_update', 'php composer.phar update --verbose'); // comando atualizar pacotes composer
set('composer_install', 'php composer.phar install --verbose'); // comando instalar pacotes composer
set('path_git', '/var/www/html/projeto_git/'); // diretorio projeto github
set('user_senha_git', 'usuario:senha'); // usuário e senha github
set('doctrine_status', 'vendor/bin/doctrine-module m:s'); // comando ver status das migrações
set('project', '@github.com/Name/projeto.git'); // url projeto git HTTPS
set('doctrine_run', 'vendor/bin/doctrine-module m:m --no-interaction'); // --no-interaction (roda sem fazer interação com o usuário - Y/N) default

/*
 * Define the servers
 */
host('999.99.999.999')
    ->user('user') // usuario servidor
    ->port(get('port'))
    ->multiplexing(false) // default
    ->set('proccess_list', ['atualiza mo smstools']) // array com os nomes dos processos para listar
    ->set('proccess_kill', []) // array com os nomes dos processos para matar
    ->set('deploy_path', get('path_git'))
    ->set('update', get('composer_update'))
    ->set('install', get('composer_install'))
    ->set('migration_run', get('doctrine_run'))
    ->set('migration_status', get('doctrine_status'))
    ->identityFile(get('path_rsa'), get('path_rsa_pub'), get('senha'))
    ->set('pull', 'git pull https://' . get('user_senha_git') . get('project') . ' ' . get('branch'));

/*
 * Pull the changes onto the server
 */
task('deploy:pull_changes', function () {
    writeln('------------------ INICIO ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("HOST: " . $host);
    cd(get('deploy_path'));
    $pull = run(get('pull'));
    writeln($pull);
    writeln('------------------ END ---------------------------------');
})->desc('running pull changes on server');

/*
 * Run composer install on the server
 */
task('deploy:composer_install', function () {
    writeln('------------------ INICIO ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("HOST: " . $host);
    cd(get('deploy_path'));
    $composer = run(get('install'));
    writeln($composer);
    writeln('------------------ END ---------------------------------');
})->desc('running composer install');

/*
 * Run composer update on the server
 */
task('deploy:composer_update', function () {
    writeln('------------------ INICIO ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("HOST: " . $host);
    cd(get('deploy_path'));
    $composer = run(get('update'));
    writeln($composer);
    writeln('------------------ END ---------------------------------');
})->desc('running composer update');

/*
 * Run the migrations on the server
 */
task('deploy:run_migrations', function () {
    writeln('------------------ INICIO ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("HOST: " . $host);
    cd(get('deploy_path'));
    $migrations = run(get('migration_run'));
    writeln($migrations);
    writeln('------------------ END ---------------------------------');
})->desc('running migrations');

/*
 * Run the status migrations on the server
 */
task('deploy:run_migrations_status', function () {
    writeln('------------------ INICIO ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("HOST: " . $host);
    cd(get('deploy_path'));
    $migrations = run(get('migration_status'));
    writeln($migrations);
    writeln('------------------ END ---------------------------------');
})->desc('running status migrations');

/*
 * Run the kill process on the server
 */
task('deploy:kill_process', function () {
    writeln('------------------ INICIO ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("HOST: " . $host);
    if (!empty(get('proccess_kill')) && !empty(get('proccess_kill')[0])) {
        foreach (get('proccess_kill') as $proccess) {
            $contentProccess = run('ps aux | grep "[p]hp /var/www/html/gateway-smpp-jasmin/public/index.php ' . $proccess .'"');
            $proccessPid = array_values(array_filter(explode(" ", $contentProccess)));
            writeln($proccessPid[1]);
            $kill = run("kill -9 $proccessPid[1] > /dev/null 2>&1 &");
            writeln($kill);
            writeln('');
            writeln("O Processo '$proccess' foi finalizado. PID: $proccessPid[1]");
        }
    } else {
        writeln('');
        writeln("Nenhum processo selecionado.");
    }
    writeln('------------------ END ---------------------------------');
})->desc('running kill process');

/*
 * Run the list process on the server
 */
task('deploy:list_process', function () {
    writeln('------------------ INICIO ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("HOST: " . $host);
    if (!empty(get('proccess_list')) && !empty(get('proccess_list')[0])) {
        foreach (get('proccess_list') as $proccess) {
            writeln('');
            $contentProccess = run('ps aux | grep "[p]hp /var/www/html/gateway-smpp-jasmin/public/index.php ' . $proccess .'"');
            writeln($contentProccess);
        }
    } else {
        writeln('');
        $contentProccess = run('ps aux | grep "[p]hp /var/www/html/gateway-smpp-jasmin/public/index.php"');
        writeln($contentProccess);
    }
    writeln('------------------ END ---------------------------------');
})->desc('running list process');

/**
 * Run task fail when failed others task.
 */
fail('deploy:kill_process', 'deploy:fail');
fail('deploy:list_process', 'deploy:fail');
fail('deploy:pull_changes', 'deploy:fail');
fail('deploy:run_migrations', 'deploy:fail');
fail('deploy:composer_update', 'deploy:fail');
fail('deploy:composer_install', 'deploy:fail');
fail('deploy:run_migrations_status', 'deploy:fail');

```
