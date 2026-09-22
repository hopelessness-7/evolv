#!/usr/bin/env python3
"""Generate database/seeders/data/content/php_fundamentals.json — unique rich entries."""
from __future__ import annotations

import json
from pathlib import Path

OUT = Path(__file__).parent / "data" / "content" / "php_fundamentals.json"


def T(md: str, order: int = 1) -> dict:
    return {"kind": "theory", "order": order, "body_md": md.strip() + "\n"}


def S(code: str, order: int) -> dict:
    return {
        "kind": "snippet",
        "order": order,
        "body_md": "```php\n" + code.strip() + "\n```\n",
        "meta": {"language": "php"},
    }


def Q(body: str, answer: str, order: int) -> dict:
    assert answer in ("A", "B", "C")
    return {
        "kind": "quiz",
        "order": order,
        "body_md": body.strip() + "\n",
        "meta": {"answer": answer},
    }


def U(md: str, order: int = 5) -> dict:
    return {"kind": "summary", "order": order, "body_md": md.strip() + "\n"}


def E(prompt: str, starter: str, tests: list, order: int, hints: list | None = None) -> dict:
    meta: dict = {
        "language": "php",
        "starter_code": starter if starter.endswith("\n") else starter + "\n",
        "tests": tests,
    }
    if hints:
        meta["hints"] = hints
    return {"kind": "exercise", "order": order, "body_md": prompt.strip() + "\n", "meta": meta}


def entry(slug: str, atoms: list) -> dict:
    return {"node_slug": slug, "version_no": 1, "atoms": atoms}


ENTRIES: list[dict] = []

# ---------------------------------------------------------------------------
ENTRIES.append(entry("php.intro", [
    T("""# Введение в PHP 8+

PHP — интерпретируемый язык для **веба и CLI**. Скрипт выполняется runtime-ом `php`, результат уходит в stdout (терминал) или в HTTP-ответ через веб-сервер (nginx/php-fpm, Apache, встроенный сервер).

## Первый скрипт

```php
<?php
declare(strict_types=1);

echo "Hello, Evolv!";
```

- `<?php` — начало кода (обязательный открывающий тег в чистых `.php` файлах).
- `declare(strict_types=1);` — **в этом файле** скалярные аргументы/returns проверяются строго (`"1"` не пройдёт туда, где ждут `int`). Без declare PHP тихо жонглирует типами.
- `echo` пишет в stdout. Для practice/Judge0 ожидаемая строка сравнивается **байт-в-байт** — без лишнего `\\n`, если он не нужен.

## Где живёт PHP

| Режим | Как запускают |
|-------|----------------|
| CLI | `php script.php`, crontab, workers |
| Web | FPM/mod_php отдаёт ответ браузеру |

Критерий узла: уметь запустить скрипт с `echo` и `strict_types`."""),
    S("""<?php
declare(strict_types=1);

echo "Hello, Evolv!";""", 2),
    Q("""Чем открывается PHP-скрипт?

- A) `<script type="php">`
- B) `<?php`
- C) `#php`""", "B", 3),
    Q("""Зачем `declare(strict_types=1);`?

- A) Ускоряет JIT в два раза
- B) Включает строгую проверку скалярных типов в файле
- C) Отключает все Warning""", "B", 4),
    U("""- Точка входа: `<?php` + `declare(strict_types=1);`
- `echo` → stdout; точность строки критична
- CLI и web — один язык, разные точки входа"""),
    E(
        "Выведите ровно `Hello, Evolv!` (без перевода строки в конце).",
        """<?php
declare(strict_types=1);

// Print exactly: Hello, Evolv!
echo '';
""",
        [{"label": "greeting", "stdin": "", "expected_output": "Hello, Evolv!"}],
        6,
        ["Замените пустую строку в echo на Hello, Evolv!", "Не добавляйте \\n."],
    ),
]))

ENTRIES.append(entry("php.variables", [
    T("""# Переменные и присваивание

Имя переменной: `$` + идентификатор (`$name`, `$_count`). Регистр важен. До использования переменную нужно инициализировать — иначе Warning и `null`.

## Присваивание и ссылки

`$b = $a;` для скаляров — **копия значения**.  
`$b = &$a;` — обе имени на одну ячейку; правят друг друга. В современном коде ссылки редки: предпочитайте return значений и объекты.

## Константы

| Способ | Когда |
|--------|--------|
| `const APP = 'evolv';` | compile-time, namespace/класс |
| `define('APP', 'evolv');` | runtime, условное определение |
| `public const string STATUS = 'ok';` | классовая (PHP 8.3+ typed) |

Константа **без** `$`. Доступ: `APP` или `User::STATUS`.

Критерий: объяснить разницу `const` vs `define`."""),
    S("""<?php
declare(strict_types=1);

$name = 'Ann';
const APP = 'evolv';

echo $name . '@' . APP;""", 2),
    Q("""Как объявить константу времени компиляции в файле?

- A) `$APP = 'evolv';`
- B) `const APP = 'evolv';`
- C) `let APP = 'evolv';""", "B", 3),
    Q("""Что делает `$b = &$a;`?

- A) Копирует `$a` один раз
- B) Связывает `$a` и `$b` на одну ячейку
- C) Создаёт immutable-копию""", "B", 4),
    U("""- `$name` — переменная; константа — без `$`
- `const` vs `define`; typed class const
- Ссылки `&` — осознанно"""),
    E(
        "Задайте `$name = 'Ann'`, константу `APP = 'evolv'` через `const` и выведите `Ann@evolv`.",
        """<?php
declare(strict_types=1);

$name = 'Ann';
const APP = 'evolv';

// TODO: echo Ann@evolv using $name and APP
echo '';
""",
        [{"label": "name@app", "stdin": "", "expected_output": "Ann@evolv"}],
        6,
    ),
]))

ENTRIES.append(entry("php.types", [
    T("""# Система типов PHP 8

Скаляры: `int`, `float`, `string`, `bool`. Композиты: `array`, `object`, `callable`, `iterable`. Особые: `null`, `mixed`, `void` (нет значения), `never` (не возвращает — throw/exit).

## Nullable и union

- `?string` ≡ `string|null`
- Union: `int|string`, `array|false` (частый стиль stdlib)
- Intersection и DNF — отдельный узел advanced-types

## Сигнатуры

```php
function label(?string $name): string
{
    return $name ?? 'guest';
}
```

При `strict_types` неверный аргумент/return → `TypeError`. Без strict PHP часто «подгонит» тип — отсюда баги `0`/`""`/`null`.

Критерий: назвать ≥5 типов и показать union/nullable."""),
    S("""<?php
declare(strict_types=1);

function label(?string $name): string
{
    return $name ?? 'guest';
}

echo label(null);""", 2),
    Q("""Что значит `?int`?

- A) Только положительный int
- B) `int` или `null`
- C) Параметр опционален без типа""", "B", 3),
    Q("""Какой return type у функции, которая всегда бросает исключение?

- A) `void`
- B) `mixed`
- C) `never`""", "C", 4),
    U("""- Скаляры + `array`/`object`/`callable`/`iterable`
- `?T`, unions, `void`/`never`/`mixed`
- `strict_types` ловит juggling на границах"""),
    E(
        "Реализуйте `label(?string $name): string` — имя или `guest` при `null`. Вызовите `label(null)` и выведите результат.",
        """<?php
declare(strict_types=1);

function label(?string $name): string
{
    // TODO
    return '';
}

echo label(null);
""",
        [{"label": "null→guest", "stdin": "", "expected_output": "guest"}],
        6,
    ),
]))

ENTRIES.append(entry("php.strings", [
    T("""# Строки

- **Одинарные** `'...'` — почти литерал (`\\'` и `\\\\`).
- **Двойные** `"..."` — интерполяция `$var` / `{$expr}`, `\n`, `\t`.
- **Heredoc / nowdoc** — многострочные блоки (nowdoc без интерполяции).

## Сборка

- Конкатенация `.`
- `sprintf('%s=%s', $k, $v)` — шаблоны
- `str_contains` / `str_starts_with` / `str_ends_with` (PHP 8+)

## Unicode

`strlen` считает **байты**. Для символов UTF-8 — `mb_strlen` / `mb_substr` (расширение mbstring). На кириллице без mbstring легко «разрезать» символ.

Критерий: собрать строку через sprintf + интерполяцию."""),
    S("""<?php
declare(strict_types=1);

$lang = 'PHP';
$ver = '8.3';
echo sprintf('%s=%s', $lang, $ver);""", 2),
    Q("""Что безопаснее для длины UTF-8 с кириллицей?

- A) `strlen`
- B) `mb_strlen`
- C) `count`""", "B", 3),
    Q("""`"Hi, {$name}"` при `$name = 'Ann'` даст:

- A) `Hi, {$name}`
- B) `Hi, Ann`
- C) Parse error""", "B", 4),
    U("""- Кавычки / heredoc / nowdoc
- `sprintf`, `str_*` PHP 8
- mbstring для Unicode"""),
    E(
        "С `$lang = 'PHP'` и `$ver = '8.3'` выведите `PHP=8.3` через `sprintf`.",
        """<?php
declare(strict_types=1);

$lang = 'PHP';
$ver = '8.3';

// TODO: sprintf('%s=%s', ...)
echo '';
""",
        [{"label": "sprintf", "stdin": "", "expected_output": "PHP=8.3"}],
        6,
    ),
]))

ENTRIES.append(entry("php.operators", [
    T("""# Операторы и ===

Арифметика: `+ - * / % **`. Сравнения: `==` (с приведением), **`===`** (тип + значение), `!==`, spaceship `<=>` (−1/0/1 для сортировок).

## Частые сюрпризы

```php
0 == '0';   // true  — juggling
0 === '0';  // false — разные типы
```

Правило продакшена: почти всегда `===` / `!==`.

## Null-helpers

- `$x ?? 'fallback'` — если `$x` null
- `$x ??= 'fallback'` — присвоить только если null
- `$obj?->method()` — nullsafe

Логика: `&&` / `||` / `!` (приоритетнее, чем `and`/`or`).

Критерий: объяснить `==` vs `===` на примере."""),
    S("""<?php
declare(strict_types=1);

$a = 0;
echo ($a == '0' ? 'loose' : 'no') . ':' . ($a === '0' ? 'strict' : 'diff');""", 2),
    Q("""`0 == '0'` и `0 === '0'` — это?

- A) true и true
- B) true и false
- C) false и false""", "B", 3),
    Q("""`$x ?? 'fallback'` при `$x = null` вернёт:

- A) `null`
- B) `fallback`
- C) Warning""", "B", 4),
    U("""- Предпочитайте `===`
- `??` / `??=` / `?->`
- `<=>` для сравнения"""),
    E(
        "При `$a = 0` выведите `loose:diff` — ветки через `== '0'` и `=== '0'` (как в теории).",
        """<?php
declare(strict_types=1);

$a = 0;

$left = $a == '0' ? 'loose' : 'no';
$right = $a === '0' ? 'strict' : 'diff';

// TODO: echo $left . ':' . $right
echo '';
""",
        [{"label": "loose-vs-strict", "stdin": "", "expected_output": "loose:diff"}],
        6,
    ),
]))

ENTRIES.append(entry("php.arrays", [
    T("""# Массивы

В PHP массив — упорядоченная **map** ключ→значение. Ключи: `int|string`.

```php
$list = [1, 2, 3];
$user = ['name' => 'Ann', 'role' => 'dev'];
```

## Инструменты

- `foreach ($a as $k => $v)`
- Destructuring: `[$x, $y] = $pair;` / `['name' => $n] = $user;`
- Spread: `$all = [0, ...$nums, 4];`
- `array_map` / `array_filter` / `array_key_exists` (ключ есть даже если value `null`)
- `in_array($v, $a, true)` — строгий третий аргумент

`isset($a['k'])` ложно и когда ключа нет, и когда значение `null` — для наличия ключа берите `array_key_exists`.

Критерий: unpack + foreach без сюрпризов."""),
    S("""<?php
declare(strict_types=1);

$nums = [1, 2, 3];
$all = [0, ...$nums, 4];
echo implode(',', $all);""", 2),
    Q("""Проверить ключ при возможном `null`-значении:

- A) только `isset`
- B) `array_key_exists`
- C) `empty` достаточно""", "B", 3),
    Q("""`[...$a, ...$b]` делает:

- A) Уникальные значения
- B) Распаковку в новый массив
- C) Только для list-ключей 0..n""", "B", 4),
    U("""- List vs assoc
- Destructuring и spread
- `array_*` + строгий `in_array`"""),
    E(
        "Из `$nums = [1, 2, 3]` соберите `[0, ...$nums, 4]` и выведите `0,1,2,3,4` через `implode`.",
        """<?php
declare(strict_types=1);

$nums = [1, 2, 3];
$all = [0, ...$nums, 4];

// TODO: implode(',', $all)
echo '';
""",
        [{"label": "spread", "stdin": "", "expected_output": "0,1,2,3,4"}],
        6,
    ),
]))

ENTRIES.append(entry("php.control-flow", [
    T("""# Управление потоком и match

Классика: `if` / `elseif` / `else`, `for` / `while` / `do-while` / `foreach`, `break` / `continue` (можно с уровнем вложенности).

## switch vs match

`switch` сравнивает через **`==`**, легко забыть `break` (fall-through).

`match` (PHP 8+) — **выражение**, сравнение через **`===`**, возвращает значение, без fall-through:

```php
$label = match ($code) {
    200 => 'ok',
    404 => 'missing',
    default => 'other',
};
```

Без подходящей ветки и без `default` → `UnhandledMatchError`.

Критерий: переписать switch на match."""),
    S("""<?php
declare(strict_types=1);

$code = 404;
echo match ($code) {
    200 => 'ok',
    404 => 'missing',
    default => 'other',
};""", 2),
    Q("""Почему `match` обычно предпочтительнее `switch`?

- A) Сравнивает через `===` и возвращает значение
- B) Нельзя указать `default`
- C) Работает только со строками""", "A", 3),
    Q("""Нет ветки и нет `default` в `match` — будет:

- A) `null`
- B) `UnhandledMatchError`
- C) Тихий пропуск""", "B", 4),
    U("""- if/loops + break/continue
- `match` вместо слабого switch
- `match` — expression с `===`"""),
    E(
        "Для `$code = 404` выведите `missing` через `match` (200→ok, 404→missing, default→other).",
        """<?php
declare(strict_types=1);

$code = 404;

echo match ($code) {
    200 => 'ok',
    // TODO: 404 branch → missing
    default => 'other',
};
""",
        [{"label": "match-404", "stdin": "", "expected_output": "missing"}],
        6,
    ),
]))

ENTRIES.append(entry("php.functions", [
    T("""# Функции

```php
function add(int $a, int $b = 0): int
{
    return $a + $b;
}

echo add(a: 2, b: 3); // named arguments
```

- Параметры с типами; defaults только справа от обязательных
- **Named args** (PHP 8): порядок аргументов можно менять
- Variadic: `function sum(int ...$ns): int`
- Return type — часть контракта; `void` если нет значения

Область: локальные переменные функции не видят внешние (кроме `global`/`$GLOBALS` — избегайте). First-class callable: `strlen(...)`.

Критерий: функция с named args и return type."""),
    S("""<?php
declare(strict_types=1);

function add(int $a, int $b = 0): int
{
    return $a + $b;
}

echo add(a: 2, b: 3);""", 2),
    Q("""Named arguments записываются так:

- A) `add(a=2, b=3)`
- B) `add(a: 2, b: 3)`
- C) `add[a: 2]`""", "B", 3),
    Q("""`int ...$ns` означает:

- A) Ровно один int
- B) Ноль или больше аргументов int
- C) Массив только по ссылке""", "B", 4),
    U("""- Типы параметров и return
- Named args, defaults, variadic
- Избегайте `global`"""),
    E(
        "Реализуйте `add(int $a, int $b = 0): int` и выведите `add(a: 2, b: 3)`.",
        """<?php
declare(strict_types=1);

function add(int $a, int $b = 0): int
{
    // TODO
    return 0;
}

echo add(a: 2, b: 3);
""",
        [{"label": "named-add", "stdin": "", "expected_output": "5"}],
        6,
    ),
]))

ENTRIES.append(entry("php.closures", [
    T("""# Closures и arrow functions

Анонимная функция — объект `Closure`:

```php
$factor = 3;
$mul = function (int $x) use ($factor): int {
    return $x * $factor;
};
```

`use ($factor)` захватывает внешнюю переменную **по значению**; `use (&$factor)` — по ссылке.

## Arrow functions

`fn(int $x): int => $x * $factor` — короткий синтаксис, **авто**-захват по значению, тело — одно выражение.

First-class callable: `$fn = strlen(...);` — Closure-обёртка над функцией.

Типичное применение: `array_map`, `usort`, колбэки фреймворка.

Критерий: замыкание с `use` + arrow `fn`."""),
    S("""<?php
declare(strict_types=1);

$factor = 3;
$mul = fn (int $x): int => $x * $factor;
echo $mul(4);""", 2),
    Q("""Чем arrow `fn` удобнее полного `function`+`use`?

- A) Не захватывает внешние переменные
- B) Короткое выражение с авто-захватом по значению
- C) Только внутри классов""", "B", 3),
    Q("""`strlen(...)` — это:

- A) Немедленный вызов
- B) First-class callable (Closure)
- C) Синтаксическая ошибка""", "B", 4),
    U("""- `function () use ($x)`
- `fn =>` с auto-capture
- `foo(...)` callables"""),
    E(
        "При `$factor = 3` создайте arrow `fn(int $x): int => $x * $factor` и выведите `$mul(4)` → `12`.",
        """<?php
declare(strict_types=1);

$factor = 3;

// TODO: $mul = fn ...; echo $mul(4);
""",
        [{"label": "arrow-mul", "stdin": "", "expected_output": "12"}],
        6,
    ),
]))

ENTRIES.append(entry("php.oop-basics", [
    T("""# ООП: классы

```php
final class User
{
    public function __construct(
        public readonly string $name,
        private int $id,
    ) {}

    public function label(): string
    {
        return $this->name . '#' . $this->id;
    }
}
```

## Visibility

`public` / `protected` / `private` — для свойств и методов. Инкапсулируйте состояние (`private`), наружу отдавайте поведение.

## Promotion и readonly

Constructor property promotion (PHP 8): visibility на параметре конструктора создаёт свойство.  
`readonly` — запись один раз (обычно в конструкторе). С 8.2 — `readonly class`.

Критерий: класс с promoted props и методом."""),
    S("""<?php
declare(strict_types=1);

final class User
{
    public function __construct(
        public readonly string $name,
        private int $id,
    ) {}

    public function label(): string
    {
        return $this->name . '#' . $this->id;
    }
}

echo (new User('Ann', 7))->label();""", 2),
    Q("""Constructor property promotion — это:

- A) Авто-Singleton
- B) Параметры конструктора с visibility становятся свойствами
- C) Только static-поля""", "B", 3),
    Q("""Повторно присвоить `readonly` свойство после конструктора:

- A) Можно всегда
- B) Нельзя (один раз)
- C) Можно только из наследника""", "B", 4),
    U("""- Visibility + `$this`
- Promotion и readonly
- Методы с типами"""),
    E(
        "Класс `User`: promoted `public readonly string $name`, `private int $id`, метод `label()` → `name#id`. Выведите label для `new User('Ann', 7)`.",
        """<?php
declare(strict_types=1);

final class User
{
    public function __construct(
        public readonly string $name,
        private int $id,
    ) {}

    public function label(): string
    {
        // TODO
        return '';
    }
}

echo (new User('Ann', 7))->label();
""",
        [{"label": "user-label", "stdin": "", "expected_output": "Ann#7"}],
        6,
    ),
]))

ENTRIES.append(entry("php.oop-inheritance", [
    T("""# Наследование и abstract

`class Cat extends Animal` наследует методы/свойства (с учётом visibility). Вызов родителя: `parent::__construct()` / `parent::method()`.

## abstract и final

- `abstract class` — нельзя `new`; может содержать `abstract function` без тела.
- `final class` / `final function` — запрет наследования/переопределения.
- Late static binding: `static::` vs `self::` — выбор класса в runtime у наследника.

```php
abstract class Animal
{
    abstract public function kind(): string;

    public function tag(): string
    {
        return 'animal:' . $this->kind();
    }
}
```

Критерий: иерархия abstract + concrete."""),
    S("""<?php
declare(strict_types=1);

abstract class Animal
{
    abstract public function kind(): string;

    public function tag(): string
    {
        return 'animal:' . $this->kind();
    }
}

final class Cat extends Animal
{
    public function kind(): string
    {
        return 'cat';
    }
}

echo (new Cat())->tag();""", 2),
    Q("""Можно ли сделать `new` у `abstract class`?

- A) Да
- B) Нет
- C) Только с `final`""", "B", 3),
    Q("""`static::method()` vs `self::method()` — в чём суть LSB?

- A) `static::` учитывает класс вызова у наследника
- B) Они всегда идентичны
- C) `self::` только для трейтов""", "A", 4),
    U("""- extends + parent::
- abstract / final
- `static::` (LSB)"""),
    E(
        "Сделайте abstract `Animal` с `kind(): string` и `tag()` → `animal:{kind}`. `Cat` возвращает `cat`. Выведите `(new Cat())->tag()`.",
        """<?php
declare(strict_types=1);

abstract class Animal
{
    abstract public function kind(): string;

    public function tag(): string
    {
        return 'animal:' . $this->kind();
    }
}

final class Cat extends Animal
{
    public function kind(): string
    {
        // TODO
        return '';
    }
}

echo (new Cat())->tag();
""",
        [{"label": "animal-cat", "stdin": "", "expected_output": "animal:cat"}],
        6,
    ),
]))

ENTRIES.append(entry("php.interfaces", [
    T("""# Interfaces и контракты

`interface` описывает **публичный контракт** без реализации (кроме default methods в редких RFC-кейсах — в базе считайте методы абстрактными). Класс: `implements Pingable`.

```php
interface Pingable
{
    public function ping(): string;
}
```

Один класс — много интерфейсов. Тип в сигнатуре `function hit(Pingable $x)` зависит от контракта, не от конкретного класса — DIP/ISP: мелкие интерфейсы лучше «бога на один раз».

Интерфейс ≠ наследование поведения; это **форма** для полиморфизма и тестов (моки).

Критерий: интерфейс + 2 реализации (в упражнении — одна полная)."""),
    S("""<?php
declare(strict_types=1);

interface Pingable
{
    public function ping(): string;
}

final class OkPing implements Pingable
{
    public function ping(): string
    {
        return 'ok';
    }
}

function hit(Pingable $p): string
{
    return 'ping:' . $p->ping();
}

echo hit(new OkPing());""", 2),
    Q("""Зачем объявлять параметр как `Pingable`, а не `OkPing`?

- A) Чтобы зависеть от контракта, а не конкретной реализации
- B) Интерфейсы быстрее классов
- C) Иначе нельзя вызвать метод""", "A", 3),
    Q("""Класс может реализовать:

- A) Только один interface
- B) Несколько interfaces
- C) Только если уже extends""", "B", 4),
    U("""- interface = контракт
- implements + полиморфизм
- ISP: узкие интерфейсы"""),
    E(
        "Интерфейс `Pingable` с `ping(): string`, класс `OkPing` → `'ok'`, функция `hit(Pingable $p): string` → `ping:` + ping. Выведите `hit(new OkPing())`.",
        """<?php
declare(strict_types=1);

interface Pingable
{
    public function ping(): string;
}

final class OkPing implements Pingable
{
    public function ping(): string
    {
        // TODO
        return '';
    }
}

function hit(Pingable $p): string
{
    return 'ping:' . $p->ping();
}

echo hit(new OkPing());
""",
        [{"label": "ping-ok", "stdin": "", "expected_output": "ping:ok"}],
        6,
    ),
]))

ENTRIES.append(entry("php.traits", [
    T("""# Traits

Trait — горизонтальное переиспользование методов без наследования:

```php
trait Logger
{
    public function log(): string
    {
        return 'logged';
    }
}

final class Service
{
    use Logger;
}
```

## Конфликты

Если два trait дают одно имя метода: `insteadof` выбрать победителя, `as` — алиас:

```php
use A, B {
    B::save insteadof A;
    A::save as saveA;
}
```

Trait не заменяет композицию сервисов: для состояния с зависимостями чаще **внедрение** объекта. Traits удобны для повторяющихся мелких способностей (helpers), не для всей доменной модели.

Критерий: trait с методом в классе."""),
    S("""<?php
declare(strict_types=1);

trait Logger
{
    public function log(): string
    {
        return 'logged';
    }
}

final class Service
{
    use Logger;
}

echo (new Service())->log();""", 2),
    Q("""Как подключить trait в класс?

- A) `extends Logger`
- B) `use Logger;` внутри класса
- C) `import Logger;`""", "B", 3),
    Q("""Два trait с одним именем метода — решается через:

- A) Только переименование файла
- B) `insteadof` / `as`
- C) Автоматически побеждает алфавит""", "B", 4),
    U("""- `use TraitName` в классе
- Конфликты: insteadof/as
- Не замена DI/композиции"""),
    E(
        "Создайте trait `Logger` с `log(): string` → `logged`, класс `Service` с `use Logger`, выведите `(new Service())->log()`.",
        """<?php
declare(strict_types=1);

trait Logger
{
    public function log(): string
    {
        // TODO
        return '';
    }
}

final class Service
{
    use Logger;
}

echo (new Service())->log();
""",
        [{"label": "trait-log", "stdin": "", "expected_output": "logged"}],
        6,
    ),
]))

ENTRIES.append(entry("php.enums-readonly", [
    T("""# Enum и readonly classes

## Backed enum

```php
enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
}

Status::Draft->value; // 'draft'
Status::from('draft');
Status::tryFrom('nope'); // null
```

Pure enum (без `: string|:int`) — только cases без scalar values. Методы и `implements` на enum допустимы.

## Readonly class (8.2+)

`readonly class Money` — все свойства readonly; удобно для DTO/value objects. Комбинируйте с promoted конструктором.

Не мутируйте «даты статуса» через публичные поля — меняйте через явные методы/новые instance.

Критерий: backed enum (+ идея readonly DTO)."""),
    S("""<?php
declare(strict_types=1);

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
}

echo Status::Draft->value;""", 2),
    Q("""`Status::tryFrom('nope')` при невалидной строке вернёт:

- A) Exception всегда
- B) `null`
- C) `Status::Draft`""", "B", 3),
    Q("""`readonly class` означает:

- A) Класс нельзя автозагрузить
- B) Все свойства только для чтения после init
- C) Запрещены методы""", "B", 4),
    U("""- Backed enum: value / from / tryFrom
- Pure vs backed
- Readonly DTO"""),
    E(
        "Объявите `enum Status: string` с `Draft = 'draft'` и выведите `Status::Draft->value`.",
        """<?php
declare(strict_types=1);

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
}

// TODO: echo value of Draft
""",
        [{"label": "enum-draft", "stdin": "", "expected_output": "draft"}],
        6,
    ),
]))

ENTRIES.append(entry("php.exceptions", [
    T("""# Исключения

Иерархия: `Throwable` ← `Error` | `Exception`. Бизнес/ожидаемые сбои — обычно `Exception` и подклассы; фатальные проблемы движка — `Error` (TypeError, ValueError…).

```php
try {
    throw new RuntimeException('bad');
} catch (RuntimeException $e) {
    echo 'caught:' . $e->getMessage();
} finally {
    // всегда
}
```

Свои исключения: `final class DomainException extends Exception {}` — ловите **конкретные** типы, не голый `Exception` без нужды. `finally` — освобождение ресурсов.

Не глотайте исключения пустым `catch`. `throw` может быть expression (PHP 8).

Критерий: свой Exception + catch."""),
    S("""<?php
declare(strict_types=1);

try {
    throw new RuntimeException('bad');
} catch (RuntimeException $e) {
    echo 'caught:' . $e->getMessage();
}""", 2),
    Q("""Общий предок Exception и Error:

- A) `RuntimeException`
- B) `Throwable`
- C) `Failure`""", "B", 3),
    Q("""Блок `finally` выполняется:

- A) Только при ошибке
- B) Всегда при выходе из try/catch
- C) Только если нет return""", "B", 4),
    U("""- Throwable / Exception / Error
- try / catch / finally
- Свои типы исключений"""),
    E(
        "В `try` бросьте `RuntimeException('bad')`, в `catch` выведите `caught:` + message.",
        """<?php
declare(strict_types=1);

try {
    // TODO: throw
    throw new RuntimeException('bad');
} catch (RuntimeException $e) {
    // TODO: echo caught:...
    echo '';
}
""",
        [{"label": "caught-bad", "stdin": "", "expected_output": "caught:bad"}],
        6,
    ),
]))

ENTRIES.append(entry("php.namespaces-autoload", [
    T("""# Namespaces и PSR-4

`namespace App\\Service;` задаёт пространство имён файла. Импорт: `use App\\Service\\UserService;`. FQCN: `\\App\\Service\\UserService`.

## PSR-4

Composer mapping: префикс namespace → каталог:

```json
"App\\\\": "src/"
```

`App\\Service\\UserService` → `src/Service/UserService.php`. Один класс — один файл с тем же именем. Автозагрузка подключает файл при первом обращении к классу — без ручных `require`.

`require vendor/autoload.php` — точка входа автозагрузчика.

Критерий: объяснить PSR-4 mapping."""),
    S("""<?php
declare(strict_types=1);

// Демонстрация FQCN-строки (без реальной автозагрузки в песочнице)
$class = 'App\\Service\\UserService';
echo $class;""", 2),
    Q("""При `"App\\\\": "src/"` класс `App\\Domain\\User` лежит в:

- A) `src/App/Domain/User.php`
- B) `src/Domain/User.php`
- C) `App/Domain/User.php` обязательно в корне проекта""", "B", 3),
    Q("""Зачем `use App\\Service\\UserService`?

- A) Скачать пакет из Packagist
- B) Импортировать короткое имя класса в текущий файл
- C) Создать singleton""", "B", 4),
    U("""- namespace / use / FQCN
- PSR-4: prefix → directory
- vendor/autoload.php"""),
    E(
        "Выведите строку FQCN `App\\Service\\UserService` (один backslash между сегментами в значении).",
        """<?php
declare(strict_types=1);

// TODO: echo App\\Service\\UserService
""",
        [{"label": "fqcn", "stdin": "", "expected_output": "App\\Service\\UserService"}],
        6,
    ),
]))

ENTRIES.append(entry("php.composer", [
    T("""# Composer

Менеджер зависимостей PHP. Ключевые файлы:

| Файл | Роль |
|------|------|
| `composer.json` | require, autoload, scripts |
| `composer.lock` | точные версии — **коммитьте** |
| `vendor/` | установлено (обычно в .gitignore) |

## Команды

- `composer require pkg/name` / `require --dev`
- `composer install` (по lock) vs `update`
- `composer dump-autoload` после изменения PSR-4
- scripts: `"test": "phpunit"`

`require` — прод; `require-dev` — phpunit, pint, ide-helpers. В проде часто `--no-dev`.

Точка входа кода: `require __DIR__.'/vendor/autoload.php';`.

Критерий: описать composer.json на пальцах."""),
    S("""<?php
declare(strict_types=1);

echo 'vendor/autoload.php';""", 2),
    Q("""`composer.lock` обычно:

- A) Игнорируют в git
- B) Коммитят, чтобы воспроизводимые сборки
- C) Генерируют только на CI""", "B", 3),
    Q("""phpunit чаще кладут в:

- A) `require`
- B) `require-dev`
- C) `suggest` обязательно""", "B", 4),
    U("""- json + lock + vendor
- require vs require-dev
- dump-autoload / install"""),
    E(
        "Выведите путь автозагрузчика Composer: `vendor/autoload.php`.",
        """<?php
declare(strict_types=1);

// TODO
""",
        [{"label": "autoload-path", "stdin": "", "expected_output": "vendor/autoload.php"}],
        6,
    ),
]))

ENTRIES.append(entry("php.files-streams", [
    T("""# Файлы и потоки

Базово:

- `file_get_contents` / `file_put_contents` — целиком в память
- `fopen` + `fread`/`fwrite` + `fclose` — контроль
- `SplFileObject` — ООП-обёртка, итерация по строкам
- `__DIR__`, `dirname`, `PATH_SEPARATOR` — пути; не конкатенируйте вслепую `../` из user input (path traversal)

Потоки (`php://stdin`, wrappers) позволяют единообразно читать источники. Для больших файлов — построчно/чанками, не `file_get_contents` на гигабайты.

Права, существование (`file_exists`, `is_readable`) и обработка ошибок — обязательны в проде.

Критерий: мысль «прочитать/записать безопасно»; в песочнице — обработка строк как «строк файла»."""),
    S("""<?php
declare(strict_types=1);

$lines = explode("\\n", "a\\nb\\nc");
echo implode('|', $lines);""", 2),
    Q("""Для огромного лога лучше:

- A) `file_get_contents` целиком
- B) Читать построчно / потоком
- C) Всегда `eval`""", "B", 3),
    Q("""Почему опасен путь из пользователя вроде `../../.env`?

- A) Медленно
- B) Path traversal — выход из разрешённой директории
- C) PHP запрещает строки с точками""", "B", 4),
    U("""- file_* vs fopen/streams
- SplFileObject
- Безопасные пути"""),
    E(
        "Разбейте строку `a\\nb\\nc` на линии и склейте через `|` → `a|b|c`.",
        """<?php
declare(strict_types=1);

$raw = "a\\nb\\nc";

// TODO: explode + implode
""",
        [{"label": "lines-join", "stdin": "", "expected_output": "a|b|c"}],
        6,
    ),
]))

ENTRIES.append(entry("php.json-http", [
    T("""# JSON и HTTP-клиент

```php
$json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
$data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
```

`JSON_THROW_ON_ERROR` (PHP 7.3+) — исключения вместо молчаливого `null`. Assoc-массив: второй аргумент `true` у `json_decode`.

## HTTP

- `file_get_contents` + `stream_context_create` — простой GET/POST
- `curl` — таймауты, заголовки, методы
- В приложениях — Guzzle/PSR-18 клиент

Всегда задавайте timeout, проверяйте HTTP-статус, не логируйте секреты. JSON API: Content-Type, схема ответа, ошибки 4xx/5xx.

Критерий: распарсить JSON API-ответ."""),
    S("""<?php
declare(strict_types=1);

$data = json_decode('{"name":"Ann"}', true, 512, JSON_THROW_ON_ERROR);
echo $data['name'];""", 2),
    Q("""Зачем `JSON_THROW_ON_ERROR`?

- A) Ускоряет parse
- B) Бросает исключение вместо тихого null при ошибке
- C) Обязателен только в CLI""", "B", 3),
    Q("""`json_decode($s, true)` возвращает:

- A) Только object `stdClass`
- B) Ассоциативный массив
- C) Всегда string""", "B", 4),
    U("""- encode/decode + flags
- Assoc vs object
- HTTP: timeouts и статусы"""),
    E(
        "Декодируйте `{\"name\":\"Ann\"}` в assoc-массив с `JSON_THROW_ON_ERROR` и выведите `name`.",
        """<?php
declare(strict_types=1);

$json = '{"name":"Ann"}';

// TODO: json_decode + echo name
""",
        [{"label": "json-name", "stdin": "", "expected_output": "Ann"}],
        6,
    ),
]))

ENTRIES.append(entry("php.datetime", [
    T("""# DateTimeImmutable

Предпочитайте **`DateTimeImmutable`**: методы `modify`/`setDate` возвращают новый объект, исходный не меняется. Мутабельный `DateTime` легко ломает состояние, разделяемое между слоями.

```php
$d = new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC'));
echo $d->format('Y'); // 2026
$next = $d->modify('+1 day');
```

- Таймзоны: явно `DateTimeZone`, не полагайтесь на default сервера в проде
- `diff` — интервал между датами
- Форматы: `format('Y-m-d H:i:s')`, atom `DATE_ATOM`
- Для «сейчас» в тестах — фиксируйте clock (или хотя бы тестовую дату)

Критерий: не использовать мутабельный DateTime без причины."""),
    S("""<?php
declare(strict_types=1);

$date = new DateTimeImmutable('2026-07-15');
echo $date->format('Y');""", 2),
    Q("""Почему Immutable предпочтительнее DateTime?

- A) Быстрее сериализация
- B) Метод modify не мутирует исходный объект
- C) Не нужна таймзона""", "B", 3),
    Q("""`$d->format('Y')` возвращает:

- A) Месяц
- B) Год (4 цифры)
- C) Timestamp""", "B", 4),
    U("""- DateTimeImmutable по умолчанию
- TZ явно
- format / modify / diff"""),
    E(
        "Создайте `DateTimeImmutable('2026-07-15')` и выведите год через `format('Y')` → `2026`.",
        """<?php
declare(strict_types=1);

$date = new DateTimeImmutable('2026-07-15');

// TODO: echo year via format('Y')
echo '';
""",
        [{"label": "year-2026", "stdin": "", "expected_output": "2026"}],
        6,
        ["format('Y')"],
    ),
]))

ENTRIES.append(entry("php.generators", [
    T("""# Generators и итераторы

`yield` создаёт `Generator` — итератор, который вычисляет значения **лениво**, не держа весь массив в памяти:

```php
function numbers(): Generator
{
    for ($i = 0; $i < 3; $i++) {
        yield $i;
    }
}

foreach (numbers() as $n) { /* ... */ }
```

`yield from` делегирует другому iterable. Генераторы реализуют Iterator; однопроходные — нельзя rewind произвольно после полного consume (новый вызов функции).

Когда: большие файлы, стримы, пайплайны. Когда нет: нужны многократные проходы/счётчик без повторного create — тогда массив или кеш.

Критерий: generator вместо огромного array."""),
    S("""<?php
declare(strict_types=1);

function numbers(): Generator
{
    for ($i = 0; $i < 3; $i++) {
        yield $i;
    }
}

echo implode(',', iterator_to_array(numbers()));""", 2),
    Q("""Главный плюс generator:

- A) Всегда быстрее array_map
- B) Ленивые значения без полного массива в памяти
- C) Автоматический SQL""", "B", 3),
    Q("""Ключевое слово для значения генератора:

- A) `return` каждый элемент
- B) `yield`
- C) `defer`""", "B", 4),
    U("""- yield / yield from
- Generator как Iterator
- Память vs многократный обход"""),
    E(
        "Напишите `numbers(): Generator` с `yield` 0..2 и выведите `0,1,2` через `implode` + `iterator_to_array`.",
        """<?php
declare(strict_types=1);

function numbers(): Generator
{
    // TODO: yield 0,1,2
}

echo implode(',', iterator_to_array(numbers()));
""",
        [{"label": "gen-012", "stdin": "", "expected_output": "0,1,2"}],
        6,
    ),
]))

ENTRIES.append(entry("php.security", [
    T("""# Безопасность PHP

Минимум на каждый день:

1. **XSS:** `htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` перед выводом в HTML.
2. **Пароли:** только `password_hash` / `password_verify` (argon2id/bcrypt), никогда MD5/SHA1 «как пароль».
3. **SQL injection:** prepared statements / query builder, никаких конкатенаций SQL с вводом.
4. **Секреты:** `.env` вне публичного root, не в git; разные ключи для окружений.
5. **CSRF / сессии / загрузки файлов** — фреймворковые middleware, whitelist расширений, хранение вне webroot.

Security — слой привычек на границах ввода/вывода, не «отдельная фича в конце».

Критерий: экранировать HTML (+ знание password_hash)."""),
    S("""<?php
declare(strict_types=1);

$input = '<b>x</b>';
echo htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');""", 2),
    Q("""Пароль пользователя хранить как:

- A) MD5 от пароля
- B) `password_hash` / verify
- C) Plain text в БД для support""", "B", 3),
    Q("""`htmlspecialchars` нужен чтобы:

- A) Ускорить шаблоны
- B) Нейтрализовать HTML/XSS при выводе
- C) Сжать JSON""", "B", 4),
    U("""- htmlspecialchars на вывод
- password_hash
- Prepared SQL, секреты в env"""),
    E(
        "Для `$input = '<b>x</b>'` выведите результат `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.",
        """<?php
declare(strict_types=1);

$input = '<b>x</b>';

// TODO: echo htmlspecialchars
""",
        [{"label": "escape-html", "stdin": "", "expected_output": "&lt;b&gt;x&lt;/b&gt;"}],
        6,
    ),
]))

ENTRIES.append(entry("php.attributes", [
    T("""# Attributes

Атрибуты PHP 8 — структурированные метаданные вместо docblock-аннотаций:

```php
#[Attribute]
final class Route
{
    public function __construct(public string $path) {}
}

#[Route('/users')]
final class UsersController {}
```

Чтение: `ReflectionClass` / `ReflectionMethod` → `getAttributes(Route::class)`.

Фреймворки (Laravel, Symfony) используют attributes для маршрутов, validation, ORM mapping. Плюс vs docblock: валидный PHP, автоcomplete, меньше парсинга строк.

`#[Attribute]` на классе атрибута задаёт, куда его можно вешать (TARGET_CLASS и т.д.).

Критерий: зачем attributes vs docblocks."""),
    S("""<?php
declare(strict_types=1);

#[Attribute]
final class Route
{
    public function __construct(public string $path) {}
}

#[Route('/ok')]
final class Demo {}

$ref = new ReflectionClass(Demo::class);
$attr = $ref->getAttributes(Route::class)[0];
echo $attr->getName() === Route::class ? 'Route' : 'no';""", 2),
    Q("""Как прочитать attribute в runtime?

- A) Только через regex docblock
- B) Reflection `getAttributes`
- C) Через `echo` класса""", "B", 3),
    Q("""Преимущество attributes над docblock-аннотациями:

- A) Меньше байт в файле всегда
- B) Настоящий PHP-синтаксис + проверка движком
- C) Работают только в Java""", "B", 4),
    U("""- #[Attribute] классы
- Reflection getAttributes
- Маршруты/валидация во фреймворках"""),
    E(
        "Объявите `#[Attribute] class Route`, повесьте `#[Route('/ok')]` на `Demo`, через Reflection выведите короткое имя `Route` (строка `Route`).",
        """<?php
declare(strict_types=1);

#[Attribute]
final class Route
{
    public function __construct(public string $path) {}
}

#[Route('/ok')]
final class Demo {}

$ref = new ReflectionClass(Demo::class);
$attr = $ref->getAttributes(Route::class)[0];

// TODO: echo 'Route' if attribute present (short name)
echo '';
""",
        [{"label": "attr-route", "stdin": "", "expected_output": "Route"}],
        6,
    ),
]))

ENTRIES.append(entry("php.testing", [
    T("""# Тестирование основ

Тест = **arrange → act → assert**. В PHPUnit: `assertSame`, `assertTrue`, `expectException`. Unit — изолированный кусок без БД/HTTP; Feature/Integration — через приложение.

Базовый sanity без фреймворка — `assert()` в CLI (в проде `zend.assertions` часто −1).

Хороший тест: детерминированный, один повод падать, читаемое имя. Плохой: зависит от порядка, sleep, внешний flaky API без stub.

Критерий: уметь сформулировать 2 assert-сценария; в упражнении — простой PASS/FAIL через сравнение."""),
    S("""<?php
declare(strict_types=1);

function add(int $a, int $b): int
{
    return $a + $b;
}

echo add(2, 3) === 5 ? 'PASS' : 'FAIL';""", 2),
    Q("""Unit-тест обычно:

- A) Ходит в реальную платёжку
- B) Проверяет кусок логики изолированно
- C) Только вручную в браузере""", "B", 3),
    Q("""Порядок AAA:

- A) Assert → Act → Arrange
- B) Arrange → Act → Assert
- C) Act → Archive → Approve""", "B", 4),
    U("""- AAA: arrange/act/assert
- Unit vs Feature
- Детерминизм и assertSame"""),
    E(
        "Функция `add(int $a, int $b): int`. Выведите `PASS`, если `add(2, 3) === 5`, иначе `FAIL`.",
        """<?php
declare(strict_types=1);

function add(int $a, int $b): int
{
    return $a + $b;
}

// TODO: echo PASS or FAIL
""",
        [{"label": "assert-pass", "stdin": "", "expected_output": "PASS"}],
        6,
    ),
]))

ENTRIES.append(entry("php.quality", [
    T("""# Качество кода

Чеклист перед PR:

1. `declare(strict_types=1);` в новых PHP-файлах
2. Форматтер: Laravel Pint / PHP-CS-Fixer — единый стиль
3. Статанализ mindset: PHPStan/Psalm уровнями — ловят null/типы до рантайма
4. Имена ясные, функции короткие, без копипасты «магических» чисел
5. Нет мёртвого кода, `dd()`/`var_dump` не в merge
6. Тесты на критичный путь зелёные

Читаемость > микрооптимизация на пустом месте. Code review ловит границы и имена лучше, чем «красоту скобок» после автоформата.

Критерий: чеклист качества перед PR."""),
    S("""<?php
declare(strict_types=1);

echo 'strict_types';""", 2),
    Q("""Что стоит делать на каждом новом PHP-файле?

- A) Отключить ошибки
- B) `declare(strict_types=1);`
- C) Подключить IonCube""", "B", 3),
    Q("""Pint / PHP-CS-Fixer нужны чтобы:

- A) Заменить PHPUnit
- B) Автоматически выровнять стиль кода
- C) Деплоить на сервер""", "B", 4),
    U("""- strict_types везде
- Форматтер + статанализ
- Читаемость и чистый PR"""),
    E(
        "Выведите литерал `strict_types` как напоминание базового качества файла.",
        """<?php
declare(strict_types=1);

// TODO: echo strict_types
""",
        [{"label": "quality-token", "stdin": "", "expected_output": "strict_types"}],
        6,
    ),
]))

ENTRIES.append(entry("php.advanced-types", [
    T("""# Продвинутые типы

## Intersection (8.1+)

`Countable&Iterator` — значение обязано удовлетворять **обоим** типам. Удобно для объектов-коллекций.

## DNF (8.2+)

Disjunctive Normal Form: `(A&B)|null` — комбинации union и intersection в каноническом виде.

## still useful

- `never` — функция не возвращает
- Covariance/contravariance: return может сужаться у наследника, параметры — осторожно расширяться по правилам LSP
- `callable` / first-class callables
- Templating через PHPDoc generics (`@template`) до runtime generics

Критерий: пример intersection type."""),
    S("""<?php
declare(strict_types=1);

function label(Countable&Iterator $items): string
{
    return 'Countable&Iterator';
}

echo label(new ArrayIterator([1, 2]));""", 2),
    Q("""Тип `A&B` означает:

- A) A или B
- B) И A, и B одновременно
- C) A минус B""", "B", 3),
    Q("""DNF-типы появились чтобы:

- A) Заменить классы
- B) Законно комбинировать union и intersection
- C) Отключить strict_types""", "B", 4),
    U("""- Intersection `A&B`
- DNF `(A&B)|C`
- never + variance basics"""),
    E(
        "Напишите функцию `label(Countable&Iterator $items): string`, возвращающую литерал `Countable&Iterator`, вызовите с `new ArrayIterator([1, 2])` и выведите результат.",
        """<?php
declare(strict_types=1);

function label(Countable&Iterator $items): string
{
    // TODO
    return '';
}

echo label(new ArrayIterator([1, 2]));
""",
        [{"label": "intersection", "stdin": "", "expected_output": "Countable&Iterator"}],
        6,
    ),
]))

ENTRIES.append(entry("php.errors-debug", [
    T("""# Ошибки и отладка

| | Exception | Error |
|--|-----------|-------|
| Смысл | Ожидаемый сбой домена/I/O | Серьёзный сбой runtime |
| Примеры | RuntimeException, InvalidArgument | TypeError, ParseError |
| Ловля | catch конкретных типов | обычно не «глушим» |

Настройки: `error_reporting`, `display_errors` (выкл в проде), лог в stderr/файл/monolog. `ErrorException` может конвертировать notices в исключения.

Отладка: Xdebug, `var_export`, воспроизводимый минимальный пример, стек `getTraceAsString()`. Сначала факт (ввод→ожидание→факт), потом гипотеза.

Критерий: различить Error и Exception."""),
    S("""<?php
declare(strict_types=1);

$t = new TypeError('boom');
echo $t instanceof Error ? 'Error' : 'Other';""", 2),
    Q("""`TypeError` является:

- A) Только Exception
- B) Подклассом Error (Throwable)
- C) Warning без объекта""", "B", 3),
    Q("""В проде `display_errors` обычно:

- A) Включают для клиентов
- B) Выключают, пишут в лог
- C) Игнорируют — PHP сам решит""", "B", 4),
    U("""- Error vs Exception
- Логи, не display в проде
- Минимальный repro для дебага"""),
    E(
        "Создайте `TypeError` и выведите `Error`, если объект `instanceof Error`.",
        """<?php
declare(strict_types=1);

$t = new TypeError('boom');

// TODO: echo Error if instanceof Error
""",
        [{"label": "instanceof-error", "stdin": "", "expected_output": "Error"}],
        6,
    ),
]))

ENTRIES.append(entry("php.capstone", [
    T("""# Capstone: мини-CLI утилита

Соберите вместе: функции, типы, JSON, аккуратный вывод. Типичный мини-CLI:

1. Принять данные (аргументы / литерал в упражнении)
2. Провалидировать
3. Посчитать
4. Вывести JSON в stdout
5. Код выхода 0/1 (в песочнице достаточно корректного JSON)

```php
$items = [1, 2, 3];
echo json_encode(['ok' => true, 'n' => count($items)], JSON_THROW_ON_ERROR);
```

В реальном CLI: `$argv`, `getopt`, исключения → stderr + `exit(1)`, enum статуса, без «тихих» catch. Здесь фиксируем контракт ответа `{"ok":true,"n":3}`.

Критерий: рабочий вывод по ТЗ."""),
    S("""<?php
declare(strict_types=1);

$items = [1, 2, 3];
echo json_encode(['ok' => true, 'n' => count($items)], JSON_THROW_ON_ERROR);""", 2),
    Q("""Для машинно-читаемого ответа CLI часто используют:

- A) Только цветной HTML
- B) JSON в stdout
- C) GUI обязательно""", "B", 3),
    Q("""`JSON_THROW_ON_ERROR` при ошибке encode:

- A) Вернёт false
- B) Бросит JsonException
- C) Проигнорирует""", "B", 4),
    U("""- Склеить функции + JSON
- Контракт stdout
- Ошибки → stderr/exit в бою"""),
    E(
        "Для `$items = [1, 2, 3]` выведите JSON `{\"ok\":true,\"n\":3}` через `json_encode` с `JSON_THROW_ON_ERROR` (без пробелов, как у json_encode по умолчанию).",
        """<?php
declare(strict_types=1);

$items = [1, 2, 3];

// TODO: echo json_encode(['ok' => true, 'n' => count($items)], JSON_THROW_ON_ERROR);
""",
        [{"label": "cli-json", "stdin": "", "expected_output": '{"ok":true,"n":3}'}],
        6,
    ),
]))

assert len(ENTRIES) == 28, len(ENTRIES)

payload = {"entries": ENTRIES}
OUT.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
print(f"wrote {len(ENTRIES)} entries -> {OUT}")
