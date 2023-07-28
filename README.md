# WetWellFed

## О проекте

[WetWellFed](https://wetwellfed.ru/) - это веб-приложение, созданное для упрощения планирования питания и учёта продуктов. 

В качестве основного фреймворка используется Symfony 5.4 на PHP 8.1, с БД MySQL.

Используются файловый кэш, авторизация, формы с валидацией, Telegram API webhook и CURL-запросы.

Позже решено было добавить Telegram бот и привязку аккаунта телеграм к пользователю на сайте. Бот дублирует некоторый функционал сайта для пользователя.

## Начало работы

Чтобы начать работу с WetWellFed, просто посетите наш веб-сайт по адресу [https://wetwellfed.ru](https://wetwellfed.ru/) и создайте учетную запись. Оттуда вы можете начать планировать свой рацион и подбирать пул подходящих продуктов с легкостью.

### **Installation**

- `docker-compose up -d --build`
- `docker-compose exec php composer install`
- `docker-compose exec php bin/console doctrine:migrations:migrate`
