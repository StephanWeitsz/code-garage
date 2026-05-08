<?php

namespace CodeGarage\Payments\Presentation\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models\Payment;

class OutstandingPaymentReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Payment $payment)
    {
    }

    public function build(): self
    {
        $courseTitle = $this->payment->course?->title ?? 'Course';

        return $this
            ->subject('Outstanding payment reminder - '.$courseTitle)
            ->view('payments::emails.outstanding-reminder');
    }
}
