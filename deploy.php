<?php
namespace Deployer;
require 'recipe/common.php';

// Configuration

set('ssh_type', 'native');
set('ssh_multiplexing', false);

set('repository', 'https://github.com/bullsoft/fp-project.git');
set('shared_files', []);
set('shared_dirs', []);
set('writable_dirs', []);

// Composer in your server  
set('bin/composer', function () {
    return '/home/work/bin/composer';
});


// Servers

server('production', '115.28.223.103')
    ->user('work')
    ->identityFile()
    ->set('deploy_path', '/home/work/deployment/fp-app');


// Tasks

desc('Restart PHP-FPM service');
task('php-fpm:restart', function () {
    // The user must have rights for restart service
    // /etc/sudoers: username ALL=NOPASSWD:/usr/sbin/service restart php-fpm
    run('sudo service php5-fpm restart');
});
after('deploy:symlink', 'php-fpm:restart');

desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

task('release:write-id', function() {
    run('tail -n 1 {{deploy_path}}/.dep/releases | cut -d, -f1 > {{deploy_path}}/releases/{{release_name}}/RELEASE_ID');
});

after('deploy:symlink', 'release:write-id');
