-- Векторные эмбеддинги для content_atoms (дедуп, похожесть)
CREATE EXTENSION IF NOT EXISTS vector;

-- Триграммы для полнотекстового поиска по канону (опционально на MVP)
CREATE EXTENSION IF NOT EXISTS pg_trgm;
