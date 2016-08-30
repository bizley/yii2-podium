<?php

return [
    'sourcePath' => __DIR__ . DIRECTORY_SEPARATOR . '..',
    'languages' => [
        'ru',
        'pl',
    ],
    'sort' => true,
    'except' => [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
        '/css',
        '/tests',
    ],
    'messagePath' => __DIR__,
];
