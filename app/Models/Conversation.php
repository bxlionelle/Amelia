<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['user_one', 'user_two'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversations', 'user_one', 'user_two');
    }

    public function scopeBetween($query, $userOne, $userTwo)
    {
        return $query->where(function ($q) use ($userOne, $userTwo) {
            $q->where('user_one', $userOne)->where('user_two', $userTwo);
        })->orWhere(function ($q) use ($userOne, $userTwo) {
            $q->where('user_one', $userTwo)->where('user_two', $userOne);
        });
    }

}
