<?php

namespace HiEvents\Mail\Attendee;

use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\Helper\Url;
use HiEvents\Mail\BaseMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * @uses /backend/resources/views/emails/orders/order-cancelled.blade.php
 */
class AttendeeTicketCanceledMail extends BaseMail
{
    public function __construct(
        private readonly AttendeeDomainObject $attendeeDomainObject,
        private readonly EventSettingDomainObject $eventSettings,
        private readonly EventDomainObject $event,
    )
    {
        parent::__construct();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->eventSettings->getSupportEmail(),
            subject: __('Your ticket has been cancelled'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.attendee-ticket-canceled',
            with: [
                'event' => $this->event,
                'attendee' => $this->attendeeDomainObject,
                'eventSettings' => $this->eventSettings,
            ]
        );
    }
}
