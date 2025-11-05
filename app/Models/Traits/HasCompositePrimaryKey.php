<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait que agrega soporte para llaves primarias compuestas en Eloquent.
 * Laravel no las maneja nativamente, por eso este trait corrige las operaciones
 * de actualización y eliminación (update/delete).
 */
trait HasCompositePrimaryKey
{
    /**
     * Ajusta el query para guardar (update) usando todas las llaves del modelo.
     */
    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->getKeyName() as $keyField) {
            $query->where($keyField, '=', $this->getAttribute($keyField));
        }

        return $query;
    }

    /**
     * Ajusta el query para seleccionar un registro específico (find, delete, etc.).
     */
    protected function setKeysForSelectQuery($query)
    {
        foreach ($this->getKeyName() as $keyField) {
            $query->where($keyField, '=', $this->getAttribute($keyField));
        }

        return $query;
    }

    /**
     * Sobrescribe el método para devolver la lista de llaves primarias.
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Indica que el modelo no usa incrementos automáticos (autoincrement).
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Evita que Eloquent asuma que la llave primaria es un solo valor.
     */
    public function getKey()
    {
        $key = [];
        foreach ($this->getKeyName() as $keyField) {
            $key[$keyField] = $this->getAttribute($keyField);
        }
        return $key;
    }
}