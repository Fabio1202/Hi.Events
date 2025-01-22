<?php

namespace HiEvents\Http\Actions\Attendees;

use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use HiEvents\DomainObjects\Generated\EventDomainObjectAbstract;
use HiEvents\DomainObjects\ProductDomainObject;
use HiEvents\DomainObjects\ProductPriceDomainObject;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\AttendeeRepositoryInterface;
use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use HiEvents\Services\Application\Handlers\Attendee\CreateAppleWalletPasskitHandler;

class CreateApplePasskitActionPublic extends BaseAction
{
    public function __construct(
        private readonly CreateAppleWalletPasskitHandler $createAppleWalletPasskitHandler,
        private readonly AttendeeRepositoryInterface $attendeeRepository,
        private readonly EventRepositoryInterface $eventRepository
    )
    {
    }

    public function __invoke(int $eventId, string $attendeeShortId)
    {
        $attendee = $this->attendeeRepository
            ->loadRelation(new Relationship(
                domainObject: ProductDomainObject::class, name: 'product'))
            ->findFirstWhere([
                AttendeeDomainObjectAbstract::SHORT_ID => $attendeeShortId
            ]);

        $event = $this->eventRepository
            ->loadRelation(
                new Relationship(
                    domainObject: EventSettingDomainObject::class,
                    name: 'event_settings'
                )
            )
            ->findFirstWhere(
                [EventDomainObjectAbstract::ID => $eventId]
            );

        if (!$attendee || !$event) {
            return $this->notFoundResponse();
        }

        return $this->createAppleWalletPasskitHandler->handle($attendee, $event);
    }
}
