.PHONY: help up up-infra down restart logs ps migrate seed test smoke build-sandbox

# Загрузка .env / Load .env if present
ifneq (,$(wildcard .env))
    include .env
    export
endif

COMPOSE := docker compose
COMPOSE_FILE := docker-compose.yml

help: ## Показать команды | Show available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-18s\033[0m %s\n", $$1, $$2}'

up: ## Запустить все сервисы | Start all services (detached)
	$(COMPOSE) -f $(COMPOSE_FILE) up -d --build

up-infra: ## Только инфра: postgres, redis, ollama, nginx | Infra only
	$(COMPOSE) -f $(COMPOSE_FILE) up -d postgres redis ollama nginx

down: ## Остановить контейнеры | Stop and remove containers
	$(COMPOSE) -f $(COMPOSE_FILE) down

restart: down up ## Перезапуск | Restart all services

logs: ## Логи всех сервисов | Tail logs from all services
	$(COMPOSE) -f $(COMPOSE_FILE) logs -f

ps: ## Список контейнеров | Show running containers
	$(COMPOSE) -f $(COMPOSE_FILE) ps

migrate: ## Миграции БД | Run database migrations (core + gateway)
	@echo "Running core migrations..."
	$(COMPOSE) -f $(COMPOSE_FILE) exec -T core php artisan migrate --force
	@echo "Running gateway migrations..."
	$(COMPOSE) -f $(COMPOSE_FILE) exec -T gateway npm run migration:run

seed: ## Сиды для разработки | Seed development data
	$(COMPOSE) -f $(COMPOSE_FILE) exec -T core php artisan db:seed --force

test: ## Тесты всех сервисов | Run service test suites
	@echo "Core (PHPUnit)..."
	-$(COMPOSE) -f $(COMPOSE_FILE) exec -T core php artisan test
	@echo "Gateway (Jest)..."
	-$(COMPOSE) -f $(COMPOSE_FILE) exec -T gateway npm test
	@echo "LLM worker (pytest)..."
	-$(COMPOSE) -f $(COMPOSE_FILE) exec -T llm-worker uv run pytest
	@echo "Practice runner (go test)..."
	-$(COMPOSE) -f $(COMPOSE_FILE) exec -T practice-runner go test ./...

build-sandbox: ## Собрать образ node-learn | Build node-learn sandbox image
	docker build -t evolv/node-learn:latest sandbox-images/node-learn

smoke: ## Сквозная проверка | Quick health check (stack must be running)
	@echo "==> Register"
	@curl -sf -X POST http://localhost/api/auth/register \
		-H "Content-Type: application/json" \
		-d '{"email":"smoke@evolv.local","password":"password123","name":"Smoke"}' \
		|| echo "(register may fail if user exists — OK)"
	@echo "\n==> Login"
	@TOKEN=$$(curl -sf -X POST http://localhost/api/auth/login \
		-H "Content-Type: application/json" \
		-d '{"email":"smoke@evolv.local","password":"password123"}' \
		| grep -o '"accessToken":"[^"]*"' | cut -d'"' -f4); \
		echo "Token: $${TOKEN:0:20}..."; \
		curl -sf http://localhost/api/ping -H "Authorization: Bearer $$TOKEN" && echo "\nGateway->Core ping OK"
