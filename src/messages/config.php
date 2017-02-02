<?php

return [
    'sourcePath' => __DIR__ . DIRECTORY_SEPARATOR . '..',
    'languages' => ['en-US', 'ja', 'pl', 'ru', 'es'],
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
    ],
    'messagePath' => __DIR__,
];
