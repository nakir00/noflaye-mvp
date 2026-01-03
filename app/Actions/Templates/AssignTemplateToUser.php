<?php

namespace App\Actions\Templates;

use App\Data\Templates\AssignTemplateData;
use App\Models\PermissionTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignTemplateToUser
{
    use AsAction;

    public function handle(AssignTemplateData $data): bool
    {
        return DB::transaction(function () use ($data) {
            $user = User::findOrFail($data->user_id);
            $template = PermissionTemplate::where('slug', $data->template->value)
                ->firstOrFail();

            // Check if already assigned
            if ($user->templates()->where('id', $template->id)->exists()) {
                return false;
            }

            // Attach template
            $user->templates()->attach($template->id, [
                'auto_sync' => $data->auto_sync,
                'valid_from' => $data->valid_from ?? now(),
                'valid_until' => $data->valid_until,
            ]);

            // Set as primary if none exists
            if (! $user->primary_template_id) {
                $user->update(['primary_template_id' => $template->id]);
            }

            // Log activity
            activity()
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties([
                    'template' => $data->template->value,
                    'template_name' => $template->name,
                ])
                ->log('template_assigned');

            Cache::tags(['users', "user.{$user->id}", 'templates'])->flush();

            return true;
        });
    }

    public function asJob(AssignTemplateData $data): void
    {
        $this->handle($data);
    }
}
