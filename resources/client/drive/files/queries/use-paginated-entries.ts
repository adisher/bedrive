import {InfiniteData, useInfiniteQuery} from '@tanstack/react-query';
import {useSearchParams} from 'react-router-dom';
import {LengthAwarePaginationResponse} from '@common/http/backend-response/pagination-response';
import {DriveEntry, DriveFolder} from '../drive-entry';
import {driveState, useDriveStore} from '../../drive-store';
import {apiClient, queryClient} from '@common/http/query-client';
import {DriveQueryKeys} from '../../drive-query-keys';
import {SortColumn, SortDirection} from '../../layout/sorting/available-sorts';
import {useActiveWorkspaceId} from '@common/workspace/active-workspace-id-context';
import {makeFolderPage, SearchPage} from '../../drive-page/drive-page';
import {useEffect} from 'react';
import {shallowEqual} from '@common/utils/shallow-equal';

export interface DriveApiIndexParams {
  orderBy?: SortColumn;
  orderDir?: SortDirection;
  folderId?: string | number | null;
  query?: string;
  filters?: string;
  deletedOnly?: boolean;
  starredOnly?: boolean;
  sharedOnly?: boolean;
  perPage?: number;
  page?: number;
  recentOnly?: boolean;
  workspaceId?: number | null;
  pageId?: number | string;
}

interface EntriesPaginationResponse
  extends LengthAwarePaginationResponse<DriveEntry> {
  folder?: DriveFolder;
}

function fetchEntries(
  params: DriveApiIndexParams
): Promise<EntriesPaginationResponse> {
  return apiClient
    .get('drive/file-entries', {
      params,
    })
    .then(response => response.data);
}

const setActiveFolder = (response: InfiniteData<EntriesPaginationResponse>) => {
  const firstPage = response.pages[0];
  const newFolder = firstPage.folder;
  const currentPage = driveState().activePage;

  if (
    newFolder &&
    currentPage &&
    currentPage.id === newFolder.hash &&
    // only update page if once to set the folder or if permissions change, to keep page reference as stable as possible
    (!currentPage.folder ||
      !shallowEqual(newFolder.permissions, currentPage.folder?.permissions))
  ) {
    driveState().setActivePage(makeFolderPage(newFolder));
  }
  return response;
};

export function usePaginatedEntries() {
  const page = useDriveStore(s => s.activePage);
  const sortDescriptor = useDriveStore(s => s.sortDescriptor);
  const [searchParams] = useSearchParams();
  const {workspaceId} = useActiveWorkspaceId();
  const params: DriveApiIndexParams = {
    pageId: page?.id,
    ...page?.queryParams,
    ...Object.fromEntries(searchParams),
    folderId: page?.isFolderPage ? page.id : null,
    workspaceId,
    ...sortDescriptor,
  };

  // if we have no search query, there's no need to call the API, show no results message instead
  const isDisabledInSearch =
    page === SearchPage && !params.query && !params.filters;

  const query = useInfiniteQuery(
    DriveQueryKeys.fetchEntries(params),
    ({pageParam = 1}) => {
      return fetchEntries({...params, page: pageParam});
    },
    {
      getNextPageParam: lastResponse => {
        const currentPage = lastResponse.current_page;
        const lastPage = lastResponse.last_page;
        if (currentPage >= lastPage) {
          return undefined;
        }
        return currentPage + 1;
      },
      enabled: page != null && !isDisabledInSearch,
    }
  );

  // need to do this in effect, to avoid react errors about
  // multiple components re-rendering at the same time
  useEffect(() => {
    if (query.data?.pages[0].folder) {
      setActiveFolder(query.data);
    }
  }, [query.data]);

  return query;
}

export function getAllEntries() {
  const caches = queryClient.getQueriesData<
    InfiniteData<EntriesPaginationResponse>
  >(DriveQueryKeys.fetchEntries());
  return caches.reduce<DriveEntry[]>((all, cache) => {
    const current = cache[1] ? cache[1].pages.flatMap(p => p.data) : [];
    return [...all, ...current];
  }, []);
}
