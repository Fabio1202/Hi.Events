import {GenericModalProps, IdParam,} from "../../../types.ts";
import {useParams} from "react-router";
import {Modal} from "../../common/Modal";
import {Alert, Button, LoadingOverlay} from "@mantine/core";
import {IconInfoCircle} from "@tabler/icons-react";
import classes from './CancelOrderPublicModal.module.scss';
import {t} from "@lingui/macro";
import {useGetEventPublic} from "../../../queries/useGetEventPublic.ts";
import {useCancelOrderPublic} from "../../../mutations/useCancelOrderPublic.ts";
import {showError, showSuccess} from "../../../utilites/notifications.tsx";

interface CancelOrderPublicModal extends GenericModalProps {
    orderShortId: string,
}

export const CancelOrderPublicModal = ({onClose, orderShortId}: CancelOrderPublicModal) => {
    const {eventId} = useParams();
    // const queryClient = useQueryClient();
    const {data: event} = useGetEventPublic(eventId);
    const cancelOrderPublicMutation = useCancelOrderPublic();

    const handleCancelOrderPublic = () => {
        cancelOrderPublicMutation.mutate({eventId, orderShortId}, {
            onSuccess: () => {
                showSuccess(t`Order has been canceled.`);
                onClose();
            },
            onError: (error: any) => {
                showError(error?.response?.data?.message || t`Failed to cancel order`);
            }
        });
    }

    if (!event) {
        return <LoadingOverlay visible/>;
    }

    return (
        <Modal
            heading={t`Cancel Order for ${event.title}`}
            opened
            onClose={onClose}
        >

            <Alert className={classes.alert} variant="light" color="red" title={t`Please Note`}
                   icon={<IconInfoCircle/>}>
                {t`After cancelling, you will not be able to restore the order. All attendees will be cancelled. This action is irreversible.`}
            </Alert>

            <Button loading={cancelOrderPublicMutation.isPending} className={'mb20'} color={'red'} fullWidth
                    onClick={handleCancelOrderPublic}>
                {t`Cancel Order`}
            </Button>
        </Modal>
    )
};
