<?php

namespace App\Mail\InternationalGatewayMails;

use App\Traits\HandlesMailTemplate;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendIgwCallSummaryReport extends Mailable
{
    use Queueable, SerializesModels, HandlesMailTemplate;

    protected $files;
    public $tableContent;
    protected $template;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($tableContent, $files, $template)
    {
        $this->tableContent = $tableContent;
        $this->files = $files;
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): SendIgwCallSummaryReport
    {
        // Commercial email addresses
//        $toAddresses = ['tarique.haque@btraccl.com'];
//        $ccAddresses = [
//            'zainal.abedin@btraccl.com',
//            'jahangir.alam@btraccl.com',
//            'fahad.islam@btraccl.com',
//            'btraccore@btraccl.com',
//            'masum.hasan@btraccl.com',
//            'CR@btraccl.com',
//            'noc@btraccl.com',
//            'billing.team@btraccl.com',
//        ];

        $toAddresses    = $this->getToAddresses($this->template);
        $ccAddresses    = $this->getCcAddresses($this->template);
        $subject        = $this->getEmailSubject($this->template);

        $mail = $this->subject($subject)
            ->to($toAddresses)
            ->cc($ccAddresses)
            ->view('emails.' . $this->getTemplateViewFile($this->template), [
                'tableContent'  => $this->tableContent,
                'template'      => $this->template,
            ]);

        $this->attachFiles($mail, $this->files);

        return $mail;
    }
}
