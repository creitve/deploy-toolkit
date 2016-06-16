# deploy-toolkit
CS-Cart and Multi-Vendor deployment toolkit for developers and system administrators.

## Requirements
1. [PHP](https://secure.php.net/) 5.5.0 and up.
2. [deployer](http://deployer.org) 3.3.0 and up.

## Prepare
1. `git clone https://github.com/simtechdev/deploy-toolkit.git`
2. `cd deploy-toolkit && mkdir -p deploy-toolkit/deploy`

## Usage example
1. Copy release-{{release_name}}.zip in to `deploy` directory
2. (Optional) Copy release-{{release_name}}.sql in to `deploy` directory
3. Change `env('release_name','4.3.7');` in `deploy.php`
4. Change server settings in `deploy.php`
5. Run `dep deploy` to deploy code

## Functions description
### deploy-clear
This function can be used for deploy full code and full database.
Follow steps are applied:

 - remove all files in to {{deploy_path}}
 - restore archive `release-{{release_name}}.zip` from local `./deploy` directory
 - restore dump `release-{{release_name}}.sql` from local `./deploy` directory
 - clear cache

### deploy
This function can be used for deploy part of code and small changes in database.
Follow steps are applied:

 - restore archive `release-{{release_name}}.zip` from local `./deploy` directory, exclude:
  - config.local.php
  - images/*
  - .htaccess
 - restore dump `release-{{release_name}}.sql` from local `./deploy` directory
 - clear cache

For More documentation see [deployer docs](http://deployer.org/docs) and deploy.php source code.
