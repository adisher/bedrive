import {useState} from 'react';
import {Button} from '@common/ui/buttons/button';
import {useShareEntry} from './queries/use-share-entry';
import {
  PermissionSelector,
  PermissionSelectorItem,
  PermissionSelectorItems,
} from './permission-selector';
import {MemberList} from './member-list';
import {
  ChipField,
  ChipValue,
} from '@common/ui/forms/input-field/chip-field/chip-field';
import {Avatar} from '@common/ui/images/avatar';
import {useTrans} from '@common/i18n/use-trans';
import {Trans} from '@common/i18n/trans';
import {DriveEntry} from '../files/drive-entry';
import {Item} from '@common/ui/forms/listbox/item';
import {isEmail} from '@common/utils/string/is-email';
import {USER_MODEL} from '@common/auth/user';
import {useNormalizedModels} from '@common/users/queries/use-normalized-models';
import {useSettings} from '@common/core/settings/use-settings';

interface SharePanelProps {
  className?: string;
  entry: DriveEntry;
}
export function SharePanel({className, entry}: SharePanelProps) {
  const {trans} = useTrans();
  const {share} = useSettings();
  const shareEntry = useShareEntry();
  const [chips, setChips] = useState<ChipValue[]>([]);
  const [isSharing, setIsSharing] = useState(false);
  const [selectedPermission, setSelectedPermission] =
    useState<PermissionSelectorItem>(PermissionSelectorItems[0]);
  const allEmailsValid = chips.every(chip => !chip.invalid);
  const [inputValue, setInputValue] = useState('');
  const query = useNormalizedModels(
    USER_MODEL,
    {perPage: 7, query: inputValue},
    {enabled: share.suggest_emails}
  );

  // show user's email, instead of name in the chip
  const displayWith = (chip: ChipValue) => chip.description || chip.name;

  return (
    <div className={className}>
      <ChipField
        value={chips}
        onChange={setChips}
        isAsync
        isLoading={query.fetchStatus === 'fetching'}
        inputValue={inputValue}
        onInputValueChange={setInputValue}
        suggestions={query.data?.results}
        displayWith={displayWith}
        validateWith={chip => {
          const invalid = !isEmail(chip.description);
          return {
            ...chip,
            invalid,
            errorMessage: invalid
              ? trans({message: 'Not a valid email'})
              : undefined,
          };
        }}
        placeholder={trans({message: 'Enter email addresses'})}
        label={<Trans message="Invite people" />}
      >
        {user => (
          <Item
            value={user.id}
            startIcon={<Avatar circle src={user.image} alt="" />}
            description={user.description}
          >
            {user.name}
          </Item>
        )}
      </ChipField>
      <div className="flex items-center gap-14 justify-between mt-14">
        <PermissionSelector
          onChange={setSelectedPermission}
          value={selectedPermission}
        />
        {chips.length ? (
          <Button
            variant="flat"
            color="primary"
            size="sm"
            disabled={isSharing || !allEmailsValid}
            onClick={() => {
              setIsSharing(true);
              shareEntry.mutate(
                {
                  emails: chips.map(c => displayWith(c)),
                  permissions: selectedPermission.value,
                  entryId: entry.id,
                },
                {
                  onSuccess: () => {
                    setChips([]);
                  },
                  onSettled: () => {
                    setIsSharing(false);
                  },
                }
              );
            }}
          >
            <Trans message="Share" />
          </Button>
        ) : null}
      </div>
      <MemberList className="mt-30" entry={entry} />
    </div>
  );
}
