## About 

CS-Cart and Multi-Vendor deployment toolkit for developers and system administrators. Current version is 1.0.1.

  * [About](#about)
      * [Demo](#demo)
      * [Requirements](#requirements)
      * [Getting started](#getting-started)
      * [Usage](#usage)
      * [Functions description](#functions-description)
          * [prepare](#prepare)
          * [deploy-clear](#deploy-clear)
          * [deploy](#deploy)
      * [Tasks description](#tasks-description)
          * [clear](#clear)
          * [deploy:prepare](#deployprepare)
          * [deploy:uploadcode](#deployuploadcode)
          * [deploy:clear_cache](#deployclear_cache)
          * [migrate:dbinit](#migratedbinit)
          * [migrate:dbmigrate](#migratedbmigrate)
          * [migrate:phinx_config](#migratephinx_config)
  * [Help needed?](#help-needed)
  * [License](#license)

#### Demo

[![asciicast](https://asciinema.org/a/dddsg010kaauba0g59o3nglo8.png)](https://asciinema.org/a/dddsg010kaauba0g59o3nglo8)

#### Requirements

* [PHP](https://secure.php.net/) 5.5.0+
* [Deployer](http://deployer.org) 3.3.0+
* [YAML](https://pecl.php.net/package/yaml) PHP extension
* [ZIP](https://pecl.php.net/package/zip) PHP extension

#### Getting started

```
git clone https://github.com/simtechdev/deploy-toolkit.git
cd deploy-toolkit
mkdir -p deploy-toolkit/deploy
```

#### Usage

1. Specify default settings in `config.yml`
  - release_name: 4.3.8
  - project_path: /local/path/to/project/cscart
  - servers config

2. Run `dep prepare development` to create a ZIP archive for development environment
3. Run `dep deploy development` to deploy code to the server.

#### Functions description

###### prepare

This function used for create zip archive release from project directory to local `./deploy` directory.
Function use command line argument to define environment.

 - create zip archive of project.

###### deploy-clear

This function can be used for deploy full code and full database.
Follow steps are applied:

 - remove all files in to `{{deploy_path}}`
 - restore archive `release-{{release_name}}.zip` from local `./deploy` directory
 - restore dump `release-{{release_name}}.sql.gz` from local `./deploy` directory
 - clear cache

###### deploy

This function can be used for deploy part of code and small changes in database.
Follow steps are applied:

 - restore archive `release-{{release_name}}.zip` from local `./deploy` directory.
 - run `phinx migrate` on server.
 - clear cache.

#### Tasks description

###### clear

Delete all files and directories from deploy directory, exclude `vendor` directory.

###### deploy:prepare

Check ssh connection and create deploy directory

###### deploy:uploadcode

Upload zipped code of project on server to deploy directory and unzip it.

###### deploy:clear_cache

Clear CS-Cart cache.

###### migrate:dbinit

Restore mysql dump from local archive from `./deploy/release-{{release_name}}.sql.gz`.

###### migrate:dbmigrate

Run `php ./vendor/bin/phinx migrate` on server.

###### migrate:phinx_config

Prepare phinx.yml config file and upload it to server.

## Help needed? 

For more documentation see [Deployer docs](http://deployer.org/docs) and deploy.php source code.

## License

MIT
