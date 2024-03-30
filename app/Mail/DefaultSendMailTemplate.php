<?php

namespace App\Mail;

use App\Traits\HandlesMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DefaultSendMailTemplate extends Mailable
{
    use Queueable, SerializesModels, HandlesMailTemplate;

    protected $files;
    protected $template;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($files, $template)
    {
        $this->files = $files;
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): DefaultSendMailTemplate
    {
        $toAddresses = $this->getToAddresses($this->template);
        $ccAddresses = $this->getCcAddresses($this->template);
        $subject = $this->getEmailSubject($this->template);

        $mail = $this->subject($subject)
            ->to($toAddresses)
            ->cc($ccAddresses)
            ->view('emails.' . $this->getTemplateViewFile($this->template), ['template' => $this->template]);

        $this->attachFiles($mail, $this->files);

        return $mail;
    }
}
