# Документация API для Android

## Что реально реализовано в проекте

Документ сверяет API с текущими маршрутами Laravel в проекте и контроллерами модулей `Users`, `Events`, `Chat` и
корневого `AuthController`.

### Базовая информация

| Параметр | Значение |
|---|---|
| Базовый URL | `http://<HOST>:8080/api` |
| Защищённые маршруты | `/api/v1/...` |
| Аутентификация | Bearer token (Sanctum) |
| Формат | JSON |
| Content-Type | `application/json` для обычных запросов |

Заголовки:

```http
Authorization: Bearer <token>
Accept: application/json
```

> Исключения: `POST /api/v1/login`, `POST /api/v1/register` и `GET /api/languages` не требуют токена.

---

## 1. Авторизация

### 1.1 Вход по email/password

#### `POST /api/v1/login`

Тело запроса:

```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

Успешный ответ `200`:

```json
{
  "token": "1|abc123def456"
}
```

Ошибки:

- `401` — неверный email/password
- `422` — валидация поля `email`/`password`

### 1.2 Регистрация

#### `POST /api/v1/register`

Тело запроса:

```json
{
  "name": "John Doe",
  "email": "user@example.com"
}
```

Успешный ответ `200`:

```json
{
  "message": "Registration successful! Please check your email for the password."
}
```

> Сервер создаёт пользователя и генерирует пароль, после чего отправляет его на email.

### 1.3 Текущий пользователь

#### `GET /api/v1/me`

Требует токен Sanctum.

Пример ответа:

```json
{
  "id": 1,
  "name": "John Doe",
  "profile": {
    "name": "John Doe",
    "avatar_url": null,
    "languages": null,
    "bio": null
  },
  "filter": {
    "center": null,
    "radius": null,
    "categories": null
  }
}
```

> `/api/v1/me` не генерирует новый токен; он только возвращает данные пользователя.

---

## 2. События

### 2.1 Список событий

#### `GET /api/v1/events`

Возвращает пагинированную коллекцию событий.

Пример:

```json
{
  "data": [
    {
      "id": 1,
      "title": "Название события",
      "thumbnail_url": "http://host/storage/thumbnail.webp",
      "category": "Спорт",
      "description": "Описание события",
      "slots": 10,
      "reserved": 3,
      "planing_time": 1720000000,
      "distance_km": 12
    }
  ],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 75
  }
}
```

### 2.2 Одно событие

#### `GET /api/v1/event/{event}`

Пример ответа:

```json
{
  "id": 1,
  "title": "Название события",
  "category": "Спорт",
  "thumbnail_url": "http://host/storage/thumbnail.webp",
  "description": "Описание события",
  "coordinate_lat": "55.7558",
  "coordinate_lng": "37.6173",
  "country": "Russia",
  "planing_time": 1720000000,
  "slots": 10,
  "tags": ["tag1", "tag2"],
  "reserved": 3,
  "member": 7,
  "members": [
    {
      "id": 7,
      "profile": {
        "name": "John Doe",
        "avatar_url": null,
        "languages": ["en", "ru"],
        "bio": "О себе"
      }
    }
  ]
}
```

### 2.3 Создание события

#### `POST /api/v1/events`

Создаёт событие. Для файла `thumbnail` обычно используется `multipart/form-data`.

Поле `thumbnail` или `thumbnail_url` обязательно в зависимости от сценария.

Ответ — объект события через `EventResource`.

### 2.4 Обновление и удаление

#### `PUT /api/v1/event/{event}`

Обновляет событие. Ответ — объект события.

#### `DELETE /api/v1/event/{event}`

```json
{
  "message": "Event deleted successfully"
}
```

### 2.5 Запись и отзыв

#### `POST /api/v1/event/{event}/subscribe`

Записывает текущего пользователя на событие.

Ответ — объект события после подписки.

#### `PATCH /api/v1/event/{event}/member/{member}`

Сохраняет отзыв/оценку участника.

Тело запроса:

```json
{
  "is_happened": true,
  "comment": "Крутое мероприятие!",
  "mark": 9
}
```

Ответ:

```json
{
  "message": "Feedback created successfully"
}
```

#### `DELETE /api/v1/event/{event}/unsubscribe`

Отписка текущего пользователя от события.

### 2.6 Категории и теги

#### `GET /api/v1/categories`

```json
[
  { "id": 1, "title": "Спорт" },
  { "id": 2, "title": "Музыка" }
]
```

#### `GET /api/v1/tags`

Список тегов.

---

## 3. Профиль, фильтр, обратная связь и устройство

### 3.1 Обновление профиля

#### `PATCH /api/v1/profile`

Пример запроса:

```json
{
  "name": "John Doe",
  "languages": ["en", "ru"],
  "bio": "О себе"
}
```

Ответ:

```json
{
  "name": "John Doe",
  "avatar_url": null,
  "languages": ["en", "ru"],
  "bio": "О себе"
}
```

### 3.2 Фильтр событий

#### `PATCH /api/v1/filter`

Пример:

```json
{
  "address": [55.7558, 37.6173],
  "radius": 50,
  "categories": [1, 3, 5]
}
```

### 3.3 Feedback

#### `POST /api/v1/feedback`

```json
{
  "text": "Классное приложение!"
}
```

Ответ:

```json
{
  "message": "Feedback created successfully."
}
```

### 3.4 FCM токен

#### `PATCH /api/v1/device`

```json
{
  "fcm_token": "device_token_here"
}
```

Ответ:

```json
{
  "message": "Device token updated."
}
```

### 3.5 Список языков

#### `GET /api/languages`

Публичный маршрут.

```json
{
  "af": "Afrikaans",
  "en": "English",
  "ru": "Russian",
  "uk": "Ukrainian"
}
```

---

## 4. Чат

### 4.1 Список сообщений

#### `GET /api/v1/chat/{chat}`

Пример ответа:

```json
{
  "data": [
    {
      "id": 1,
      "content": "Привет, кто идёт?",
      "author": {
        "id": 7,
        "profile": {
          "name": "John Doe",
          "avatar_url": null,
          "languages": ["en", "ru"],
          "bio": "О себе"
        }
      },
      "created_at": 1720000000
    }
  ]
}
```

### 4.2 Создание сообщения

#### `POST /api/v1/chat/{chat}`

```json
{
  "content": "Привет, кто идёт?"
}
```

### 4.3 Получение и редактирование сообщения

- `GET /api/v1/chat/{chat}/message/{message}`
- `PATCH /api/v1/chat/{chat}/message/{message}`
- `DELETE /api/v1/chat/{chat}/message/{message}`

> В текущей реализации `DELETE` и некоторые другие методы чата могут возвращать `true` вместо JSON-объекта `{ "message": ... }`.

---

## 5. Маршруты, которые существуют, но не готовы как полноценный API

В проекте есть модуль `Notificatiion`, но он пока заглушка:

- `GET /api/v1/notificatiions`
- `POST /api/v1/notificatiions`
- `GET /api/v1/notificatiions/{id}`
- `PUT /api/v1/notificatiions/{id}`
- `DELETE /api/v1/notificatiions/{id}`

Файл маршрутов есть, но контроллер пока возвращает `view(...)` и не реализует реальный JSON-API.

---

## 6. Важные замечания по текущему состоянию

1. Все защищённые маршруты требуют `Authorization: Bearer <token>`.
2. В проекте есть несоответствие между общим форматом ответа и отдельными маршрутами чата: часть методов
   возвращает `true`, а не JSON.
3. Список событий и фильтр зависят от `user->filter` и `user->profile`.
4. OAuth, FCM и геокодинг в проекте частично реализованы, но после анализа кода ещё требуют проверки на
   production-сценарии.
5. Контроллеры и ресурсы в `Events`/`Chat` формируют ответы не идеально унифицировано — для Android важно проверять
   реальные структуры ответа на каждом маршруте.

---

## 7. Краткая карта маршрутов

### Публичные

- `POST /api/v1/login`
- `POST /api/v1/register`
- `GET /api/languages`

### Защищённые

- `GET /api/v1/me`
- `GET /api/v1/events`
- `GET /api/v1/event/{event}`
- `POST /api/v1/events`
- `PUT /api/v1/event/{event}`
- `DELETE /api/v1/event/{event}`
- `POST /api/v1/event/{event}/subscribe`
- `PATCH /api/v1/event/{event}/member/{member}`
- `DELETE /api/v1/event/{event}/unsubscribe`
- `GET /api/v1/categories`
- `GET /api/v1/tags`
- `PATCH /api/v1/profile`
- `PATCH /api/v1/filter`
- `POST /api/v1/feedback`
- `PATCH /api/v1/device`
- `GET /api/v1/chat/{chat}`
- `POST /api/v1/chat/{chat}`
- `GET /api/v1/chat/{chat}/message/{message}`
- `PATCH /api/v1/chat/{chat}/message/{message}`
- `DELETE /api/v1/chat/{chat}/message/{message}`

Это актуальная карта маршрутов по коду проекта на момент анализа.
