<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "posts";
    public function author(): BelongsTo
    {
        return $this -> belongsTo(User::class);
    }
    public function attachments(): HasMany
    {
        return $this -> hasMany(Attachment::class);
    }
    public function comments(): HasMany
    {
        return $this -> hasMany(Comment::class);
    }
    public function likes(): HasMany
    {
        return $this -> hasMany(Like::class);
    }
    public function published_in_group(): BelongsTo
    {
        return $this -> belongsTo(Group::class);
    }
    public function is_event(): HasOne
    {
        return $this -> hasOne(Event::class);
    }
    public function is_comment(): HasOne
    {
        return $this -> hasOne(Comment::class);
    }
}
