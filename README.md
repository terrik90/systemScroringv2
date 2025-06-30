Перед запуском проекта убедитесь, что у вас установлены composer, symfony CLI, PHP, Mysql.
Шаги по запуску:
1) Клонировать репозиторий и перейти в директорию проекта
2) Установить зависимости: composer install
3) В файлах .env и .env.test настроить строку подключения к БД Mysql
4) Выполнить миграцию БД: php bin/console doctrine:migrations:migrate
5) Запустить проект: symfony server:start
6) Перейти по адресу https://localhost:8000

Запуск тестов:
1) Перейти в директорию проекта
2) Прописать в консоль vendor/bin/phpunit

Запуск консольной команды по рассчету скоринга:
1) Перейти в директорию проекта
2) php bin/console app:calculate-scoring - для расчета скоринга всех клиентов
3) php bin/console app:calculate-scoring <id> - для расчета скоринга конкретного клиента
