<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    protected $toAddresses;
    protected $ccAddresses;
    protected $htmlTemplate;
    protected $files;
    protected $directory;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $toAddresses, $ccAddresses, $htmlTemplate, $files, $directory)
    {
        $this->subject = $subject;
        $this->toAddresses = $toAddresses;
        $this->ccAddresses = $ccAddresses;
        $this->htmlTemplate = $htmlTemplate;
        $this->files = $files;
        $this->directory = $directory;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): SendMailTemplate
    {
        $mail = $this->subject($this->subject)
            ->from('automail.billing@btraccl.com', 'Bangla Trac Billing')
            ->to($this->toAddresses)
            ->cc($this->ccAddresses)
            ->view('emails.' . $this->htmlTemplate);

        foreach ($this->files as $file) {
            $attachmentPath = $file;
            if (file_exists($attachmentPath)) {
                $mail->attach($attachmentPath, ['as' => basename($file)]);
            } else {
                Log::channel('noclick')->error("File not found: $attachmentPath");
            }
        }

        return $mail;
    }
}
