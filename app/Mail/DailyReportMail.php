<?php

namespace App\Mail;

use App\Models\DailyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public DailyReport $report;

    public function __construct(DailyReport $report)
    {
        $this->report = $report;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Rapport Journalier - {$this->report->boutique->nom} - {$this->report->date->format('d/m/Y')}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-report',
            with: [
                'report' => $this->report,
                'boutique' => $this->report->boutique,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->report->pdf_path && Storage::exists($this->report->pdf_path)) {
            $attachments[] = Attachment::fromStorage($this->report->pdf_path)
                ->as("rapport-{$this->report->date->format('Y-m-d')}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
