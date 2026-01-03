<?php

namespace App\Data\Templates;

use App\Enums\Template as TemplateEnum;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class TemplateData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        public string $slug,

        public ?string $description = null,

        public ?int $parent_id = null,

        public int $level = 0,

        public bool $is_active = true,

        public bool $is_system = false,

        public bool $auto_sync_users = false,
    ) {}

    /**
     * Create from enum
     */
    public static function fromEnum(TemplateEnum $template): self
    {
        return new self(
            name: $template->label(),
            slug: $template->value,
            description: "Template: {$template->label()}",
            is_active: true,
            is_system: true,
        );
    }
}
