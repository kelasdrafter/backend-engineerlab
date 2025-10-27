<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseEnrollmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $course;
    public $batch;
    public $transaction;
    public $whatsappUrl;
    public $batchStartDate;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Course $course, $batch, Transaction $transaction)
    {
        $this->user = $user;
        $this->course = $course;
        $this->batch = $batch;
        $this->transaction = $transaction;
        
        // Determine WhatsApp URL (batch priority, fallback to course)
        $this->whatsappUrl = $batch ? $batch->whatsapp_group_url : $course->whatsapp_group_url;
        
        // Format batch start date if available
        $this->batchStartDate = $batch ? \Carbon\Carbon::parse($batch->start_date)->format('d M Y') : null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Selamat! Pendaftaran Kursus Berhasil - ' . $this->course->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'course-enrollment',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}