import {useState} from 'react';
import {SvgImage} from '@common/ui/images/svg-image/svg-image';
import {TextField} from '@common/ui/forms/input-field/text-field/text-field';
import {Button} from '@common/ui/buttons/button';
import {useCheckLinkPassword} from './queries/check-link-password';
import {Trans} from '@common/i18n/trans';
import {useTrans} from '@common/i18n/use-trans';
import {ShareableLinkNavbar} from './shareable-link-navbar';
import {useParams} from 'react-router-dom';
import secureFilesSvg from '@common/auth/ui/account-settings/access-token-panel/secure-files.svg';

export function PasswordPage() {
  const {trans} = useTrans();
  const {hash} = useParams();
  const fieldLabel = trans({message: 'Password'});
  const [password, setPassword] = useState<string>('');
  const checkPassword = useCheckLinkPassword();
  const linkHash = hash ? hash.split(':')[0] : null;

  const passwordIsInvalid = checkPassword.data && !checkPassword.data.matches;

  return (
    <div className="bg-alt w-full h-full flex flex-col">
      <ShareableLinkNavbar />
      <div className="px-10 md:px-20 mx-auto my-80">
        <div className="flex flex-col md:flex-row gap-40 md:gap-14 bg items-center max-w-[560px] border rounded p-24">
          <div className="h-132">
            <SvgImage src={secureFilesSvg} />
          </div>
          <form
            onSubmit={e => {
              e.preventDefault();
              checkPassword.mutate({
                linkHash: linkHash!,
                password,
              });
            }}
          >
            <span className="text-sm">
              <Trans message="The link you are trying to access is password protected." />
            </span>
            <TextField
              autoFocus
              placeholder={fieldLabel}
              aria-label={fieldLabel}
              className="mt-10 mb-20"
              type="password"
              value={password}
              required
              errorMessage={
                passwordIsInvalid && <Trans message="Password is not valid" />
              }
              onChange={e => {
                setPassword(e.target.value);
              }}
            />
            <div className="text-right">
              <Button
                variant="flat"
                color="primary"
                type="submit"
                className="w-full md:w-auto"
                disabled={checkPassword.isLoading}
              >
                <Trans message="Enter" />
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
