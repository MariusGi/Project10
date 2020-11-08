INSTRUCTIONS to run on docker:

1.git clone https://github.com/MariusGi/Project10.git

2.cd Project10/

3.docker-compose up -d -build

3.* docker-compose start (* in case 3 was already executed before)

4.docker exec -it php74-container2 bash

4.* winpty docker exec -it php74-container2 bash (* in case there is a warning to execute command so)

5.composer update

6.php bin/console doctrine:database:create

7.php bin/console doctrine:migrations:migrate

8.goto http://localhost:8080/public-holidays
