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
    | The default value is Laravel's helper base_path(),
    | which makes the folder structure into:
    |
    | `app/{destination}/{placeholder}`
    |
    */
    'root' => base_path(),

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
    | Example class implementation:
    | `\App\Repositories\ExampleRepository::class`
    */
    'destination' => null,

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
    | `{root}/{placeholder}/Repositories`
    */
    'placeholder' => null
];