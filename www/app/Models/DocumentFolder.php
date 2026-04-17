<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentFolder extends Model
{
    protected $fillable = ['user_id', 'name', 'color', 'parent_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(DocumentFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DocumentFolder::class, 'parent_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'folder_id');
    }
}
