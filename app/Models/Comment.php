<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Post;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Post
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "comments";
    public $timestamps = false;

    public function replies_to(): BelongsTo {
        return $this -> belongsTo(Post::class, 'replies_to');
    }
}
