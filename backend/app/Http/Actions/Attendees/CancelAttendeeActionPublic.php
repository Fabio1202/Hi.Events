<?php

namespace HiEvents\Http\Actions\Attendees;

use HiEvents\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use HiEvents\DomainObjects\ProductDomainObject;
use HiEvents\DomainObjects\ProductPriceDomainObject;
use HiEvents\DomainObjects\Status\AttendeeStatus;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Exceptions\ResourceConflictException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\AttendeeRepositoryInterface;
use HiEvents\Resources\Attendee\AttendeeResourcePublic;
use HiEvents\Resources\Order\OrderResource;
use HiEvents\Services\Application\Handlers\Attendee\CancelAttendeePublicHandler;
use HiEvents\Services\Application\Handlers\Attendee\DTO\CancelAttendeePublicDTO;
use HiEvents\Services\Application\Handlers\Order\DTO\CancelOrderDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CancelAttendeeActionPublic extends BaseAction
{

    public function __construct(
        private readonly CancelAttendeePublicHandler $cancelAttendeePublicHandler,
    )
    {
    }

    /**
     * @todo move to handler
     */
    public function __invoke(int $eventId, string $attendeeShortId): JsonResponse|Response
    {
        try {
            $attendee = $this->cancelAttendeePublicHandler->handle(new CancelAttendeePublicDTO($eventId, $attendeeShortId));
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_CONFLICT);
        }

        return $this->resourceResponse(AttendeeResourcePublic::class, $attendee->setStatus(AttendeeStatus::CANCELLED->name));
    }
}
