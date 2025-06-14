<?php

namespace App\Mail;

use App\Models\AlumniMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AlumniInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The alumni message instance.
     *
     * @var AlumniMessage
     */
    public $alumniMessage;

    /**
     * Create a new message instance.
     *
     * @param AlumniMessage $message
     * @return void
     */
    public function __construct(AlumniMessage $message)
    {
        $this->alumniMessage = $message;
        Log::info('Creating new AlumniInvitation email for message ID: ' . $message->id);
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $recipientEmail = $this->alumniMessage->alumni->email ?? 'unknown';
        Log::info("Preparing email envelope for: {$recipientEmail}", [
            'subject' => $this->alumniMessage->subject,
            'message_id' => $this->alumniMessage->id
        ]);

        return new Envelope(
            subject: $this->alumniMessage->subject,
            // Adding from address if you want to override default
            from: config('mail.from.address'),
            replyTo: config('mail.reply_to.address', config('mail.from.address')),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
public function content()
{
    Log::info('Building email content', [
        'message_id' => $this->alumniMessage->id,
        'has_content' => !empty($this->alumniMessage->content)
    ]);

    return new Content(
        markdown: 'emails.alumni.invitation',
        with: [
            'content' => $this->alumniMessage->content,
            'alumni' => $this->alumniMessage->alumni,
            'subject' => $this->alumniMessage->subject,
            'url' => $this->getUrl() // Add this line
        ]
    );
}

/**
 * Get the URL for the email button or header link
 *
 * @return string
 */
protected function getUrl()
{
    // Customize this based on your app logic
    // Example: route to alumni dashboard or invitation page
    return 'https://your-app.com/invitations/'  . $this->alumniMessage->id;
}

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        // Example of how you might add attachments in the future
        // return [
        //     Attachment::fromStorage('/path/to/file'),
        // ];
        
        return [];
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Failed to send AlumniInvitation email', [
            'message_id' => $this->alumniMessage->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Update the message status to failed
        $this->alumniMessage->update([
            'status' => 'failed',
            'error' => $exception->getMessage()
        ]);
    }
}