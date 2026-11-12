<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Где разрешено проверять проекты
    |--------------------------------------------------------------------------
    |
    | Аудит читает файлы проекта на этой машине. Чтобы не читать всю файловую
    | систему, разрешаем только рабочие папки пользователя и storage/app/projects.
    |
    */
    'allowed_roots' => array_values(array_filter([
        getenv('USERPROFILE') ? getenv('USERPROFILE').DIRECTORY_SEPARATOR.'Desktop' : null,
        getenv('USERPROFILE') ? getenv('USERPROFILE').DIRECTORY_SEPARATOR.'Documents' : null,
        getenv('HOME') ? getenv('HOME').DIRECTORY_SEPARATOR.'Desktop' : null,
        storage_path('app/projects'),
        base_path(),
    ])),
];
