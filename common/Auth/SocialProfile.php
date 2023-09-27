<?php namespace Common\Auth;

use App\User;
use Illuminate\Database\Eloquent\Model;

class SocialProfile extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['access_expires_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
