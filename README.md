# Reviews API (Laravel)

Тестовое задание: REST API на Laravel 11 (PHP 8.2+) с CRUD для пользователей, компаний и отзывов, полиморфной привязкой отзывов, статистикой по компании и топом компаний. Дополнительные Composer-пакеты для бизнес-логики не используются.

## Требования

- Docker и Docker Compose
- После запуска HTTP доступен на **http://laravel.local** (или http://localhost если домен не настроен)

## Быстрый старт

```bash
cp www/.env.example www/.env
docker compose up -d --build
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan storage:link
docker compose exec php php artisan migrate --seed
```

Проверка:

- `GET http://laravel.local/health` → `{"ok":true}`
- API с префиксом `/api` (см. ниже)

Переменные БД в `www/.env` должны совпадать с сервисом `db` в `docker-compose.yml` (`DB_HOST=db`, пользователь и пароль как в корневом `.env`).

## Docker

- **`docker-compose.yml`** — PHP-FPM, Nginx, MySQL, общий volume с кодом приложения (`SITE_PATH=./www`).
- **`Dockerfile`** — образ PHP 8.4-FPM с расширениями для Laravel и установленным Composer.

## Модель данных

- **User**: UUID, имя и фамилия (длина строго больше 3 и строго меньше 40 символов), телефон `+7` + 10 цифр (уникален), аватар PNG/JPEG до 2 МБ.
- **Company**: UUID, название (те же ограничения длины), описание 150–400 символов, логотип PNG до 3 МБ.
- **Review**: UUID, автор (`user_id`), полиморфная сущность (`user` или `company`), текст 150–550 символов, оценка 1–10.

Ответы API используют **camelCase** для полей JSON.

## Эндпоинты

Базовый URL: `http://laravel.local/api`.

### Пользователи

| Метод | Путь | Описание |
|--------|------|----------|
| GET | `/users` | Список |
| POST | `/users` | Создание (`multipart/form-data`: `first_name`, `last_name`, `phone`, `avatar`) |
| GET | `/users/{uuid}` | Один пользователь |
| PUT/PATCH | `/users/{uuid}` | Обновление (поля опциональны; файл `avatar` опционален) |
| DELETE | `/users/{uuid}` | Удаление |
| GET | `/users/{uuid}/reviews` | Отзывы, оставленные **о** пользователе (полиморфная цель) |

### Компании

| Метод | Путь | Описание |
|--------|------|----------|
| GET | `/companies/top-rated` | Топ-10 компаний по средней оценке |
| GET | `/companies/{uuid}/statistics` | Статистика отзывов компании |
| GET | `/companies/{uuid}/reviews` | Отзывы о компании |
| GET | `/companies` | Список |
| POST | `/companies` | Создание (`multipart`: `title`, `description`, `logo`) |
| GET | `/companies/{uuid}` | Одна компания |
| PUT/PATCH | `/companies/{uuid}` | Обновление |
| DELETE | `/companies/{uuid}` | Удаление |

### Отзывы

| Метод | Путь | Описание |
|--------|------|----------|
| GET | `/reviews` | Список |
| POST | `/reviews` | Создание (`application/json`: `user_id`, `reviewable_type` = `user` \| `company`, `reviewable_id`, `content`, `rating`) |
| GET | `/reviews/{uuid}` | Один отзыв |
| PUT/PATCH | `/reviews/{uuid}` | Обновление |
| DELETE | `/reviews/{uuid}` | Удаление |

### Статистика компании (`GET /companies/{id}/statistics`)

Пример тела ответа:

```json
{
  "avgRating": 7.3,
  "totalReviews": 18,
  "ratingDistribution": { "1": 0, "2": 1, "3": 2, "4": 0, "5": 0, "6": 0, "7": 0, "8": 0, "9": 0, "10": 3 },
  "latestReviewDate": "2025-03-10T14:30:00Z",
  "avgContentLength": 287.5
}
```

Если отзывов нет: `avgRating`, `latestReviewDate`, `avgContentLength` — `null`, `totalReviews` — `0`, в `ratingDistribution` все ключи `"1"`–`"10"` равны `0`.

### Топ компаний (`GET /companies/top-rated`)

Массив до 10 объектов: `id`, `title`, `avgRating` (или `null`, если нет отзывов), `reviewsCount`. Сначала компании с отзывами, выше средняя оценка; при равной средней — больше отзывов. Компании без отзывов в конце.

## Коды ответов

- `200` / `201` — успех
- `204` — успешное удаление без тела
- `404` — модель не найдена
- `422` — ошибка валидации (JSON с полем `errors`)

## Сидер

`DatabaseSeeder`: не менее **5** пользователей, **5** компаний, **20** отзывов (цели — и пользователи, и компании).

## Структура проекта (важное)

Инфраструктура Docker (`docker-compose.yml`, `Dockerfile`, `nginx/`, `php/`, `mysql/`) лежит в **корне репозитория**; само приложение Laravel — в **`www/`** (как типичный document root: `www/public/index.php`).

- `www/` — приложение Laravel (`public/` — точка входа для веб-сервера)
- `www/app/Services/` — расчёт статистики и топа (разделение ответственности)
- `www/app/Http/Requests/` — валидация запросов
- Полиморфная связь: `Review` → `reviewable` (`User` или `Company`)
