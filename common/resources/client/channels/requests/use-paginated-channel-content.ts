import {useInfiniteData} from '@common/ui/infinite-scroll/use-infinite-data';
import {Channel, ChannelContentItem} from '@common/channels/channel';
import {
  channelEndpoint,
  channelQueryKey,
} from '@common/channels/requests/use-channel';
import {useChannelQueryParams} from '@common/channels/use-channel-query-params';

export function usePaginatedChannelContent<
  T extends ChannelContentItem = ChannelContentItem
>(channel: Channel<T>) {
  const queryParams = useChannelQueryParams(channel);
  return useInfiniteData<T>({
    willSortOrFilter: true,
    initialPage: channel.content,
    queryKey: channelQueryKey(channel.id),
    endpoint: channelEndpoint(channel.id),
    paginate: 'simple',
    queryParams: {
      returnContentOnly: 'true',
      ...queryParams,
    },
  });
}
