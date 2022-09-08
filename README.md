# BIS-Back-end-Assignment

This is a demo using Docker and Laravel. Data is stored here permanently

## Setup

1. Install `docker` and `docker-compose`
2. clone this project
3. cd into the folder
4. runn `cp my-project/.env.example my-project/.env`
5. run `docker-compose up --build -d` (grab a coffee, this will take some time)
6. run `docker-compose exec app rm -rf vendor composer.lock`
7. run `docker-compose exec app composer install`
8. run `docker-compose exec app php artisan key:generate`
9. run `docker-compose exec app php artisan migrate` << Wait for mysql to be ready

## How to Use

- execude any command inside the app container `docker-compose exec <command>`
- run artisan commands with `docker-compose exec app php artisan <command>`
- run tests with `docker-compose exec app php artisan test`
- access database with `docker-compose exec mariadb mysql`

### Default admin
user: `bis@bis.de`
password: `please_change_me!`

## docker permission issue

follow these steps if you have permission issues running the container

1. `sudo groupadd docker` (if group not already exist)
2. `sudo usermod -aG docker $USER` -> adds your user to the group
3. `newgrp docker`
4. try to run again
5. if there is still an error, try reboot

