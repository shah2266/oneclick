<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_TransportException;

class TestMailOutlook extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return string
     */
    public function build()
    {
        try {
            $transport = (new Swift_SmtpTransport('mail2.btraccl.com', 143, 'ssl'))
                ->setUsername('shah.alam@btraccl.com')
                ->setPassword('Shah$2266');

            $mailer = new Swift_Mailer($transport);

            // Send a test email to check if the configuration is correct
            $message = (new Swift_Message('Test Email'))
                ->setFrom(['shah.alam@btraccl.com' => 'Shah Alam'])
                ->setTo(['shah.alam@btraccl.com' => 'Recipient Name'])
                ->setBody('This is a test email.');

            $result = $mailer->send($message);

            if ($result) {
                return 'ok';
            } else {
                return 'Failed to send email.';
            }
        } catch (Swift_TransportException $e) {
            return $e->getMessage();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
