<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\LaravelDriver\MailerSendTrait;

class MailerSendTest extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    /**
     * Create a new message instance.
     */

    public $file;
    public $filename;
    public $arrData;

    public function __construct($arrData)
    {
        //
        $this->arrData = $arrData;
        // $this->filename = $filename;
    }

    // public function build()
    // {
    //     return $this->view('mails.slip')
    //         ->attachData($this->file, $this->filename, [
    //             'mime' => 'application/pdf',
    //         ]);
    // }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->arrData[0],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $to = Arr::get($this->to, '0.address');

        // Additional options for MailerSend API features
        // $this->mailersend(
        //     template_id: null,
        //     variables: [
        //         new Variable($to, ['name' => 'Your Name'])
        //     ],
        //     tags: ['tag'],
        //     personalization: [
        //         new Personalization($to, [
        //             'var' => 'variable',
        //             'number' => 123,
        //             'object' => [
        //                 'key' => 'object-value'
        //             ],
        //             'objectCollection' => [
        //                 [
        //                     'name' => 'John'
        //                 ],
        //                 [
        //                     'name' => 'Patrick'
        //                 ]
        //             ],
        //         ])
        //     ],
        //     precedenceBulkHeader: true,
        //     sendAt: new Carbon('2022-01-28 11:53:20'),
        // );

        //     // Additional options for MailerSend API features
        //     // $this->mailersend(
        //     //     template_id: null,
        //     //     variables: [
        //     //         new Variable($to, ['name' => 'Your Name'])
        //     //     ],
        //     //     tags: ['tag'],
        //     //     personalization: [
        //     //         new Personalization($to, [
        //     //             'var' => 'variable',
        //     //             'number' => 123,
        //     //             'object' => [
        //     //                 'key' => 'object-value'
        //     //             ],
        //     //             'objectCollection' => [
        //     //                 [
        //     //                     'name' => 'John'
        //     //                 ],
        //     //                 [
        //     //                     'name' => 'Patrick'
        //     //                 ]
        //     //             ],
        //     //         ])
        //     //     ],
        //     //     precedenceBulkHeader: true,
        //     //     sendAt: new Carbon('2022-01-28 11:53:20'),
        //     // );


        // ERROR BUG IN text
        return new Content(
            view: 'mails.slip',
            text: 'mails.slip',
            with: [
                "arrData" => $this->arrData
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    // public function attachments(): array
    // {
    //     return [
    //         Attachment::fromData(fn () => $this->file, $this->filename)
    //             ->withMime('application/pdf'),
    //     ];
    // }
}
