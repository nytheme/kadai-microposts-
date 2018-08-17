<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 自分自身ではないかの確認
        $its_me = $this->id == $userId;
    
        if ($exist || $its_me) {
            // 既にフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }

    public function unfollow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 自分自身ではないかの確認
        $its_me = $this->id == $userId;
    
        if ($exist && !$its_me) {
            // 既にフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }

    public function is_following($userId) {
        return $this->followings()->where('follow_id', $userId)->exists();
    }

    //タイムライン用のマイクロポストを取得するためのメソッド
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()-> pluck('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $follow_user_ids);
        
        $favorite_user_ids = $this->favoritings()-> pluck('users.id')->toArray();
        $favorite_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $favorite_user_ids);
    }
    
   //課題favoriteここから
    public function favoritings()
    {
        return $this->belongsToMany(Micropost::class, 'user_favorite', 'user_id', 'favorite_id')->withTimestamps();
    }

    public function favoriters()
    {
        return $this->belongsToMany(User::class, 'user_favorite', 'favorite_id', 'user_id')->withTimestamps();
    }
    
    public function favorite($userId)
    {
        // 既にファボしているかの確認
        $exist = $this->is_favoriting($userId);
        // 自分自身ではないかの確認(自分もファボできる使用)
        //$its_me = $this->id == $userId;
    
        if ($exist ) {
            // 既にファボしていれば何もしない
            return false;
        } else {
            // 未ファボであればファボする
            $this->favoritings()->attach($userId);
            return true;
        }
    }

    public function unfavorite($userId)
    {
        // 既にファボしているかの確認
        $exist = $this->is_favoriting($userId);
        // 自分自身ではないかの確認
        //$its_me = $this->id == $userId;
    
        if ($exist ) {
            // 既にファボしていればファボを外す
            $this->favoritings()->detach($userId);
            return true;
        } else {
            // 未ファボであれば何もしない
            return false;
        }
    }
    
    public function is_favoriting($userId) {
        return $this->favoritings()->where('favorite_id', $userId)->exists();
    }
    
}
