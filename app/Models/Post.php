<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "posts";

    protected $casts = [
        "attachments" => "json",
        "comments" => "json",
        "likes" => "json",
        "reports" => "json",
    ];

    public function author(): BelongsTo {
        return $this -> belongsTo(User::class, "author");
    }

    public function attachments(): HasMany {
        return $this -> hasMany(Attachment::class, "attachments");
    }

    public function comments(): HasMany {
        return $this -> hasMany(Comment::class, "comments");
    }

    public function likes(): HasMany {
        return $this -> hasMany(Like::class, "likes");
    }

    public function published_in_group(): BelongsTo {
        return $this -> belongsTo(Group::class, "group_id");
    }

    public function is_event(): HasOne {
        return $this -> hasOne(Event::class, "event_id");
    }

    public function is_comment(): HasOne {
        return $this -> hasOne(Comment::class, "comment_id");
    }

    public function reports(): HasMany {
        return $this -> hasMany(Report::class, "reports");
    }
}
