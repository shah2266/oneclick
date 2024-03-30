<?php

namespace App\Mail\IgwAndIosMails;

use App\Traits\HandlesMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendIofInOutDayWiseReport extends Mailable
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
    public function build(): SendIofInOutDayWiseReport
    {

        //Commercial email addresses
//        $toAddresses = ['corporate.affairs@iofbd.com'];
//        $ccAddresses = [
//            'zainal.abedin@btraccl.com',
//            'rokib.mahmud@btraccl.com',
//            'noc@iofbd.com',
//            'btraccore@btraccl.com',
//            'CR@btraccl.com',
//            'noc@btraccl.com',
//            'billing@iofbd.com',
//            'dipendu.saha@iofbd.com',
//            'billing.team@btraccl.com'
//        ];


        $toAddresses = $this->getToAddresses($this->template);
        $ccAddresses = $this->getCcAddresses($this->template);
        $subject = $this->getEmailSubject($this->template);

        $mail = $this->subject($subject)
            ->to($toAddresses)
            ->cc($ccAddresses)
            ->view('emails.' . $this->getTemplateViewFile($this->template), [
                'template'      => $this->template,
                'tableContent'  => $this->tableContent
                ]);

        $this->attachFiles($mail, $this->files);

        return $mail;
    }
}
