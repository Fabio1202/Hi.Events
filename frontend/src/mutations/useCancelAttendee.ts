import {useMutation, useQueryClient} from "@tanstack/react-query";
import {IdParam} from "../types.ts";
import {attendeeClientPublic} from "../api/attendee.client.ts";
import {GET_ATTENDEE_PUBLIC_QUERY_KEY} from "../queries/useGetAttendeePublic.ts";
import {GET_EVENT_PUBLIC_QUERY_KEY} from "../queries/useGetEventPublic.ts";

export const useCancelAttendee = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({eventId, attendeeShortId}: {
            eventId: IdParam,
            attendeeShortId: string,
        }) => attendeeClientPublic.cancel(eventId, attendeeShortId),

        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({
                queryKey: [GET_ATTENDEE_PUBLIC_QUERY_KEY, variables.attendeeShortId]
            });
            queryClient.invalidateQueries({
                queryKey: [GET_EVENT_PUBLIC_QUERY_KEY, variables.eventId]
            });
        }
    });
}