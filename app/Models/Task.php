<?php

namespace App\Models;

use App\Traits\HasAuthor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    use HasAuthor;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['title', 'description'];
}
