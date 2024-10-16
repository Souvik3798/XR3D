<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelFormats extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'format',
        'model_file',
        'model3d_id',
    ];

    /**
     * Get the Model3D that owns the ModelFormat.
     */
    public function model3d(): BelongsTo
    {
        return $this->belongsTo(Model3D::class);
    }
}
