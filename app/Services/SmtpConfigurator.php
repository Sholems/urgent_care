<?php

namespace App\Services;

use App\Models\SmtpSetting;

class SmtpConfigurator
{
    /**
     * Apply SMTP settings from the database to the runtime mail configuration.
     */
    public static function apply(): void
    {
        $smtp = SmtpSetting::first();

        if (!$smtp) {
            return;
        }

        config([
            'mail.default' => $smtp->mailer ?? 'smtp',
            'mail.mailers.smtp.host' => $smtp->host,
            'mail.mailers.smtp.port' => $smtp->port,
            'mail.mailers.smtp.username' => $smtp->username,
            'mail.mailers.smtp.password' => $smtp->password,
            'mail.mailers.smtp.encryption' => $smtp->encryption,
            'mail.from.address' => $smtp->from_address,
            'mail.from.name' => $smtp->from_name,
        ]);
    }
}

