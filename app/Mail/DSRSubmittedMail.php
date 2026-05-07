<?php

namespace App\Mail;

use App\Models\Dsr;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DSRSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dsr;
    public $htmlContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Dsr $dsr,$htmlContent)
    {
        $this->dsr = $dsr;
        $this->htmlContent = $htmlContent;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject(
            'Daily Status Report – ' .
                Carbon::parse($this->dsr->work_date)->format('jS \o\f F Y')
        )->html($this->htmlContent);
    }
}
