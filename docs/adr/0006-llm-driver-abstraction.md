# 0006. LLM через абстракцию драйверов / LLM via driver abstraction

- **Status:** Accepted
- **Date:** 2026-05-28

---

## Русский

### Контекст

Платформе требуется LLM для генерации уроков, оценки решений и построения RAG-контекста. На MVP — самый дешёвый и доступный вариант, в перспективе возможен переход на коммерческий API ради качества/скорости.

Варианты MVP: Ollama (локально), OpenAI API, Anthropic API, OpenRouter (агрегатор).

### Решение

В модуле `AI` интерфейс `LlmDriver` и реализация `OllamaDriver`. Контракт минимальный:

```php
interface LlmDriver
{
    public function embed(string $text): array;          // float[]
    public function chat(array $messages, array $opts = []): string;
    public function chatStream(array $messages, array $opts = []): iterable;
}
```

Текущая привязка — **Ollama** в compose-стеке. Модели:

- Эмбеддинги: `nomic-embed-text` (768 dim)
- Чат: `llama3.2` (или другая, конфигурируемо через `.env`)

Прод-варианты (`OpenAiDriver`, `AnthropicDriver`, `OpenRouterDriver`) добавляются без изменения вызывающего кода.

### Последствия

**Плюсы:** zero-cost dev; переключение прод-провайдера — это один service binding; legко поддерживать несколько провайдеров параллельно (A/B).

**Минусы:** Ollama на CPU медленный — для итеративной отладки UX это нормально, для нагрузочных сценариев нужен GPU или внешний API.

**Связь с Qdrant:** размерность эмбеддингов фиксируется выбором модели (768 для nomic-embed-text). При смене модели — пересоздание коллекции в Qdrant и переиндексация.

---

## English

### Context

Need LLM for lesson generation, solution review, and RAG. MVP optimises for cost and zero-setup; production may switch to paid APIs for quality.

### Decision

`LlmDriver` interface in the `AI` module with `OllamaDriver` as the MVP implementation. Models: `nomic-embed-text` (768d) for embeddings, `llama3.2` for chat. Production drivers (`OpenAiDriver`, etc.) plug in via service container without touching call sites.

### Consequences

**Pros:** zero-cost dev; provider swap is a single binding change.

**Cons:** Ollama on CPU is slow; embedding model change requires Qdrant collection rebuild.
