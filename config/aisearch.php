<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Python Virtual Environment Path
    |--------------------------------------------------------------------------
    |
    | The path to the Python virtual environment. This should point to the
    | directory containing the bin/python3 executable. Leave empty to use
    | the system Python installation.
    |
    */

    'venv_path' => env('AI_SEARCH_VENV_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | AI Search Script Path
    |--------------------------------------------------------------------------
    |
    | The path to the Python AI search script. This can be an absolute path
    | or a path relative to the Laravel base directory.
    |
    */

    'script_path' => env('AI_SEARCH_SCRIPT_PATH', 'scripts/ai_search_api.py'),

];
