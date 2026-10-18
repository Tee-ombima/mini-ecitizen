<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['department_id', 'name', 'category', 'description', 'fee', 'sla_days', 'fields', 'required_documents', 'is_active'];

    protected function casts(): array
    {
        return ['fields' => 'array', 'required_documents' => 'array', 'is_active' => 'boolean', 'fee' => 'decimal:2'];
    }

    public function department() { return $this->belongsTo(Department::class); }
    public function applications() { return $this->hasMany(Application::class); }
}
