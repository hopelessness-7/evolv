<?php

return [

    'default' => env('LLM_DRIVER', 'ollama'),

    'drivers' => [

        'ollama' => [
            'host' => env('OLLAMA_HOST', 'http://ollama:11434'),
            'timeout' => (int) env('OLLAMA_TIMEOUT', 120),
            'models' => [
                'default' => env('OLLAMA_CHAT_MODEL', 'llama3.2'),
                'embed' => env('OLLAMA_EMBED_MODEL', 'nomic-embed-text'),
                'chat' => env('OLLAMA_CHAT_MODEL', 'llama3.2'),
                'code_review' => env('OLLAMA_CODE_MODEL', 'qwen2.5-coder:7b'),
                'coach' => env('OLLAMA_COACH_MODEL', 'llama3.2'),
                'psych_session' => env('OLLAMA_PSYCH_MODEL', 'qwen2.5:7b'),
                'spec_review' => env('OLLAMA_SPEC_MODEL', 'qwen2.5:7b'),
                'daily_plan' => env('OLLAMA_PLAN_MODEL', 'phi3:mini'),
                'structured_json' => env('OLLAMA_JSON_MODEL', 'llama3.2'),
            ],
        ],

    ],

    'embed_dim' => (int) env('QDRANT_EMBED_DIM', 768),

];
