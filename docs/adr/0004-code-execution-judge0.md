# 0004. Выполнение кода через Judge0 / Code execution via Judge0

- **Status:** Accepted
- **Date:** 2026-05-28

---

## Русский

### Контекст

В практической части курсов ученик пишет код, который должен исполняться сервером с гарантией изоляции и лимитами по CPU/RAM/времени. Языковая поддержка как минимум: JavaScript/Node, Python; в перспективе — Go, SQL, TypeScript.

Варианты:

1. Свой Docker-оркестратор песочниц (нужно писать lifecycle, lim/quota, cleanup)
2. **Judge0** — self-hosted REST-сервис исполнения кода
3. piston — аналог, проще, но меньше языков
4. Внешний API (Sphere Engine, JDoodle) — платно

### Решение

**Judge0**, self-hosted в compose-стеке. Модуль `Practice` обращается к нему по REST.

- Judge0 содержит ~70 языковых раннеров «из коробки»
- Возвращает stdout/stderr/exit code/память/время
- Поддерживает stdin для тестов
- Развёртывание — один контейнер + рабочие, лимиты через env

### Последствия

**Плюсы:** не пишем сэндбокс с нуля; широкая языковая поддержка; стандартизированный API.

**Минусы:** дополнительный сервис в стеке; компиляторы тяжёлые по памяти; масштабирование — отдельная задача (Judge0 имеет свои воркеры).

**Безопасность:** Judge0 использует isolate (chroot+namespaces), это адекватный изоляционный уровень для MVP. На проде дополнительно ограничиваем через cgroups и timeout.

---

## English

### Context

Practice features require executing user-submitted code with isolation and CPU/RAM/time limits, across multiple languages.

Options: custom Docker sandbox, **Judge0** (self-hosted), piston, paid SaaS.

### Decision

**Judge0**, self-hosted. The `Practice` module calls it over REST. Supports ~70 language runners out of the box.

### Consequences

**Pros:** no custom sandbox; broad language support.

**Cons:** additional service; memory-heavy compilers; scaling is its own concern.
