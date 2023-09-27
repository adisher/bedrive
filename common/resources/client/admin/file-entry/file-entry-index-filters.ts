import {
  BackendFilter,
  FilterControlType,
  FilterOperator,
  FilterSelectControl,
} from '../../datatable/filters/backend-filter';
import {message} from '../../i18n/message';
import {USER_MODEL} from '../../auth/user';
import {
  createdAtFilter,
  updatedAtFilter,
} from '@common/datatable/filters/timestamp-filters';

export const FILE_ENTRY_TYPE_FILTER: BackendFilter<FilterSelectControl> = {
  key: 'type',
  label: message('Type'),
  description: message('Type of the file'),
  defaultOperator: FilterOperator.eq,
  control: {
    type: FilterControlType.Select,
    defaultValue: '05',
    options: [
      {key: '02', label: message('Text'), value: 'text'},
      {
        key: '03',
        label: message('Audio'),
        value: 'audio',
      },
      {
        key: '04',
        label: message('Video'),
        value: 'video',
      },
      {
        key: '05',
        label: message('Image'),
        value: 'image',
      },
      {key: '06', label: message('PDF'), value: 'pdf'},
      {
        key: '07',
        label: message('Spreadsheet'),
        value: 'spreadsheet',
      },
      {
        key: '08',
        label: message('Word Document'),
        value: 'word',
      },
      {
        key: '09',
        label: message('Photoshop'),
        value: 'photoshop',
      },
      {
        key: '10',
        label: message('Archive'),
        value: 'archive',
      },
      {
        key: '11',
        label: message('Folder'),
        value: 'folder',
      },
    ],
  },
};

export const FILE_ENTRY_INDEX_FILTERS: BackendFilter[] = [
  FILE_ENTRY_TYPE_FILTER,
  {
    key: 'public',
    label: message('Visibility'),
    description: message('Whether file is publicly accessible'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.Select,
      defaultValue: '01',
      options: [
        {key: '01', label: message('Private'), value: false},
        {key: '02', label: message('Public'), value: true},
      ],
    },
  },
  createdAtFilter({
    description: message('Date file was uploaded'),
  }),
  updatedAtFilter({
    description: message('Date file was last changed'),
  }),
  {
    key: 'owner_id',
    label: message('Uploader'),
    description: message('User that this file was uploaded by'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.SelectModel,
      model: USER_MODEL,
    },
  },
];
