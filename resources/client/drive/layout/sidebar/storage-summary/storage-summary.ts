import {useQuery} from '@tanstack/react-query';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {prettyBytes} from '@common/uploads/utils/pretty-bytes';
import {DriveQueryKeys} from '../../../drive-query-keys';
import {apiClient} from '@common/http/query-client';

export interface FetchStorageSummaryResponse extends BackendResponse {
  used: number;
  available: number;
}

export function useStorageSummary() {
  return useQuery(DriveQueryKeys.fetchStorageSummary, fetchStorageSummary, {
    select: formatResponse,
  });
}

function fetchStorageSummary(): Promise<FetchStorageSummaryResponse> {
  return apiClient.get(`user/space-usage`).then(response => response.data);
}

function formatResponse(response: FetchStorageSummaryResponse) {
  // null means that user has unlimited space available
  const percentage =
    response.available === null
      ? 0
      : (response.used * 100) / response.available;

  return {
    usedFormatted: prettyBytes(response.used),
    availableFormatted: prettyBytes(response.available),
    percentage,
    used: response.used,
    available: response.available,
  };
}
