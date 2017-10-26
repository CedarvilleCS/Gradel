php bin/console doctrine:schema:drop --full-database --force
php bin/console doctrine:schema:update --force

php bin/console doctrine:fixtures:load -y
