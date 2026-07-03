# Notifications — дизайн / Design

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

### Зона ответственности

Модуль доставляет уведомления пользователю: **in-app inbox** и **email**. Не решает *что* напомнить — слушает события других модулей (Coach, Onboarding, …) и сохраняет/отправляет.

### API (v1)

```
GET   /api/v1/notifications
GET   /api/v1/notifications?unread_only=1&per_page=20
GET   /api/v1/notifications/{id}          # помечает прочитанным
GET   /api/v1/notifications/preferences
PATCH /api/v1/notifications/preferences   # { "email_enabled": true }
```

**Auth:** Sanctum.

**Ответ списка:**

```json
{
  "notifications": [
    {
      "id": 1,
      "type": "daily_plan",
      "title": "План на 2026-07-03",
      "body": "...",
      "data": { "date": "2026-07-03" },
      "read_at": null,
      "emailed_at": "2026-07-03T09:00:00+00:00",
      "created_at": "...",
      "is_read": false
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 2,
    "unread_count": 2
  }
}
```

### Типы (`NotificationType`)

| Тип | Источник |
|-----|----------|
| `daily_plan` | Coach — событие `DailyPlanReady` |
| `onboarding_reminder` | Coach — reminders из плана |
| `coach_tip` | Coach (будущее) |
| `system` | системные сообщения |

### Поток доставки

```
CoachService → DailyPlanReady event
                    ↓
         DailyPlanNotificationService
                    ↓
         NotificationDispatcher
              ↙         ↘
    user_notifications   SendNotificationEmailJob → Mail (SMTP / Mailpit)
```

Межмодульный вызов из любого модуля:

```php
app(NotificationDispatcherInterface::class)->send($user, new SendNotificationData(
    type: NotificationType::System,
    title: '...',
    body: '...',
));
```

### Таблицы

- `user_notifications` — inbox
- `notification_preferences` — `email_enabled` (по умолчанию `true`)

### Email

- Mailable: `UserNotificationMail`
- Job: `SendNotificationEmailJob` (очередь Laravel)
- Dev: Mailpit (`MAIL_MAILER=smtp`, порт 1025)

---

<a id="english"></a>

## English

Notifications module: paginated inbox (`GET /notifications`), detail with auto-read (`GET /notifications/{id}`), email via queued mailable, preferences toggle. Coach integrates through `DailyPlanReady` event — no direct Coach → Mail coupling.
