import {ThumbUpIcon} from '@common/icons/material/ThumbUp';
import {ThumbDownIcon} from '@common/icons/material/ThumbDown';
import {VotableModel} from '@common/votes/votable-model';
import {Button} from '@common/ui/buttons/button';
import {useStoreVote} from '@common/votes/requests/use-store-vote';
import {useState} from 'react';
import {FormattedNumber} from '@common/i18n/formatted-number';

interface Props {
  model: VotableModel;
  className?: string;
  showUpvotesOnly?: boolean;
}
export function ThumbButtons({model, className, showUpvotesOnly}: Props) {
  const changeVote = useStoreVote(model);

  const [upvotes, setUpvotes] = useState(model.upvotes || 0);
  const [downvotes, setDownvotes] = useState(model.downvotes || 0);
  const [currentVote, setCurrentVote] = useState(model.current_vote);

  const syncLocalState = (model: VotableModel) => {
    setUpvotes(model.upvotes);
    setDownvotes(model.downvotes);
    setCurrentVote(model.current_vote);
  };

  return (
    <div className={className}>
      <Button
        className="gap-6"
        sizeClassName="px-8 py-4"
        color={currentVote === 'upvote' ? 'primary' : undefined}
        disabled={changeVote.isLoading}
        onClick={() => {
          changeVote.mutate(
            {voteType: 'upvote'},
            {
              onSuccess: response => syncLocalState(response.model),
            }
          );
        }}
      >
        <ThumbUpIcon />
        <div>
          <FormattedNumber value={upvotes} />
        </div>
      </Button>
      {!showUpvotesOnly && (
        <Button
          className="gap-6"
          sizeClassName="px-8 py-4"
          color={currentVote === 'downvote' ? 'primary' : undefined}
          disabled={changeVote.isLoading}
          onClick={() => {
            changeVote.mutate(
              {voteType: 'downvote'},
              {
                onSuccess: response => syncLocalState(response.model),
              }
            );
          }}
        >
          <ThumbDownIcon />
          <div>
            <FormattedNumber value={downvotes} />
          </div>
        </Button>
      )}
    </div>
  );
}
