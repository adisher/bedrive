export interface Vote {
  id: number;
  user_id: number;
  user_ip: string;
  vote_type: 'upvote' | 'downvote';
}
