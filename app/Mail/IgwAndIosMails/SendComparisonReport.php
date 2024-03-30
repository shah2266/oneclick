<?php

namespace App\Mail\IgwAndIosMails;

use App\Traits\HandlesMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendComparisonReport extends Mailable
{
    use Queueable, SerializesModels, HandlesMailTemplate;

    public $incoming;
    public $outgoing;
    protected $files;
    protected $template;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $files, $template)
    {
        $this->incoming = $data['incoming'];
        $this->outgoing = $data['outgoing'];
        $this->files = $files;
        $this->template = $template;
    }

    public function build(): SendComparisonReport
    {
        $toAddresses    = $this->getToAddresses($this->template);
        $ccAddresses    = $this->getCcAddresses($this->template);
        $subject        = $this->getEmailSubject($this->template);

        $mail = $this->subject($subject)
            ->to($toAddresses)
            ->cc($ccAddresses)
            ->view('emails.' . $this->getTemplateViewFile($this->template), [
                'incoming' => $this->incoming,
                'outgoing' => $this->outgoing,
                'template' => $this->template,
            ]);

        $this->attachFiles($mail, $this->files);

        return $mail;
    }
}
