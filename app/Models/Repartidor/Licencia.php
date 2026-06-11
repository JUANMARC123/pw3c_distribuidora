<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;

class Licencia extends Model
{
    protected $table = 'licencias';
    protected $primaryKey = 'id_licencia';
    public $timestamps = false;

    protected $fillable = ['categoria'];

    public function repartidores()
    {
        return $this->hasMany(Repartidor::class, 'id_licencia');
    }
}
