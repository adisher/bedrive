import React, {Fragment, memo, useContext, useState} from 'react';
import {SiteConfigContext} from '@common/core/settings/site-config-context';
import {Link} from 'react-router-dom';
import {Comment} from '@common/comments/comment';
import {useAuth} from '@common/auth/use-auth';
import {UserAvatar} from '@common/ui/images/user-avatar';
import {Button} from '@common/ui/buttons/button';
import {Trans} from '@common/i18n/trans';
import {NewCommentForm} from '@common/comments/new-comment-form';
import {User} from '@common/auth/user';
import {Commentable} from '@common/comments/commentable';
import {useDeleteComments} from '@common/comments/requests/use-delete-comments';
import {DialogTrigger} from '@common/ui/overlays/dialog/dialog-trigger';
import {queryClient} from '@common/http/query-client';
import {ConfirmationDialog} from '@common/ui/overlays/dialog/confirmation-dialog';
import {FormattedDuration} from '@common/i18n/formatted-duration';
import {useIsMobileMediaQuery} from '@common/utils/hooks/is-mobile-media-query';
import {ThumbButtons} from '@common/votes/thumb-buttons';
import {ReplyIcon} from '@common/icons/material/Reply';
import {MoreVertIcon} from '@common/icons/material/MoreVert';
import {
  Menu,
  MenuItem,
  MenuTrigger,
} from '@common/ui/navigation/menu/menu-trigger';
import {FormattedRelativeTime} from '@common/i18n/formatted-relative-time';
import {useSubmitReport} from '@common/reports/requests/use-submit-report';

interface CommentListItemProps {
  comment: Comment;
  commentable: Commentable;
  canDelete?: boolean;
}
export function CommentListItem({
  comment,
  commentable,
  // user can delete comment if they have created it, or they have relevant permissions on commentable
  canDelete,
}: CommentListItemProps) {
  const isMobile = useIsMobileMediaQuery();
  const {user, hasPermission} = useAuth();
  const [replyFormVisible, setReplyFormVisible] = useState(false);
  const showReplyButton =
    user != null &&
    !comment.deleted &&
    !isMobile &&
    comment.depth < 5 &&
    hasPermission('comments.create');

  return (
    <div
      style={{paddingLeft: `${comment.depth * 20}px`}}
      onClick={() => {
        if (isMobile) {
          setReplyFormVisible(!replyFormVisible);
        }
      }}
    >
      <div className="flex items-start gap-24 py-18 min-h-70 group">
        <UserAvatar user={comment.user} size="xl" circle />
        <div className="text-sm flex-auto">
          <div className="flex items-center gap-8 mb-4">
            {comment.user && <UserDisplayName user={comment.user} />}
            <time className="text-muted text-xs">
              <FormattedRelativeTime date={comment.created_at} />
            </time>
            {comment.position ? (
              <Position commentable={commentable} position={comment.position} />
            ) : null}
          </div>
          <div>
            {comment.deleted ? (
              <span className="text-muted italic">
                <Trans message="[COMMENT DELETED]" />
              </span>
            ) : (
              comment.content
            )}
          </div>
          {!comment.deleted && (
            <div className="flex items-center gap-8 mt-10 -ml-8">
              {showReplyButton && (
                <Button
                  sizeClassName="text-sm px-8 py-4"
                  startIcon={<ReplyIcon />}
                  onClick={() => setReplyFormVisible(!replyFormVisible)}
                >
                  <Trans message="Reply" />
                </Button>
              )}
              <ThumbButtons model={comment} showUpvotesOnly />
              <CommentOptionsTrigger
                comment={comment}
                canDelete={canDelete}
                user={user}
              />
            </div>
          )}
        </div>
      </div>
      {replyFormVisible ? (
        <NewCommentForm
          className={!comment?.depth ? 'pl-20' : undefined}
          commentable={commentable}
          inReplyTo={comment}
          autoFocus
          onSuccess={() => {
            setReplyFormVisible(false);
          }}
        />
      ) : null}
    </div>
  );
}

interface PositionProps {
  commentable: Commentable;
  position: number;
}
const Position = memo(({commentable, position}: PositionProps) => {
  if (!commentable.duration) return null;
  const seconds = (position / 100) * (commentable.duration / 1000);
  return (
    <span className="text-muted text-xs">
      <Trans
        message="at :position"
        values={{
          position: <FormattedDuration seconds={seconds} />,
        }}
      />
    </span>
  );
});

interface DeleteCommentsButtonProps {
  comment: Comment;
  canDelete?: boolean;
  user: User | null;
}
export function CommentOptionsTrigger({
  comment,
  canDelete,
  user,
}: DeleteCommentsButtonProps) {
  const deleteComments = useDeleteComments();
  const reportComment = useSubmitReport(comment);

  const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
  const showDeleteButton =
    (comment.user_id === user?.id || canDelete) && !comment.deleted;

  const handleReport = () => {
    reportComment.mutate({});
  };

  const handleDelete = (isConfirmed: boolean) => {
    setIsDeleteDialogOpen(false);
    if (isConfirmed) {
      deleteComments.mutate(
        {commentIds: [comment.id]},
        {
          onSuccess: () => {
            queryClient.invalidateQueries(['comment']);
          },
        }
      );
    }
  };

  return (
    <Fragment>
      <MenuTrigger>
        <Button startIcon={<MoreVertIcon />} sizeClassName="text-sm px-8 py-4">
          <Trans message="More" />
        </Button>
        <Menu>
          <MenuItem value="report" onSelected={() => handleReport()}>
            <Trans message="Report comment" />
          </MenuItem>
          {showDeleteButton && (
            <MenuItem
              value="delete"
              onSelected={() => setIsDeleteDialogOpen(true)}
            >
              <Trans message="Delete" />
            </MenuItem>
          )}
        </Menu>
      </MenuTrigger>
      <DialogTrigger
        type="modal"
        isOpen={isDeleteDialogOpen}
        onClose={isConfirmed => handleDelete(isConfirmed)}
      >
        <ConfirmationDialog
          isDanger
          title={<Trans message="Delete comment?" />}
          body={
            <Trans message="Are you sure you want to delete this comment?" />
          }
          confirm={<Trans message="Delete" />}
        />
      </DialogTrigger>
    </Fragment>
  );
}

interface UserDisplayNameProps {
  user: User;
}
function UserDisplayName({user}: UserDisplayNameProps) {
  const {auth} = useContext(SiteConfigContext);
  if (auth.getUserProfileLink) {
    return (
      <Link
        to={auth.getUserProfileLink(user)}
        className="hover:underline text-base font-medium"
      >
        {user.display_name}
      </Link>
    );
  }
  return <div className="text-base font-medium">{user.display_name}</div>;
}
