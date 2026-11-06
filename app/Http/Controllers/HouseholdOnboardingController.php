<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * EP03-03: alta inicial del hogar.
 * Un visitante nuevo registra su cuenta y crea el hogar en un solo flujo;
 * queda automáticamente como primer administrador (RG-01).
 */
class HouseholdOnboardingController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
            'household_name' => ['required', 'string', 'max:120'],
            'timezone' => ['required', 'string', 'timezone:all'],
        ]);

        $user = null;
        $household = null;

        // El usuario y el hogar nacen juntos: si el hogar falla, no queda usuario huérfano (RG-02/RG-10).
        DB::transaction(function () use ($validated, &$user, &$household) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $household = new Household([
                'name' => $validated['household_name'],
                'currency' => 'MXN',
                'timezone' => $validated['timezone'],
            ]);

            $household->creator()->associate($user);
            $household->save();
            $household->users()->attach($user, ['role' => 'administrator']);
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('current_household_id', $household->id);

        return redirect()->route('home')->with('status', '¡Listo! Tu hogar «'.$validated['household_name'].'» quedó preparado. Podrás invitar a tu pareja desde Mi hogar.');
    }
}
