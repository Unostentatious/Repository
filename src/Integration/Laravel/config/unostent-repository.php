<?php
declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Root Directory
    |--------------------------------------------------------------------------
    |
    | The base directory in which the application is assigned the folder,
    | and the structure of the repositories where will be based on ie:
    |
    | {root}/{destination}/{placeholder}
    |
    | The default value is Lumen's helper base_path(),
    | which makes the folder structure into:
    |
    | `app/{destination}/{placeholder}`
    |
    | Note:
    |  - For Laravel apps just replace the default value to \app_path()
    |
    */
    'root' => \base_path() . '/app',

    /*
    |--------------------------------------------------------------------------
    | Destination Directory
    |--------------------------------------------------------------------------
    |
    | Define the repositories destination within the `{root}` directory,
    | when a value is given ie `Database` the default path will be:
    | `{root}/Database/{placeholder}`.
    |
    | The default value is null, which makes the folder structure into:
    | `{root}/{placeholder}`
    |
    */
    'destination' => 'Database',

    /*
    |--------------------------------------------------------------------------
    | Placeholder Directory
    |--------------------------------------------------------------------------
    |
    | Define the repositories placeholder within the `{root}\{destination}` directory,
    | when a value is given ie `Repo` the default path will be:
    |`{root}/{destination}/Repo`.
    |
    | The default value is null, which makes the folder structure into:
    | `{root}/{destination}/Repositories`
    |
    */
    'placeholder' => null
];