import {useAuth} from '@common/auth/use-auth';
import {Trans} from '@common/i18n/trans';
import {Link} from 'react-router-dom';
import {LinkStyle} from '@common/ui/buttons/external-link';
import {MessageDescriptor} from '@common/i18n/message-descriptor';

interface Props {
  message: MessageDescriptor;
}
export function AccountRequiredCard({message}: Props) {
  const {user} = useAuth();
  if (user) return null;
  return (
    <div className="border border-dashed py-30 px-20 my-40 mx-auto text-center max-w-850 rounded">
      <div className="text-xl font-semibold mb-8">
        <Trans message="Account required" />
      </div>
      <div className="text-muted text-base">
        <Trans
          {...message}
          values={{
            l: parts => (
              <Link className={LinkStyle} to="/login">
                {parts}
              </Link>
            ),
            r: parts => (
              <Link className={LinkStyle} to="/register">
                {parts}
              </Link>
            ),
          }}
        />
      </div>
    </div>
  );
}
