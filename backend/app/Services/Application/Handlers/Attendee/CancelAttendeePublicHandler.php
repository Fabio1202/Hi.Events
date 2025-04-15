<?php

namespace HiEvents\Services\Application\Handlers\Attendee;

use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use HiEvents\DomainObjects\Generated\OrderDomainObjectAbstract;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\ProductDomainObject;
use HiEvents\DomainObjects\ProductPriceDomainObject;
use HiEvents\DomainObjects\Status\AttendeeStatus;
use HiEvents\Exceptions\ResourceConflictException;
use HiEvents\Mail\Attendee\AttendeeTicketCanceledMail;
use HiEvents\Mail\Order\OrderCancelled;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\AttendeeRepositoryInterface;
use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Services\Application\Handlers\Attendee\DTO\CancelAttendeePublicDTO;
use HiEvents\Services\Application\Handlers\Order\DTO\CancelOrderDTO;
use HiEvents\Services\Domain\Order\OrderCancelService;
use HiEvents\Services\Domain\Product\ProductQuantityUpdateService;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\DatabaseManager;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

class CancelAttendeePublicHandler
{
    public function __construct(
        //private readonly OrderCancelService       $orderCancelService,
        private readonly AttendeeRepositoryInterface $attendeeRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly ProductQuantityUpdateService $productQuantityUpdateService,
        private readonly DatabaseManager          $databaseManager,
        private readonly Mailer                       $mailer,
    )
    {
    }

    /**
     * @throws Throwable
     * @throws ResourceConflictException
     */
    public function handle(CancelAttendeePublicDTO $cancelAttendeePublicDTO): AttendeeDomainObjectAbstract
    {
        return $this->databaseManager->transaction(function () use ($cancelAttendeePublicDTO) {
            $attendee = $this->attendeeRepository
                ->loadRelation(new Relationship(
                    domainObject: ProductDomainObject::class,
                    name: 'product'))
                ->findFirstWhere([
                    AttendeeDomainObjectAbstract::SHORT_ID => $cancelAttendeePublicDTO->attendeeShortId,
                    AttendeeDomainObjectAbstract::EVENT_ID => $cancelAttendeePublicDTO->eventId,
                ]);

            $event = $this->eventRepository
                ->loadRelation(new Relationship(
                    domainObject: EventSettingDomainObject::class,
                    name: 'event_settings'))
                ->findById($cancelAttendeePublicDTO->eventId);

            if (!$attendee) {
                throw new ResourceNotFoundException(__('Order not found'));
            }

            if (!$attendee->getProduct()->getCancelableProduct()) {
                throw new ResourceConflictException(__('Product not cancelable'));
            }

            if ($attendee->getStatus() === AttendeeStatus::CANCELLED->name) {
                throw new ResourceConflictException(__('Attendee already cancelled'));
            }

            $this->attendeeRepository->updateWhere(
                attributes: [
                    'status' => AttendeeStatus::CANCELLED->name,
                ],
                where: [
                    'short_id' => $attendee->getShortId(),
                    'event_id' => $cancelAttendeePublicDTO->eventId,
                ]
            );

            $this->mailer
                ->to($attendee->getEmail())
                ->locale($attendee->getLocale())
                ->send(new AttendeeTicketCanceledMail(
                    $attendee,
                    $event->getEventSettings(),
                    $event,
                ));

            $this->productQuantityUpdateService->decreaseQuantitySold($attendee->getProductPriceId());

            return $this->attendeeRepository->findById($attendee->getId());
        });
    }
}
