# deploy-toolkit
CS-Cart and Multi-Vendor deployment toolkit for developers and system administrators.

## Requirements
1. [PHP](https://secure.php.net/) 5.5.0 and up.
2. [deployer](http://deployer.org) 3.3.0 and up.
3. PHP extensions:
 - [yaml](https://pecl.php.net/package/yaml)
 - [zip](https://pecl.php.net/package/zip)

## Prepare
1. `git clone https://github.com/simtechdev/deploy-toolkit.git`
2. `cd deploy-toolkit && mkdir -p deploy-toolkit/deploy`

## Usage example
1. Change default settings in `config.yml`
  - release_name: 4.3.8
  - project_path: /local/path/to/project/cscart
  - servers config
2. Run `dep prepare development` to zip code for development environment
3. Run `dep deploy development` to deloy code to dev servers.

## Functions description
### prepare
This function used for create zip archive release from project directory to local `./deploy` directory.
Function use command line argument to define environment.

 - create zip archive of project.

### deploy-clear
This function can be used for deploy full code and full database.
Follow steps are applied:

 - remove all files in to {{deploy_path}}
 - restore archive `release-{{release_name}}.zip` from local `./deploy` directory
 - restore dump `release-{{release_name}}.sql.gz` from local `./deploy` directory
 - clear cache

### deploy
This function can be used for deploy part of code and small changes in database.
Follow steps are applied:

 - restore archive `release-{{release_name}}.zip` from local `./deploy` directory.
 - run `phinx migrate` on server.
 - clear cache.

## Tasks description
### clear
Delete all files and directories from deploy directory, exclude `vendor` directory.

### deploy:prepare
Check ssh connection and create deploy directory

### deploy:uploadcode
Upload zipped code of project on server to deploy directory and unzip it.

### deploy:clear_cache
Clear CS-Cart cache.

### migrate:dbinit
Restore mysql dump from local archive from `./deploy/release-{{release_name}}.sql.gz`.

### migrate:dbmigrate
Run `php ./vendor/bin/phinx migrate` on server.

### migrate:phinx_config
Prepare phinx.yml config file and upload it to server.

For More documentation see [deployer docs](http://deployer.org/docs) and deploy.php source code.
