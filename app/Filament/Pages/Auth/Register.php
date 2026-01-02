<?php

namespace App\Filament\Pages\Auth;

use App\Models\PermissionTemplate;
use Filament\Auth\Pages\Register;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Illuminate\Database\Eloquent\Model;

class AdminRegister extends Register
{
    /**
     * Customize the registration form
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getTemplateFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * Add template selection field
     */
    protected function getTemplateFormComponent(): Component
    {
        return Select::make('template_id')
            ->label('Account Type')
            ->options(
                PermissionTemplate::where('is_active', true)
                    ->whereNotIn('slug', ['admin', 'super_admin'])
                    ->pluck('name', 'id')
            )
            ->default(function () {
                return PermissionTemplate::where('slug', 'customer')
                    ->where('is_active', true)
                    ->first()?->id;
            })
            ->required()
            ->helperText('Choose your account type')
            ->native(false);
    }

    /**
     * Handle user registration
     */
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        // Assign selected template
        if (isset($data['template_id'])) {
            $template = PermissionTemplate::find($data['template_id']);

            if ($template) {
                // Attach template
                $user->templates()->attach($template->id, [
                    'auto_sync' => true,
                    'valid_from' => now(),
                ]);

                // Set as primary template
                $user->update(['primary_template_id' => $template->id]);
            }
        } else {
            // Fallback: assign customer template
            $customerTemplate = PermissionTemplate::where('slug', 'customer')->first();
            if ($customerTemplate) {
                $user->templates()->attach($customerTemplate->id, [
                    'auto_sync' => true,
                    'valid_from' => now(),
                ]);
                $user->update(['primary_template_id' => $customerTemplate->id]);
            }
        }

        return $user;
    }
}
