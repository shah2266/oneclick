<?php

namespace App\Mail\CdrStatus;

use App\Traits\HandlesMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCdrStatusReport extends Mailable
{
    use Queueable, SerializesModels, HandlesMailTemplate;

    public $cdrStatus;
    protected $template;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($cdrStatus, $template)
    {
        $this->cdrStatus = $cdrStatus;
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): SendCdrStatusReport
    {
        $toAddresses    = $this->getToAddresses($this->template);
        $ccAddresses    = $this->getCcAddresses($this->template);
        $subject        = $this->getEmailSubject($this->template);

        return $this->subject($subject)
            ->to($toAddresses)
            ->cc($ccAddresses)
            ->view('emails.' . $this->getTemplateViewFile($this->template), [
                'cdrStatus' => $this->cdrStatus,
                'template'  => $this->template
            ]);
    }
}
