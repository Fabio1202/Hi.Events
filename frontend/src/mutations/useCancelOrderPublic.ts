import {useMutation, useQueryClient} from "@tanstack/react-query";
import {IdParam} from "../types.ts";
import {attendeeClientPublic} from "../api/attendee.client.ts";
import {GET_ATTENDEE_PUBLIC_QUERY_KEY} from "../queries/useGetAttendeePublic.ts";
import {GET_EVENT_PUBLIC_QUERY_KEY} from "../queries/useGetEventPublic.ts";
import {orderClientPublic} from "../api/order.client.ts";
import {GET_ORDER_PUBLIC_QUERY_KEY} from "../queries/useGetOrderPublic.ts";
import {GET_ORDER_QUERY_KEY} from "../queries/useGetOrder.ts";
import {GET_EVENT_QUERY_KEY} from "../queries/useGetEvent.ts";

export const useCancelOrderPublic = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({eventId, orderShortId}: {
            eventId: IdParam,
            orderShortId: string,
        }) => orderClientPublic.cancel(eventId, orderShortId),

        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({
                queryKey: [GET_ATTENDEE_PUBLIC_QUERY_KEY]
            });
            queryClient.invalidateQueries({
                queryKey: [GET_ORDER_QUERY_KEY]
            });
            queryClient.invalidateQueries({
                queryKey: [GET_ORDER_PUBLIC_QUERY_KEY]
            });
            queryClient.invalidateQueries({
                queryKey: [GET_EVENT_PUBLIC_QUERY_KEY]
            });
            queryClient.invalidateQueries({
                queryKey: [GET_EVENT_QUERY_KEY]
            });
        }
    });
}