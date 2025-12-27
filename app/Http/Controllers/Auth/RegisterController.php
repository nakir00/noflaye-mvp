<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PermissionTemplate;
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

        // Créer l'utilisateur avec le template customer par défaut
        $customerTemplate = PermissionTemplate::where('slug', 'customer')
            ->where('is_active', true)
            ->first();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'primary_template_id' => $customerTemplate?->id,
        ]);

        // Attacher le template customer à l'utilisateur
        if ($customerTemplate) {
            $user->templates()->attach($customerTemplate->id, [
                'auto_sync' => true,
            ]);
        }

        // Connecter l'utilisateur
        Auth::login($user);

        return redirect()->intended('/');
    }
}
