# Тестовое WIAM 14.11.2025

## Установка

```
docker-compose build
docker-compose up -d
docker-compose exec php-fpm composer install
docker-compose exec php-fpm php yii migrate --interactive=0
```

## Запуск воркеров
```
docker-compose exec php-fpm php yii queue/listen --verbose
```

временя на выполнение задания ~6ч (из них половина настройка проекта и docker, остальное на реализацию и грабли со swagger)