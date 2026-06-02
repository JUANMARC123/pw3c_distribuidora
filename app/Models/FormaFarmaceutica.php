<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaFarmaceutica extends Model
{
    use HasFactory;

    protected $table = 'formas_farmaceuticas';

    protected $fillable = [
        'nombre',
    ];

    public function presentaciones()
    {
        return $this->hasMany(Presentacion::class);
    }
}
