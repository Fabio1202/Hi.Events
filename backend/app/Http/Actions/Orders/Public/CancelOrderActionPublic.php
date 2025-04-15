<?php

namespace HiEvents\Http\Actions\Orders\Public;

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
use HiEvents\Resources\Order\OrderResourcePublic;
use HiEvents\Services\Application\Handlers\Attendee\CancelAttendeePublicHandler;
use HiEvents\Services\Application\Handlers\Attendee\DTO\CancelAttendeePublicDTO;
use HiEvents\Services\Application\Handlers\Order\DTO\CancelOrderDTO;
use HiEvents\Services\Application\Handlers\Order\DTO\CancelOrderPublicDTO;
use HiEvents\Services\Application\Handlers\Order\Public\CancelOrderPublicHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CancelOrderActionPublic extends BaseAction
{

    public function __construct(
        private readonly CancelOrderPublicHandler $cancelOrderPublicHandler,
    )
    {
    }

    /**
     * @todo move to handler
     */
    public function __invoke(int $eventId, string $orderShortId): JsonResponse|Response
    {
        try {
            $order = $this->cancelOrderPublicHandler->handle(new CancelOrderPublicDTO($eventId, $orderShortId));
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_CONFLICT);
        }

        return $this->resourceResponse(OrderResourcePublic::class, $order->setStatus(OrderStatus::CANCELLED->name));
    }
}
