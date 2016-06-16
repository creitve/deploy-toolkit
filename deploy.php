<?php
/* Tool for deploy cscart */

/* Environments */
env('release_name','4.3.7');
env('www_user','apache');
env('www_group','apache');
/* Servers */
server('dev', '10.0.0.10')
    ->user('vagrant')
    ->password('vagrant')
    ->env('deploy_path', '/var/www/html')
    ->env('dbhost','localhost')
    ->env('dbuser','simtechdev')
    ->env('dbpwd','simtechdev')
    ->env('dbname','simtechdev');



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
  run("cd {{deploy_path}} && rm -rf ./*");
})->desc('clear deploy directory');

task('deploy:durty_release',function(){
  $releaseArchive = env()->parse("release-{{release_name}}.zip");
  upload("./deploy/$releaseArchive","{{deploy_path}}/$releaseArchive");
  writeln("<info>unzip release...</info>");
  run("cd {{deploy_path}} && unzip -o $releaseArchive -x 'config.local.php' -x 'images/*' -x '.htaccess' && rm -rf $releaseArchive");
})->desc('Durty release');

task('deploy:clear_release',function(){
  $releaseArchive = env()->parse("release-{{release_name}}.zip");
  upload("./deploy/$releaseArchive","{{deploy_path}}/$releaseArchive");
  writeln("<info>unzip release...</info>");
  run("cd {{deploy_path}} && unzip -o $releaseArchive && rm -rf $releaseArchive");
})->desc('Durty release');

task('deploy:migrate',function(){
  $releaseDB = env()->parse("release-{{release_name}}.sql");
  if (runLocally("if [ -f ./deploy/$releaseDB ]; then echo 'true'; fi")->toBool()) {
    upload("./deploy/$releaseDB","{{deploy_path}}/$releaseDB");
    writeln("<info>migrating database...</info>");
    run("cd {{deploy_path}} && mysql --host={{dbhost}} --user={{dbuser}} --password={{dbpwd}} {{dbname}} < $releaseDB && rm $releaseDB");
  } else {
    writeln("<error>./deploy/$releaseDB not found</error>");
  }
})->desc('Migrate database');

task('deploy:clear_cache',function(){
  run("rm -rf {{deploy_path}}/var/cache/*");
})->desc('clear cscart cache');

task('deploy:fix_perms',function(){
  run("chown -R {{www_user}}:{{www_group}} {{deploy_path}}");
})->desc('set up files/dirs permissions');

task('deploy',[
  'deploy:prepare',
  'deploy:durty_release',
  'deploy:migrate',
  'deploy:clear_cache',
])->desc('deploy release CS-Cart');

task('deploy-clear',[
  'clear',
  'deploy:prepare',
  'deploy:clear_release',
  'deploy:migrate',
  'deploy:clear_cache',
])->desc('Deploy new CS-Cart');

task('success', function () {
  writeln("<info>Successfully deployed!</info>");
})->once()
  ->setPrivate();

after('deploy', 'success');
?>
