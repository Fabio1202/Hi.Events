import {GenericModalProps, IdParam,} from "../../../types.ts";
import {useParams} from "react-router";
import {Modal} from "../../common/Modal";
import {Alert, Button, LoadingOverlay} from "@mantine/core";
import {IconInfoCircle} from "@tabler/icons-react";
import classes from './CancelAttendeeModal.module.scss';
import {t} from "@lingui/macro";
import {useCancelAttendee} from "../../../mutations/useCancelAttendee.ts";
import {showError, showSuccess} from "../../../utilites/notifications.tsx";
import {useGetAttendeePublic} from "../../../queries/useGetAttendeePublic.ts";
import {useGetEventPublic} from "../../../queries/useGetEventPublic.ts";

interface CancelAttendeeModalProps extends GenericModalProps {
    attendeeShortId: string,
}

export const CancelAttendeeModal = ({onClose, attendeeShortId}: CancelAttendeeModalProps) => {
    const {eventId} = useParams();
    // const queryClient = useQueryClient();
    const {data: attendee} = useGetAttendeePublic(eventId, attendeeShortId)
    const {data: event, data: {products} = {}} = useGetEventPublic(eventId);
    const cancelAttendeeMutation = useCancelAttendee()

    const handleCancelAttendee = () => {
        cancelAttendeeMutation.mutate({eventId, attendeeShortId}, {
            onSuccess: () => {
                showSuccess(t`Attendee has been canceled.`);
                onClose();
            },
            onError: (error: any) => {
                showError(error?.response?.data?.message || t`Failed to cancel attendee`);
            }
        });
    }

    if (!attendee || !event) {
        return <LoadingOverlay visible/>;
    }

    return (
        <Modal
            heading={t`Cancel Attendee ${attendee.first_name} ${attendee.last_name}`}
            opened
            onClose={onClose}
        >

            <Alert className={classes.alert} variant="light" color="red" title={t`Please Note`}
                   icon={<IconInfoCircle/>}>
                {t`After cancelling, you will not be able to restore this attendee. This action is irreversible.`}
            </Alert>

            <Button loading={cancelAttendeeMutation.isPending} className={'mb20'} color={'red'} fullWidth
                    onClick={handleCancelAttendee}>
                {t`Cancel Attendee`}
            </Button>
        </Modal>
    )
};
