<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class GraficasController extends Controller
{
    public function getData()
    {
        // Datos para el Sunburst Chart (D3.js)
        $sunburstData = [
            'name' => 'Categorias',  // Raíz del Sunburst
            'children' => $this->buildHierarchy()
        ];

        // Datos para el TreeMap de Google Charts
        $treeMapData = $this->getTreeMapDataFromDB();

        return Inertia::render('Socat/Categorias/CategoriaTaxonomica', [
            'data' => $sunburstData,
            'treeMapData' => $treeMapData,
        ]);
    }

    private function buildHierarchy(): array
    {
        $hierarchy = [];

        $padres = Nombre::select('CategoriaTaxonomica.NombreCategoriaTaxonomica as categoria_nombre', 'CategoriaTaxonomica.IdCategoriaTaxonomica', DB::raw('count(*) as count')) // Agregamos el conteo aquí
            ->join('CategoriaTaxonomica', 'Nombre.IdCategoriaTaxonomica', '=', 'CategoriaTaxonomica.IdCategoriaTaxonomica')
            ->where('Nombre.EstadoRegistro', 1)
            ->where(function ($query) {
                $query->whereBetween('CategoriaTaxonomica.IdCategoriaTaxonomica', [2, 25])
                    ->orWhere('CategoriaTaxonomica.IdCategoriaTaxonomica', '>=', 27);
            })
            ->groupBy('CategoriaTaxonomica.NombreCategoriaTaxonomica', 'CategoriaTaxonomica.IdCategoriaTaxonomica')
            ->orderBy('CategoriaTaxonomica.NombreCategoriaTaxonomica', 'desc')
            ->get()->toArray();

        foreach ($padres as $padre) {
            $categoriaPadre = [
                'name' => isset($padre['categoria_nombre']) ? $padre['categoria_nombre'] : (isset($padre->categoria_nombre) ? $padre->categoria_nombre : 'Nombre Desconocido'),
                'value' => isset($padre['count']) ? $padre['count'] : (isset($padre->count) ? $padre->count : 0), // Pasamos el conteo al Sunburst
                'children' => $this->buildChildren(isset($padre['IdCategoriaTaxonomica']) ? $padre['IdCategoriaTaxonomica'] : (isset($padre->IdCategoriaTaxonomica) ? $padre->IdCategoriaTaxonomica : 0))
            ];
            $hierarchy[] = $categoriaPadre;
        }

        return $hierarchy;
    }

    private function buildChildren(int $idCategoriaPadre, int $page = 1, int $perPage = 50): array
    {
        $children = [];

        $nombres = Nombre::where('Nombre.IdCategoriaTaxonomica', $idCategoriaPadre)
            ->whereNotNull('Nombre.IdNombreAscendente')
            ->forPage($page, $perPage)
            ->get();

        foreach ($nombres as $nombre) {
            $children[] = [
                'name' => $nombre->NombreCompleto,
                'value' => 1  // Este valor podría ser modificado si quieres representar algo más en el Sunburst
            ];
        }

        return $children;
    }

        private function getTreeMapDataFromDB(): array
    {
        $dataFromDB = Nombre::join('CategoriaTaxonomica', 'Nombre.IdCategoriaTaxonomica', '=', 'CategoriaTaxonomica.IdCategoriaTaxonomica')
            ->select(
                'CategoriaTaxonomica.NombreCategoriaTaxonomica as categoria_nombre',
                DB::raw('count(*) as count') // Contar las ocurrencias
            )
            ->where('Nombre.EstadoRegistro', 1)
            ->groupBy('CategoriaTaxonomica.NombreCategoriaTaxonomica')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();

        // Formatear los datos para el TreeMap de Google Charts
        $treeMapData = [
            ['Location', 'Parent', 'Market trade volume (size)', 'Market increase/decrease (color)']
        ];

        // Agregar una raíz
        $treeMapData[] = ['Global', null, 0, 0];

       

        return $treeMapData;
    }      
}