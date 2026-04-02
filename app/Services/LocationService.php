<?php

namespace App\Services;

use App\Models\Ccaa;
use App\Models\Municipio;
use App\Models\Provincia;
use Illuminate\Support\Collection;

class LocationService
{
    /**
     * Obtiene todas las provincias
     */
    public function getProvincias(): Collection
    {
        return Provincia::orderBy('nombre_provincia')->get();
    }

    /**
     * Obtiene una provincia por ID
     */
    public function getProvinciaById(int $id): ?Provincia
    {
        return Provincia::find($id);
    }

    /**
     * Obtiene una provincia por nombre
     */
    public function getProvinciaByName(string $nombre): ?Provincia
    {
        return Provincia::where('nombre_provincia', $nombre)->first();
    }

    /**
     * Obtiene municipios de una provincia
     */
    public function getMunicipiosByProvincia(int $provinciaId): Collection
    {
        return Municipio::where('provincia_id', $provinciaId)
            ->orderBy('nombre_municipio')
            ->get();
    }

    /**
     * Obtiene un municipio por ID
     */
    public function getMunicipioById(int $id): ?Municipio
    {
        return Municipio::find($id);
    }

    /**
     * Obtiene un municipio por nombre
     */
    public function getMunicipioByName(string $nombre, ?int $provinciaId = null): ?Municipio
    {
        $query = Municipio::where('nombre_municipio', $nombre);

        if ($provinciaId) {
            $query->where('provincia_id', $provinciaId);
        }

        return $query->first();
    }

    /**
     * Obtiene el ID de CCAA de una provincia
     */
    public function getCcaaIdByProvincia(int $provinciaId): ?int
    {
        $provincia = $this->getProvinciaById($provinciaId);

        return $provincia ? $provincia->id_ccaa : null;
    }

    /**
     * Obtiene todas las CCAA
     */
    public function getCcaas(): Collection
    {
        return Ccaa::orderBy('nombre_ccaa')->get();
    }

    /**
     * Obtiene una CCAA por ID
     */
    public function getCcaaById(int $id): ?Ccaa
    {
        return Ccaa::find($id);
    }

    /**
     * Obtiene el nombre de municipio por ID
     */
    public function getMunicipioNombreById(int $id): ?string
    {
        $municipio = $this->getMunicipioById($id);

        return $municipio ? $municipio->nombre_municipio : null;
    }
}
