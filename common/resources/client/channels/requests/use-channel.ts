import {useQuery} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {useParams} from 'react-router-dom';
import {Channel} from '@common/channels/channel';
import {useChannelQueryParams} from '@common/channels/use-channel-query-params';

interface Response extends BackendResponse {
  channel: Channel;
}

export function useChannel(
  slugOrId?: string | number,
  userParams?: Record<string, string | null>
) {
  const params = useParams();
  const channelId = slugOrId || params.slugOrId!;
  const queryParams = useChannelQueryParams(undefined, userParams);
  return useQuery(
    // only refetch when channel ID or restriction changes and not query params.
    // content will be re-fetched in channel content components
    channelQueryKey(channelId, {restriction: queryParams.restriction}),
    () => fetchChannel(channelId, queryParams)
  );
}

export function channelQueryKey(
  slugOrId: number | string,
  params?: Record<string, string | null>
) {
  return ['channel', `${slugOrId}`, params];
}

export function channelEndpoint(slugOrId: number | string) {
  return `channel/${slugOrId}`;
}

function fetchChannel(
  slugOrId: number | string,
  params: Record<string, string | number | undefined | null> = {}
): Promise<Response> {
  return apiClient
    .get(channelEndpoint(slugOrId), {params})
    .then(response => response.data);
}
