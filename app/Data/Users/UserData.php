<?php

namespace App\Data\Users;

use App\Enums\Template as TemplateEnum;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required, Email]
        public string $email,

        public ?TemplateEnum $primary_template = null,

        public ?string $phone = null,

        public bool $is_active = true,
    ) {}
}
