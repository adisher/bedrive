import {Channel} from '@common/channels/channel';
import {useParams, useSearchParams} from 'react-router-dom';
import {useBackendFilterUrlParams} from '@common/datatable/filters/backend-filter-url-params';
import {BackendFiltersUrlKey} from '@common/datatable/filters/backend-filters-url-key';

export function useChannelQueryParams(
  channel?: Channel,
  userParams?: Record<string, string | null>
) {
  const params = useParams();
  const [searchParams] = useSearchParams();
  const {encodedFilters} = useBackendFilterUrlParams();

  const queryParams = {
    ...userParams,
    restriction: params.restriction || '',
    order: searchParams.get('order'),
    [BackendFiltersUrlKey]: encodedFilters,
    paginate: 'simple',
  };

  // always set default channel order to keep query key stable
  if (!queryParams.order && channel) {
    queryParams.order = channel.config.contentOrder || 'popularity:desc';
  }

  return queryParams;
}
