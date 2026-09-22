<?php

return [
    'xp' => [
        'step_complete' => 10,
        'quiz_correct' => 5,
        'practice_accepted' => 20,
    ],

    /*
    | Catalog of puzzle piece ids that can appear in node meta['puzzle_pieces'].
    | Used for docs/UI; unlock is driven by LearningPath StepCompleted events.
    */
    'pieces' => [
        'php_brace',
        'php_dollar',
        'php_arrow',
        'laravel_blade',
        'laravel_route',
        'laravel_eloquent',
    ],
];
