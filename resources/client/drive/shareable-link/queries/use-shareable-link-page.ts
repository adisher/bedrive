import {
  QueryFunctionContext,
  QueryKey,
  useInfiniteQuery,
} from '@tanstack/react-query';
import {useParams} from 'react-router-dom';
import axios from 'axios';
import type {FetchShareableLinkResponse} from './use-entry-shareable-link';
import type {DriveEntry} from '../../files/drive-entry';
import {DriveQueryKeys} from '../../drive-query-keys';
import {
  linkPageState,
  useLinkPageStore,
} from '../shareable-link-page/link-page-store';
import {LengthAwarePaginationResponse} from '@common/http/backend-response/pagination-response';
import {BackendErrorResponse} from '@common/errors/backend-error-response';
import {apiClient} from '@common/http/query-client';

interface FetchShareableLinkPageResponse extends FetchShareableLinkResponse {
  folderChildren: LengthAwarePaginationResponse<DriveEntry>;
}

interface FetchShareableLinkPageErrorResponse extends BackendErrorResponse {
  passwordInvalid: boolean;
}

interface FetchLinkByHashParams {
  hash: string;
  page?: number;
  order?: string;
  password?: string | null;
}
function fetchLinkByHash({
  hash,
  page = 1,
  order,
  password,
}: FetchLinkByHashParams): Promise<FetchShareableLinkPageResponse> {
  return apiClient
    .get(`shareable-links/${hash}`, {
      params: {withEntries: true, page, order, password},
    })
    .then(response => response.data);
}

export function useShareableLinkPage() {
  const {hash} = useParams();
  const {orderBy, orderDir} = useLinkPageStore(s => s.activeSort);
  const order = `${orderBy}:${orderDir}`;
  const isPasswordProtected = useLinkPageStore(s => s.isPasswordProtected);
  const password = useLinkPageStore(s => s.password);

  const query = useInfiniteQuery(
    DriveQueryKeys.fetchShareableLink({hash, sort: order}),
    ({pageParam = 1}: QueryFunctionContext<QueryKey, number>) => {
      return fetchLinkByHash({hash: hash!, page: pageParam, order, password});
    },
    {
      getNextPageParam: lastResponse => {
        if (!lastResponse.folderChildren) return undefined;
        const currentPage = lastResponse.folderChildren.current_page;
        const lastPage = lastResponse.folderChildren.last_page;
        if (currentPage >= lastPage) {
          return undefined;
        }
        return currentPage + 1;
      },
      // disable query if link is password protected and correct
      // password was not entered yet, to prevent unnecessary requests
      enabled: (!!hash && !isPasswordProtected) || password != null,
      keepPreviousData: true,
      retry: (retryCount, e: unknown) => {
        if (isPasswordInvalidResponse(e)) {
          return false;
        } else {
          return retryCount <= 2;
        }
      },
      onError: (e: unknown) => {
        if (isPasswordInvalidResponse(e)) {
          return linkPageState().setIsPasswordProtected(true);
        }
      },
    }
  );

  return {
    ...query,
    link: query.data?.pages[0].link,
    entries: query.data?.pages.flatMap(p => p.folderChildren?.data),
  };
}

function isPasswordInvalidResponse(e: unknown) {
  return (
    axios.isAxiosError(e) &&
    (e.response?.data as FetchShareableLinkPageErrorResponse)?.passwordInvalid
  );
}
