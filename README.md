# Установка
git clone git@github.com:buck-seeks-for-job/click-test-task.git

cd client-test-task

composer install

# Прогон тестов
php vendor/phpunit/phpunit/phpunit -c phpunit.xml

# Запуск приложения
./measurement-calculator your_path

Результат будет лежать в файле output.csv
