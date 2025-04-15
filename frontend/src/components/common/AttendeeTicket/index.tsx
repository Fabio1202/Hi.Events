import {Card} from "../Card";
import {getAttendeeProductPrice, getAttendeeProductTitle} from "../../../utilites/products.ts";
import {Anchor, Button, CopyButton, Alert} from "@mantine/core";
import {formatCurrency} from "../../../utilites/currency.ts";
import {t} from "@lingui/macro";
import {useDisclosure} from "@mantine/hooks";
import {prettyDate} from "../../../utilites/dates.ts";
import QRCode from "react-qr-code";
import {IconCopy, IconPrinter, IconTicketOff, IconInfoCircle} from "@tabler/icons-react";
import {Attendee, Event, IdParam, Product} from "../../../types.ts";
import classes from './AttendeeTicket.module.scss';
import {CancelOrderModal} from "../../modals/CancelOrderModal";
import {useState} from "react";
import {CancelAttendeeModal} from "../../modals/CancelAttendeeModal";

interface AttendeeTicketProps {
    event: Event;
    attendee: Attendee;
    product: Product;
    hideButtons?: boolean;
}

export const AttendeeTicket = ({attendee, product, event, hideButtons = false}: AttendeeTicketProps) => {
    const productPrice = getAttendeeProductPrice(attendee, product);
    const [isCancelAttendeeModalOpen, cancelModal] = useDisclosure(false);
    const [attendeeShortId, setAttendeeShortId] = useState<string>();
    console.log(attendee.product);
    hideButtons = hideButtons || attendee.status === 'CANCELLED'

    const handleModalClick = (attendeeShortId: string, modal: { open: () => void }) => {
        setAttendeeShortId(attendeeShortId)
        modal.open();
    }

    return (
        <Card className={classes.attendee}>
            <div className={classes.attendeeInfo}>
                <div className={classes.attendeeNameAndPrice}>
                    <div className={classes.attendeeName}>
                        <h2>
                            {attendee.first_name} {attendee.last_name}
                        </h2>
                        <div className={classes.productName}>
                            {getAttendeeProductTitle(attendee)}
                        </div>
                        <Anchor href={`mailto:${attendee.email}`}>
                            {attendee.email}
                        </Anchor>
                    </div>
                    <div className={classes.productPrice}>
                        <div className={classes.badge}>
                            {productPrice > 0 && formatCurrency(productPrice, event?.currency)}
                            {productPrice === 0 && t`Free`}
                        </div>
                    </div>
                </div>
                <div className={classes.eventInfo}>
                    <div className={classes.eventName}>
                        {event?.title}
                    </div>
                    <div className={classes.eventDate}>
                        {prettyDate(event.start_date, event.timezone)}
                    </div>
                </div>
            </div>
            <div className={classes.qrCode}>
                <div className={classes.attendeeCode}>
                    {attendee.public_id}
                </div>

                <div className={classes.qrImage}>
                    {attendee.status === 'CANCELLED' && (
                        <div className={classes.cancelled}>
                            {t`Cancelled`}
                        </div>
                    )}

                    {attendee.status === 'AWAITING_PAYMENT' && (
                        <div className={classes.awaitingPayment}>
                            {t`Awaiting Payment`}
                        </div>
                    )}
                    {attendee.status !== 'CANCELLED' && <QRCode value={String(attendee.public_id)}/>}

                </div>

                {!hideButtons && (
                    <div className={classes.productButtons}>
                        <Button variant={'transparent'}
                                size={'sm'}
                                onClick={() => window?.open(`/product/${event.id}/${attendee.short_id}/print`, '_blank', 'noopener,noreferrer')}
                                leftSection={<IconPrinter size={18}/>
                                }>
                            {t`Print`}
                        </Button>

                        <CopyButton value={`${window?.location.origin}/product/${event.id}/${attendee.short_id}`}>
                            {({copied, copy}) => (
                                <Button variant={'transparent'}
                                        size={'sm'}
                                        onClick={copy}
                                        leftSection={<IconCopy size={18}/>
                                        }>
                                    {copied ? t`Copied` : t`Copy Link`}
                                </Button>
                            )}
                        </CopyButton>
                    </div>
                )}

                {(!hideButtons && event.apple_wallet_enabled) && (
                        <Button className={classes.addToWallet} onClick={() => window.location.href = `${window?.location.origin}/api/public/events/${event.id}/attendees/${attendee.short_id}/apple-wallet`}>
                            <img className={classes.appleWallet} src="/images/Add_To_Apple_Wallet.svg"
                                 alt={`Add to Apple Wallet`}/>
                        </Button>
                )}

                {!hideButtons && attendee.product?.cancelable_product && (
                    <Button variant={'transparent'}
                            size={'sm'}
                            color={'red'}
                            style={{marginTop: '20px'}}
                            onClick={() => handleModalClick(attendee.short_id, cancelModal)}
                            leftSection={<IconTicketOff size={18}/>
                            }>
                        {t`Cancel`}
                    </Button>
                )}

            </div>

            {isCancelAttendeeModalOpen && <CancelAttendeeModal onClose={cancelModal.close} attendeeShortId={attendeeShortId}/>}
        </Card>
    );
}
