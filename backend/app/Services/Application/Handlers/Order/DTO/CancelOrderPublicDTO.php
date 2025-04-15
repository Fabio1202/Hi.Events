<?php

namespace HiEvents\Services\Application\Handlers\Order\DTO;

use HiEvents\DataTransferObjects\BaseDTO;

class CancelOrderPublicDTO extends BaseDTO
{
    public function __construct(
        public int $eventId,
        public string $orderShortId
    )
    {
    }
}
