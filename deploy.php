<?php
/* Tool for deploy cscart */

require 'yamlconfig.cls.php';

/* Default arguments and options. */
argument('stage', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Run tasks only on this server or group of servers.');

set('config','config.yml');

set('default_stage', 'development');

$defaultConfig = new YamlConfig(get('config'),'default');

/* Environments */
env('release_name',$defaultConfig->getReleaseName());
env('www_user','apache');
env('www_group','apache');

/* Servers */

foreach ($defaultConfig->getServerList() as $server) {
  server($server['name'], $server['host'])
    ->user($server['user'])
    ->password($server['password'])
    ->stage($server['stage'])
    ->env('deploy_path', $server['deploy_path'])
    ->env('dbhost',$server['dbhost'])
    ->env('dbuser',$server['dbuser'])
    ->env('dbpwd',$server['dbpwd'])
    ->env('dbname',$server['dbname']);
}

task('prepare',function(){
  $stage = 'development';
  if (input()->hasArgument('stage') && input()->getArgument('stage') != '') {
      $stage = input()->getArgument('stage');
  }
  runLocally("mkdir -p ./deploy");
  $devConfig = new YamlConfig(get('config'),$stage);
  $releaseArchive = env()->parse("release-{{release_name}}.zip");
  $devConfig->zipDir("./deploy/$releaseArchive");
})->desc('Prepare release from project directory');

/* Preparing server for deployment. */
task('deploy:prepare', function () {
    \Deployer\Task\Context::get()->getServer()->connect();
    // Check if shell is POSIX-compliant
    try {
        cd(''); // To run command as raw.
        $result = run('echo $0')->toString();
        if ($result == 'stdin: is not a tty') {
            throw new RuntimeException(
                "Looks like ssh inside another ssh.\n" .
                "Help: http://goo.gl/gsdLt9"
            );
        }
    } catch (\RuntimeException $e) {
        $formatter = \Deployer\Deployer::get()->getHelper('formatter');
        $errorMessage = [
            "Shell on your server is not POSIX-compliant. Please change to sh, bash or similar.",
            "Usually, you can change your shell to bash by running: chsh -s /bin/bash",
        ];
        write($formatter->formatBlock($errorMessage, 'error', true));
        throw $e;
    }
    run('if [ ! -d {{deploy_path}} ]; then mkdir -p {{deploy_path}}; fi');
})->desc('Preparing server for deploy');

task('clear',function(){
  run("cd {{deploy_path}} && rm -rf `ls | grep -v vendor`");
})->desc('Clear deploy directory');

task('deploy:uploadcode',function(){
  $releaseArchive = env()->parse("release-{{release_name}}.zip");
  upload("./deploy/$releaseArchive","{{deploy_path}}/$releaseArchive");
  writeln("<info>unzip release...</info>");
  run("cd {{deploy_path}} && unzip -o $releaseArchive && rm -rf $releaseArchive");
})->desc('Upload code');

task('migrate:dbinit',function(){
  $releaseDB = env()->parse("release-{{release_name}}.sql.gz");
  if (runLocally("if [ -f ./deploy/$releaseDB ]; then echo 'true'; fi")->toBool()) {
    upload("./deploy/$releaseDB","{{deploy_path}}/$releaseDB");
    writeln("<info>migrating database...</info>");
    run("cd {{deploy_path}} && gunzip -c $releaseDB | mysql --host={{dbhost}} --user={{dbuser}} --password={{dbpwd}} {{dbname}} && rm $releaseDB");
  } else {
    writeln("<error>./deploy/$releaseDB not found</error>");
  }
})->desc("Restore DB from ./deploy/release-{{release_name}}.sql.gz file");

task('migrate:dbmigrate',function(){
  run("cd {{deploy_path}} && php ./vendor/bin/phinx migrate");
})->desc('Database migration');

task('deploy:clear_cache',function(){
  run("rm -rf {{deploy_path}}/var/cache/*");
})->desc('Clear cscart cache');

task('deploy:fix_perms',function(){
  run("chown -R {{www_user}}:{{www_group}} {{deploy_path}}");
})->desc('Set up files/dirs permissions');

task('migrate:phinx_config',function(){
  $config = new YamlConfig(get('config'),'default');
  $config->servers2Phinx('./deploy/phinx.yml');
  upload('./deploy/phinx.yml',"{{deploy_path}}/phinx.yml");
})->desc("Create phinx config");

task('deploy',[
  'deploy:prepare',
  'deploy:uploadcode',
  'migrate:phinx_config',
  'migrate:dbmigrate',
  'deploy:clear_cache',
])->desc('Deploy new CS-Cart release');

task('deploy-clear',[
  'clear',
  'deploy:prepare',
  'deploy:uploadcode',
  'migrate:phinx_config',
  'migrate:dbinit',
  'deploy:clear_cache',
])->desc('Deploy clear CS-Cart installation');

task('success', function () {
  writeln("<info>Successfully deployed!</info>");
})->once()
  ->setPrivate();

after('deploy', 'success');
?>
