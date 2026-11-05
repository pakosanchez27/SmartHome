<?php

use Illuminate\Support\Facades\Route;

/*
 | EP-02: biblioteca viva del sistema de diseño.
 | La ruta "home" apunta temporalmente a la biblioteca UI; EP-16 la reemplaza por B01.
 | Los módulos se registran aquí conforme avanza su épica (ver app/Support/Navigation.php).
 */
Route::view('/', 'ui-kit')->name('home');
