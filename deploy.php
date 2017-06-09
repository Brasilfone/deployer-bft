<?php
use function Deployer\{host, task, run, set, get, writeln, cd, fail};

/**
 * Define configurations general
 */
set('port', 222); // default
set('git_tty', true);
set('branch', 'master'); // branch pull
set('ssh_type', 'native'); // default
set('ssh_multiplexing', true); // default
set('senha', 'brasilfone'); // senha rsa
set('path_rsa', '~/.ssh/id_rsa'); // diretorio chave privada
set('path_rsa_pub', '~/.ssh/id_rsa.pub'); // diretorio chave publica
set('composer_update', 'php composer.phar update --verbose'); // comando atualizar pacotes composer
set('composer_install', 'php composer.phar install --verbose'); // comando instalar pacotes composer
set('path_git', '/to/path/'); // diretorio projeto github
set('user_senha_git', 'user:password'); // usuário e senha github
set('doctrine_status', 'vendor/bin/doctrine-module m:s'); // comando ver status das migrações
set('project', '@github.com/company/project.git'); // url projeto git HTTPS
set('doctrine_run', 'vendor/bin/doctrine-module m:m --no-interaction'); // --no-interaction (roda sem fazer interação com o usuário - Y/N) default - Y
set('path_raiz', '/var/www/html/'); // default

/*
 * Define the servers
 */
host('177.52.174.246')
    ->configFile('~/.ssh/config')
    ->forwardAgent(true)
    ->multiplexing(false) // default
    ->set('proccess_list', []) // array com os nomes dos processos para listar
    ->set('proccess_kill', []) // array com os nomes dos processos para matar
    ->set('deploy_path', get('path_git'))
    ->set('update', get('composer_update'))
    ->set('install', get('composer_install'))
    ->set('migration_run', get('doctrine_run'))
    ->set('migration_status', get('doctrine_status'))
    ->set('pull', 'git pull https://' . get('user_senha_git') . get('project') . ' ' . get('branch'));

/*
 * Pull the changes onto the server
 */
task('deploy:pull_changes', function () {
    writeln('------------------ GIT PULL ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("SERVER: " . $host);
    cd(get('deploy_path'));
    $pull = run(get('pull'));
    writeln($pull);
    writeln('------------------ FIM ---------------------------------');
})->desc('running pull changes on server');

/*
 * Run composer install on the server
 */
task('deploy:composer_install', function () {
    writeln('------------------ COMPOSER INSTALL ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("SERVER: " . $host);
    cd(get('deploy_path'));
    $composer = run(get('install'));
    writeln($composer);
    writeln('------------------ FIM ---------------------------------');
})->desc('running composer install');

/*
 * Run composer update on the server
 */
task('deploy:composer_update', function () {
    writeln('------------------ COMPOSER UPDATE ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("SERVER: " . $host);
    cd(get('deploy_path'));
    $composer = run(get('update'));
    writeln($composer);
    writeln('------------------ FIM ---------------------------------');
})->desc('running composer update');

/*
 * Run the migrations on the server
 */
task('deploy:run_migrations', function () {
    writeln('------------------ MIGRATIONS:MIGRATIONS ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("SERVER: " . $host);
    cd(get('deploy_path'));
    $migrations = run(get('migration_run'));
    writeln($migrations);
    writeln('------------------ FIM ---------------------------------');
})->desc('running migrations');

/*
 * Run the status migrations on the server
 */
task('deploy:run_migrations_status', function () {
    writeln('------------------ MIGRATIONS:STATUS ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("SERVER: " . $host);
    cd(get('deploy_path'));
    $migrations = run(get('migration_status'));
    writeln($migrations);
    writeln('------------------ FIM ---------------------------------');
})->desc('running status migrations');

/*
 * Run the kill process on the server
 */
task('deploy:kill_proccess', function () {
    writeln('------------------ KILL PROCCESS ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("SERVER: " . $host);
    if (!empty(get('proccess_kill')) && !empty(get('proccess_kill')[0])) {
        foreach (get('proccess_kill') as $proccess) {
            $contentProccess = run('ps aux | grep "[p]hp /var/www/html/' . get('deploy_path') . '/public/index.php ' . $proccess .'"');
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
    writeln('------------------ FIM ---------------------------------');
})->desc('running kill process');

/*
 * Run the list process on the server
 */
task('deploy:list_proccess', function () {
    writeln('------------------ LIST PROCCESS ------------------------------');
    $config = run('ifconfig');
    $host = substr($config, 88, 14);
    writeln("SERVER: " . $host);
    if (!empty(get('proccess_list')) && !empty(get('proccess_list')[0])) {
        foreach (get('proccess_list') as $proccess) {
            writeln('');
            $contentProccess = run('ps aux | grep "[p]hp /var/www/html/' . get('deploy_path') . '/public/index.php ' . $proccess .'"');
            writeln($contentProccess);
        }
    } else {
        writeln('');
        $contentProccess = run('ps aux | grep "[p]hp /var/www/html/' . get('deploy_path') . '/public/index.php"');
        writeln($contentProccess);
    }
    writeln('------------------ FIM ---------------------------------');
})->desc('running list process');

/*
 * Run the fail task on de server
 */
task('deploy:fail', function () {
    writeln('------------------ FAILED ------------------------------');
    writeln('Erro ao executar task!');
    writeln('------------------ FIM ---------------------------------');
    exit();
})->desc('running fail task');
