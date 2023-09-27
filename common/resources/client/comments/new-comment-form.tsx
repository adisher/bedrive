import {Commentable} from '@common/comments/commentable';
import {Comment} from '@common/comments/comment';
import {useTrans} from '@common/i18n/use-trans';
import {useAuth} from '@common/auth/use-auth';
import {useCreateComment} from '@common/comments/requests/use-create-comment';
import {RefObject, useState} from 'react';
import clsx from 'clsx';
import {TextField} from '@common/ui/forms/input-field/text-field/text-field';
import {Avatar} from '@common/ui/images/avatar';
import {message} from '@common/i18n/message';
import {Trans} from '@common/i18n/trans';
import {useObjectRef} from '@react-aria/utils';
import {Button} from '@common/ui/buttons/button';

export interface NewCommentFormProps {
  commentable: Commentable;
  inReplyTo?: Comment;
  onSuccess?: () => void;
  className?: string;
  autoFocus?: boolean;
  inputRef?: RefObject<HTMLInputElement>;
  // additional data that should be sent to backend when creating comments
  payload?: Record<string, number | string>;
}
export function NewCommentForm({
  commentable,
  inReplyTo,
  onSuccess,
  className,
  autoFocus,
  payload,
  ...props
}: NewCommentFormProps) {
  const {trans} = useTrans();
  const {user} = useAuth();
  const createComment = useCreateComment();
  const inputRef = useObjectRef<HTMLInputElement>(props.inputRef);
  const [inputIsExpanded, setInputIsExpanded] = useState(false);
  const [inputValue, setInputValue] = useState('');

  const clearInput = () => {
    setInputIsExpanded(false);
    if (inputRef.current) {
      inputRef.current.blur();
      setInputValue('');
    }
  };

  return (
    <form
      className={clsx('py-6 flex gap-24', className)}
      onSubmit={e => {
        e.preventDefault();
        if (inputValue && !createComment.isLoading) {
          createComment.mutate(
            {
              ...payload,
              commentable,
              content: inputValue,
              inReplyTo,
            },
            {
              onSuccess: () => {
                clearInput();
                onSuccess?.();
              },
            }
          );
        }
      }}
    >
      <Avatar size="xl" circle src={user?.avatar} label={user?.display_name} />
      <div className="flex-auto">
        <div className="text-xs text-muted mb-10">
          <Trans
            message="Comment as :name"
            values={{
              name: (
                <span className="font-medium text">{user?.display_name}</span>
              ),
            }}
          />
        </div>
        <TextField
          inputRef={inputRef}
          autoFocus={autoFocus}
          inputElementType="textarea"
          inputClassName="resize-none"
          value={inputValue}
          onChange={e => setInputValue(e.target.value)}
          onFocus={() => setInputIsExpanded(true)}
          onBlur={() => {
            if (!inputValue) {
              setInputIsExpanded(false);
            }
          }}
          minLength={3}
          rows={inputIsExpanded ? 3 : 1}
          placeholder={
            inReplyTo
              ? trans(message('Write a reply'))
              : trans(message('Leave a comment'))
          }
        />
        {inputIsExpanded && (
          <div className="flex items-center gap-12 justify-end mt-12">
            <Button variant="outline" onClick={() => clearInput()}>
              <Trans message="Cancel" />
            </Button>
            <Button
              variant="outline"
              color="primary"
              type="submit"
              disabled={createComment.isLoading || inputValue.length < 3}
            >
              <Trans message="Comment" />
            </Button>
          </div>
        )}
      </div>
    </form>
  );
}
