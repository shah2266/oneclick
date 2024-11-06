<?php

namespace App\Traits;
use App\Models\NoclickCommand;
use App\Models\NoclickMailTemplate;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait HandlesMailTemplate {
    use ReportDateHelper;
    /**
     * @param $template
     * @return array
     */
    protected function getToAddresses($template): array
    {
        $app = $this->getActiveSetting();
        if(Str::lower($app->environment) === 'local') {
            return ['shah.alam@btraccl.com']; //Test email
        } else {
            return $this->trimExplodedString($template['to_email_addresses']);
        }
    }

    /**
     * @param $template
     * @return array
     */
    protected function getCcAddresses($template): array
    {
        $app = $this->getActiveSetting();
        if(Str::lower($app->environment) === 'local') {
            return ['shaha2266@gmail.com']; // Test email
        } else {
            // Call the trimExplodedString function with the 'cc_email_addresses' value from the template
            return $this->trimExplodedString($template['cc_email_addresses']);
        }
    }

    /**
     * @param $template
     * @return string
     */
    protected function getEmailSubject($template): string
    {
        // Return formatted subject
        return $this->formatSubject($template);
    }

    /**
     * @param $template
     * @return string
     */
    protected function formatSubject($template): string
    {

        $subject = $template['subject'];
        $hasSubjectDate = strtolower($template['has_subject_date']);
        $exist_this_subject = "BTrac IOS Wise & IGW to BTrac IOS IN wise Call Summary of";
        $inline_date = ($template['subject'] === $exist_this_subject)
            ? Carbon::parse(self::getDateToUse())->addDay()->format('d-M-Y')
            : Carbon::parse(self::getDateToUse())->format('d-M-Y');
        $subject_partials = $subject . ' ' . $inline_date;

        // Subject formatted
        if ($hasSubjectDate === 'before subject') {
            return Carbon::parse(self::getDateToUse())->format('d-M-Y') . ' ' . $subject;
        } elseif ($hasSubjectDate === 'after subject') {
            return $subject_partials;
        } else {
            return $subject;
        }

    }

    /**
     * @param $template
     * @return string
     */
    protected function getTemplateViewFile($template): string
    {
        return str_replace('_', '-', $template['has_custom_mail_template']);
    }

    /**
     * @param $mail
     * @param $files
     * @return void
     */
    protected function attachFiles($mail, $files): void
    {
        foreach ($files as $file) {
            $attachmentPath = $file;
            if (file_exists($attachmentPath)) {
                $mail->attach($attachmentPath, ['as' => basename($file)]);
            } else {
                Log::channel('noclick')->error("File not found: $attachmentPath");
            }
        }
    }

    /**
     * @param $command
     * @return array
     */
    protected function findMailTemplate($command): array
    {
        return NoclickCommand::where('command', $command)
            ->with('noclickMailTemplate')
            ->firstOrFail()
            ->noclickMailTemplate
            ->toArray();
    }

    /**
     * @param $templateName
     * @return array
     */
    protected function findMailTemplateByName($templateName): array
    {
        return NoclickMailTemplate::where('template_name', $templateName)
            ->firstOrFail()
            ->toArray();
    }

    /**
     * @param $string
     * @return array
     */
    protected function trimExplodedString($string): array
    {
        // Explode the string by commas to get an array of substrings
        return array_map('trim', explode(',', $string));
    }

    protected function maskedEmailAddress()
    {
        return env('MAIL_FROM_ADDRESS');
    }

    protected function maskedEmailName()
    {
        return env('APP_NAME');
    }

    protected function getActiveSetting()
    {
        return Setting::where('status', 'active')->first();
    }
}
