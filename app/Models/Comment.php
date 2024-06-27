<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "comments";
    public $timestamps = false;

    public function replies_to(): BelongsTo {
        return $this -> belongsTo(Post::class, "replies_to");
    }

    public function post(): BelongsTo {
        return $this -> belongsTo(Post::class, "post");
    }
}
