<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;

class ExtensionCI extends Model
{
    protected $table = 'extensiones_ci';
    protected $primaryKey = 'id_extension_ci';
    public $timestamps = false;

    protected $fillable = ['nombre_extension'];

    public function repartidores()
    {
        return $this->hasMany(Repartidor::class, 'id_extension_ci');
    }
}
