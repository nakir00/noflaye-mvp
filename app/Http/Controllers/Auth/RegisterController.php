<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * Affiche le formulaire d'inscription
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Traite l'inscription d'un nouvel utilisateur
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Créer l'utilisateur avec le rôle customer par défaut
        $customerRole = Role::where('slug', 'customer')->first();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'primary_role_id' => $customerRole?->id,
        ]);

        // Attacher le rôle customer à l'utilisateur
        if ($customerRole) {
            $user->roles()->attach($customerRole->id, [
                'scope_type' => null,
                'scope_id' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'granted_by' => null,
            ]);
        }

        // Connecter l'utilisateur
        Auth::login($user);

        return redirect()->intended('/');
    }
}
