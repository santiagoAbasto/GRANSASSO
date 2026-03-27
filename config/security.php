<?php

$emails = array_filter(array_map(
    static fn (string $email): string => strtolower(trim($email)),
    explode(',', (string) env('ADMIN_ALLOWED_EMAILS', 'admin@gransasso.com'))
));

return [
    'admin_allowed_emails' => array_values($emails),
];
