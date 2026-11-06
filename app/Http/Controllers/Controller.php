<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    /** EP03-05: autorización server-side disponible en todos los controladores. */
    use AuthorizesRequests;
}
