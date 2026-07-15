<?php

namespace App\Services;

class WhatsAppLink
{
    public function make(?string $rawPhone, string $message): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $rawPhone);

        if ($phone === '') {
            return null;
        }

        if (in_array(strlen($phone), [10, 11], true)) {
            $phone = "55{$phone}";
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }
}
