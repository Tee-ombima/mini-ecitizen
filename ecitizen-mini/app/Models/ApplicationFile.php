<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationFile extends Model
{
    protected $fillable = ['application_id', 'label', 'path', 'original_name', 'mime', 'size'];
    public function application() { return $this->belongsTo(Application::class); }
}
