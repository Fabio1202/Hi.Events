<?php

namespace HiEvents\Services\Application\Handlers\Attendee\DTO;

use HiEvents\DataTransferObjects\BaseDTO;

class CancelAttendeePublicDTO extends BaseDTO
{
    public function __construct(
        public int $eventId,
        public string $attendeeShortId
    )
    {
    }
}
