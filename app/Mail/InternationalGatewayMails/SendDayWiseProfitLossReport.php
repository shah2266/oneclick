<?php

namespace App\Mail\InternationalGatewayMails;

use App\Traits\HandlesMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendDayWiseProfitLossReport extends Mailable
{
    use Queueable, SerializesModels, HandlesMailTemplate;

    protected $files;
    public $dayWise;
    protected $template;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($dayWise, $files, $template)
    {
        $this->dayWise  = $dayWise;
        $this->files    = $files;
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): SendDayWiseProfitLossReport
    {
        $toAddresses    = $this->getToAddresses($this->template);
        $ccAddresses    = $this->getCcAddresses($this->template);
        $subject        = $this->getEmailSubject($this->template);

        $mail = $this->subject($subject)
            ->to($toAddresses)
            ->cc($ccAddresses)
            ->view('emails.' . $this->getTemplateViewFile($this->template), [
                'dayWise'  => $this->dayWise,
                'template' => $this->template,
            ]);

        $this->attachFiles($mail, $this->files);

        return $mail;
    }
}
