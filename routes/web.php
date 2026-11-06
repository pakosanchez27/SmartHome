<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\HouseholdInvitationController;
use App\Http\Controllers\HouseholdMemberController;
use App\Http\Controllers\HouseholdOnboardingController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PasswordRecoveryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
 | EP-02: biblioteca viva del sistema de diseño.
 | La ruta "home" apunta temporalmente a la biblioteca UI; EP-16 la reemplaza por B01.
 | Los módulos se registran aquí conforme avanza su épica (ver app/Support/Navigation.php).
 */
Route::view('/', 'ui-kit')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/crear-hogar', [HouseholdOnboardingController::class, 'create'])->name('household.register');
    Route::post('/crear-hogar', [HouseholdOnboardingController::class, 'store'])->middleware('throttle:auth')->name('household.register.store');
    Route::get('/iniciar-sesion', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/iniciar-sesion', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/recuperar-acceso', [PasswordRecoveryController::class, 'create'])->name('password.request');
    Route::post('/recuperar-acceso', [PasswordRecoveryController::class, 'send'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [PasswordRecoveryController::class, 'edit'])->name('password.reset');
    Route::post('/restablecer-contrasena', [PasswordRecoveryController::class, 'update'])->middleware('throttle:6,1')->name('password.update');
});

Route::post('/cerrar-sesion', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/mi-perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/mi-perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/mi-perfil/contrasena', [ProfileController::class, 'password'])->name('profile.password');
});

Route::middleware('auth')->group(function () {
    Route::get('/mi-hogar', [HouseholdController::class, 'index'])->name('household.index');
    Route::post('/mi-hogar/invitaciones', [HouseholdInvitationController::class, 'store'])->name('household.invitations.store');
    Route::post('/mi-hogar/invitaciones/{invitation}/reenviar', [HouseholdInvitationController::class, 'resend'])->name('household.invitations.resend');
    Route::post('/mi-hogar/invitaciones/{invitation}/cancelar', [HouseholdInvitationController::class, 'cancel'])->name('household.invitations.cancel');

    // EP03-06: gestión de integrantes (solo administración, Policy Household::manageMembers).
    Route::post('/mi-hogar/integrantes/{member}/rol', [HouseholdMemberController::class, 'updateRole'])->name('household.members.role')->whereNumber('member');
    Route::post('/mi-hogar/integrantes/{member}/suspender', [HouseholdMemberController::class, 'suspend'])->name('household.members.suspend')->whereNumber('member');
    Route::post('/mi-hogar/integrantes/{member}/reactivar', [HouseholdMemberController::class, 'reactivate'])->name('household.members.reactivate')->whereNumber('member');
    Route::post('/mi-hogar/integrantes/{member}/retirar', [HouseholdMemberController::class, 'remove'])->name('household.members.remove')->whereNumber('member');

    Route::post('/invitacion/{token}/aceptar', [InvitationController::class, 'accept'])->name('invitation.accept');
});

Route::post('/invitacion/{token}/rechazar', [InvitationController::class, 'reject'])->name('invitation.reject');
Route::get('/invitacion/{token}', [InvitationController::class, 'show'])->name('invitation.show');

// Recorridos de demostración sin persistencia; no son módulos de producción.
Route::prefix('prototipos')->name('prototypes.')->group(function () {
    Route::view('/', 'prototypes.index')->name('index');
    Route::view('/gasto', 'prototypes.expense')->name('expense');
    Route::view('/presupuesto', 'prototypes.budget')->name('budget');
    Route::view('/menu', 'prototypes.meals')->name('meals');
    Route::view('/compras', 'prototypes.shopping')->name('shopping');
    Route::view('/despensa', 'prototypes.pantry')->name('pantry');
});
