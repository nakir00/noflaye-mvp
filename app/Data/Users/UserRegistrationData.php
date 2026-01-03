<?php

namespace App\Data\Users;

use App\Enums\Template as TemplateEnum;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UserRegistrationData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required, Email]
        public string $email,

        #[Required, Min(8)]
        public string $password,

        public ?TemplateEnum $initial_template = null,

        public ?string $phone = null,
    ) {}
}
