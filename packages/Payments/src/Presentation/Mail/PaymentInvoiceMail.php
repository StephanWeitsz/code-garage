<?php

namespace CodeGarage\Payments\Presentation\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models\Payment;

class PaymentInvoiceMail extends Mailable
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
            ->subject('Payment invoice - '.$courseTitle.' ('.$this->payment->reference.')')
            ->view('payments::emails.invoice');
    }
}
