<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'synopsis', 'year'];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function save(array $options = [])
    {
        if (!$this->exists) {
            #when insert new movie, we add the creator
            $this->attributes['created_by'] = auth()->user()->id;
        }

        return parent::save($options);
    }
}
