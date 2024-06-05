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
    public    $totalProfit;
    public    $dayWise;
    protected $template;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $files, $template)
    {
        $this->totalProfit      = $data['totalProfit'];
        $this->dayWise          = $data['dayWise'];
        $this->files            = $files;
        $this->template         = $template;
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
                'totalProfit'       => $this->totalProfit,
                'dayWise'           => $this->dayWise,
                'template'          => $this->template,
            ]);

        $this->attachFiles($mail, $this->files);

        return $mail;
    }
}
