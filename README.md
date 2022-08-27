# BIS-Back-end-Assignment

This is a demo using Docker and Laravel. Data is stored here permanently

## How to run

1. Install docker
2. clone this project
3. cd into the folder
4. run `docker-compose up -d`
5. run `docker-compose exec myapp php artisan migrate` << Wait for mysql to be ready
6. check `localhost:8000`

## docker permission issue

follow these steps if you have permission issues running the container

1. `sudo groupadd docker` (if group not already exist)
2. `sudo usermod -aG docker $USER` -> adds your user to the group
3. `newgrp docker` or logout and login
4. try to run again
