<?php

namespace App\Data\Templates;

use App\Enums\Template as TemplateEnum;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

/**
 * Data Transfer Object for assigning permission templates to users
 *
 * This DTO encapsulates all the data needed to assign a permission template to a user.
 * Templates provide predefined sets of permissions for specific roles (e.g., SHOP_MANAGER, ADMIN).
 * Used in conjunction with AssignTemplateToUser action.
 *
 * @property int $user_id The ID of the user receiving the template
 * @property TemplateEnum $template The template enum case being assigned
 * @property bool $auto_sync Whether to automatically sync template permission updates (default: true)
 * @property Carbon|null $valid_from When the template becomes active (null = immediately)
 * @property Carbon|null $valid_until When the template expires (null = never)
 */
class AssignTemplateData extends Data
{
    public function __construct(
        #[Required]
        public int $user_id,

        #[Required]
        #[WithCast(EnumCast::class)]
        public TemplateEnum $template,

        public bool $auto_sync = true,

        public ?Carbon $valid_from = null,

        public ?Carbon $valid_until = null,
    ) {}

    /**
     * Get the template slug as a string
     *
     * Convenience method to extract the slug value from the template enum.
     * Useful when interfacing with database queries or external systems.
     *
     * @return string The template slug (e.g., 'shop_manager', 'admin', 'driver')
     *
     * @example
     * $data->templateSlug(); // Returns 'shop_manager' if template is Template::SHOP_MANAGER
     */
    public function templateSlug(): string
    {
        return $this->template->value;
    }
}
