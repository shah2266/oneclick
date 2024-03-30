<?php

namespace App\Mail\IgwOperatorSwitchMails;

use App\Traits\HandlesMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendIosBtrcReport extends Mailable
{
    use Queueable, SerializesModels, HandlesMailTemplate;

    public $tblContent;
    protected $template;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($tblContent, $template)
    {
        $this->tblContent = $tblContent;
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): SendIosBtrcReport
    {

        $toAddresses    = $this->getToAddresses($this->template);
        $ccAddresses    = $this->getCcAddresses($this->template);
        $subject        = $this->getEmailSubject($this->template);

        return $this->subject($subject)
            ->to($toAddresses)
            ->cc($ccAddresses)
            ->view('emails.' . $this->getTemplateViewFile($this->template), [
                'tblContent'    => $this->tblContent,
                'template'      => $this->template
            ]);
    }
}
