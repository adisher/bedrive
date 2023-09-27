import React, {useState} from 'react';
import {SearchIcon} from '@common/icons/material/Search';
import {TextField} from '@common/ui/forms/input-field/text-field/text-field';
import {SearchPage} from '../drive-page/drive-page';
import {useTrans} from '@common/i18n/use-trans';
import {useDriveStore} from '../drive-store';
import {useNavigate} from '@common/utils/hooks/use-navigate';
import {useSearchParams} from 'react-router-dom';
import {IconButton} from '@common/ui/buttons/icon-button';

export function NavbarSearch() {
  const {trans} = useTrans();
  const navigate = useNavigate();
  const activePage = useDriveStore(s => s.activePage);
  const [searchParams] = useSearchParams();
  const [inputValue, setInputValue] = useState(searchParams.get('query') || '');

  return (
    <form
      className="flex-auto max-w-620"
      onSubmit={e => {
        e.preventDefault();
        navigate(
          {
            pathname: SearchPage.path,
            search: `?query=${inputValue}`,
          },
          {replace: true}
        );
      }}
    >
      <TextField
        size="sm"
        background="bg-paper"
        value={inputValue}
        onChange={e => setInputValue(e.target.value)}
        onFocus={() => {
          if (activePage !== SearchPage) {
            navigate(SearchPage.path);
          }
        }}
        startAdornment={
          <IconButton type="submit" radius="rounded">
            <SearchIcon />
          </IconButton>
        }
        className="flex-auto max-w-620"
        placeholder={trans({message: 'Search'})}
        aria-label={trans({message: 'Search files and folders'})}
      />
    </form>
  );
}
