<?php

namespace App\Logics\Models;

class Comment extends Base
{
    protected $table = 'comments';

    protected $fillable = ['novel_id', 'user_id', 'username', 'user_img', 'content', 'status'];

    public $fields = [
        'id',
        'novel_id', 'user_id', 'username', 'user_img', 'content', 'created_at',
    ];

    public function novel() {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}