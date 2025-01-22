<?php

namespace HiEvents\Services\Application\Handlers\Attendee;

use DateTime;
use DateTimeZone;
use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\EventDomainObject;
use Illuminate\Support\Facades\App;
use PKPass\PKPass;

class CreateAppleWalletPasskitHandler
{

    public function handle(AttendeeDomainObject $attendee, EventDomainObject $event): \Illuminate\Http\Response
    {
        // Laravel set language locael
        //App::setLocale($attendee->getLocale());

        $pass = new PKPass(config('pkpass.passCertificatePath'), config('pkpass.passCertificatePassword'));

        //Format start date as 2025-05-28T18:00+01:00
        $start_date = new DateTime($event->getStartDate());
        $start_date->setTimezone(new DateTimeZone($event->getTimezone()));
        $start_date = $start_date->format('Y-m-d\TH:iP');

        //Hex (6 digits) to RGB
        function hex2rgb($hex) {
            $hex = str_replace("#", "", $hex);
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
            return "rgb($r, $g, $b)";
        }

        $event_settings = $event->getEventSettings();


        $pass_definition = [
            "description"       => $event->getDescription(),
            "formatVersion"     => 1,
            "organizationName"  => $event->getOrganizer()->getName(),
            "passTypeIdentifier"=> config('pkpass.passTypeIdentifier'),
            "serialNumber"      => $event->getShortId() . '-' . $attendee->getPublicId(),
            "teamIdentifier"    => config('pkpass.teamIdentifier'),
            "groupingIdentifier"=> $event->getShortId(),
            "foregroundColor"   => hex2rgb($event_settings->getHomepageSecondaryTextColor()),
            "backgroundColor"   => hex2rgb($event_settings->getHomepagePrimaryColor()),
            "labelColor"        => hex2rgb($event_settings->getHomepageSecondaryTextColor()),
            "logoText"          => $event->getTitle(),
            //"voided" => "false",
            "barcode" => [
                "message"   => $attendee->getPublicId(),
                "format"    => "PKBarcodeFormatQR",
                "altText"   => $attendee->getPublicId(),
                "messageEncoding"=> "utf-8",
            ],
            "semantics" => [
                "eventEndDate" => "2025-05-28T18:00+01:00",
                "eventType" => "PKEventTypeConference",
            ],
            "eventTicket" => [
                "headerFields" => [
                    /*[
                        "key" => "eventStartDate",
                        "label" => "Absolventenfeier 2025",
                        "value" => $start_date,
                        "type" => "date",
                        "dateStyle" => "PKDateStyleShort",
                    ]*/
                ],
                "primaryFields" => [
                    [
                        "key" => "name",
                        "label" => "Name",
                        "value" => $attendee->getFirstName() . ' ' . $attendee->getLastName(),
                    ],
                    [
                        "key" => "ticketType",
                        "label" => "Ticket Type",
                        "value" => $attendee->getProduct()->getTitle(),
                    ]
                ],
                "secondaryFields" => [
                    [
                        "key" => "eventStartDate",
                        "label" => "Date",
                        "value" => $start_date,
                        "type" => "date",
                        "dateStyle" => "PKDateStyleLong",
                        "timeStyle" => "PKDateStyleShort",
                    ]
                ],
                "auxiliaryFields" => [
                    [
                        "key" => "eventName",
                        "label" => "Event",
                        "value" => $event->getTitle(),
                    ]
                ],
                "backFields" => [
                    [
                        "key" => "venueRoom",
                        "label" => "Room",
                        "value" => "Aula",
                    ],
                    [
                        "key" => "contact",
                        "label" => "Contact",
                        "value" => __("If you have any questions or need assistance, feel free to reach out to our friendly support team at") . ": " . $event->getOrganizer()->getEmail(),
                    ]
                ]
            ],
        ];

        $pass->setData($pass_definition);

        // Definitions can also be set from a JSON string
// $pass->setPassDefinition(file_get_contents('/path/to/pass.json));

// Add assets to the PKPass package
//    $pass->addAsset(base_path('resources/assets/wallet/background.png'));
//    $pass->addAsset(base_path('resources/assets/wallet/thumbnail.png'));
        $pass->addFile(base_path('resources/assets/wallet/icon.png'));
        $pass->addFile(base_path('resources/assets/wallet/logo.png'));

        $pkpass = $pass->create();


        return new \Illuminate\Http\Response($pkpass, 200, [
            'Content-Transfer-Encoding' => 'binary',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="pass.pkpass"',
            'Content-length' => strlen($pkpass),
            'Content-Type' => PKPass::MIME_TYPE,
            'Pragma' => 'no-cache',
        ]);
    }

}
