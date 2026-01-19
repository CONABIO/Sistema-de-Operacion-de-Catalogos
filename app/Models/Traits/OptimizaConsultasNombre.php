<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;

trait OptimizaConsultasNombre
{
    /**
     * Determina el estatus optimizado
     */
    private function determinarEstatusOptimizado($nombre)
    {
        static $estatusMap = [
            1 => "Sinonimo",
            6 => "NA", 
            -9 => "ND"
        ];
        
        if (isset($estatusMap[$nombre->Estatus])) {
            return $estatusMap[$nombre->Estatus];
        }

        static $nombresEspeciales = [
            'Animalia' => 'Válido',
            'Plantae' => 'Correcto',
            'Fungi' => 'Correcto',
            'Protozoa' => 'Correcto',
            'Archaea' => 'Correcto',
            'Bacteria' => 'Correcto',
            'Chromista' => 'Correcto'
        ];
        
        if (isset($nombresEspeciales[$nombre->Nombre])) {
            return $nombresEspeciales[$nombre->Nombre];
        }
        
        return $nombre->categoria->IdNivel2 === 0 ? "Correcto" : "Válido";
    }

    /**
     * Obtiene conteos de ejemplares en batch
     */
    private function obtenerConteosEjemplaresBatch(array $idsNombres)
    {
        if (empty($idsNombres)) {
            return collect();
        }

        // Query optimizada con UNION ALL
        $placeholders = implode(',', array_fill(0, count($idsNombres), '?'));
        
        $resultados = DB::connection('catcentral')
            ->select("
                SELECT IdNombre, COUNT(*) as conteo
                FROM (
                    SELECT t.IdNombreRel as IdNombre
                    FROM snib.nombre_taxonomia nt
                    INNER JOIN catalogocentralizado._TransformaTablaNombre_snib t 
                        ON nt.idnombre = t.IdNombre
                    WHERE t.IdNombreRel IN ($placeholders)
                        AND nt.estadoregistro NOT LIKE '%En proceso de integraci%'
                    
                    UNION ALL
                    
                    SELECT nt.IdNombre
                    FROM snib.nombre_taxonomia nt
                    WHERE nt.IdNombre IN ($placeholders)
                        AND nt.estadoregistro NOT LIKE '%En proceso de integraci%'
                ) AS subquery
                GROUP BY IdNombre
            ", array_merge($idsNombres, $idsNombres));

        return collect($resultados)->keyBy('IdNombre');
    }

    /**
     * Obtiene referencias en batch
     */
    private function obtenerReferenciasBatch(array $idsNombres)
    {
        if (empty($idsNombres)) {
            return collect();
        }

        $referencias = DB::connection('catcentral')
            ->table('Nombre')
            ->selectRaw('
                Nombre.IdNombre,
                Bibliografia.IdBibliografia, 
                Bibliografia.Autor, 
                Bibliografia.Anio,
                Bibliografia.TituloPublicacion AS Titulo, 
                Bibliografia.CitaCompleta AS Cita,
                RelNombreBiblio.Observaciones AS ObsRelNom
            ')
            ->join('RelNombreBiblio', 'RelNombreBiblio.IdNombre', '=', 'Nombre.IdNombre')
            ->join('Bibliografia', 'Bibliografia.IdBibliografia', '=', 'RelNombreBiblio.IdBibliografia')
            ->whereIn('Nombre.IdNombre', $idsNombres)
            ->orderBy('Bibliografia.IdBibliografia')
            ->orderBy('Bibliografia.Autor')
            ->orderBy('Bibliografia.Anio')
            ->get()
            ->groupBy('IdNombre');

        return $referencias;
    }

    /**
     * Obtiene relaciones en batch
     */
    private function obtenerRelacionesBatch(array $idsNombres)
    {
        if (empty($idsNombres)) {
            return collect();
        }

        $placeholders = implode(',', array_fill(0, count($idsNombres), '?'));
        $params = array_merge($idsNombres, $idsNombres, $idsNombres);

        $relaciones = DB::connection('catcentral')
            ->select("
                SELECT 
                    Tipo_Relacion.IdTipoRelacion,
                    Tipo_Relacion.RutaIcono AS TipoRelIcono,
                    Tipo_Relacion.Descripcion,
                    Nombre.IdNombre,
                    Nombre.NombreCompleto,
                    Nombre.Estatus,
                    Nombre.SistClasCatDicc,
                    Nombre.NombreAutoridad,
                    COUNT(DISTINCT RelacionBibliografia.IdBibliografia) AS Biblio,
                    CategoriaTaxonomica.IdNivel2,
                    CategoriaTaxonomica.RutaIcono AS CategIcono,
                    Nombre_Relacion.FechaCaptura,
                    Nombre_Relacion.FechaModificacion,
                    Nombre_Relacion.Observaciones,
                    Nombre_Relacion.IdNombre AS RelIdNom,
                    Nombre_Relacion.IdNombreRel AS RelIdNomRel
                FROM Nombre
                INNER JOIN CategoriaTaxonomica 
                    ON Nombre.IdCategoriaTaxonomica = CategoriaTaxonomica.IdCategoriaTaxonomica
                INNER JOIN Nombre_Relacion 
                    ON (Nombre.IdNombre = Nombre_Relacion.IdNombre 
                        OR Nombre.IdNombre = Nombre_Relacion.IdNombreRel)
                INNER JOIN Tipo_Relacion 
                    ON Tipo_Relacion.IdTipoRelacion = Nombre_Relacion.IdTipoRelacion
                LEFT JOIN RelacionBibliografia 
                    ON RelacionBibliografia.IdNombre = Nombre_Relacion.IdNombre 
                    AND RelacionBibliografia.IdNombreRel = Nombre_Relacion.IdNombreRel
                    AND RelacionBibliografia.IdTipoRelacion = Nombre_Relacion.IdTipoRelacion
                WHERE (
                    Nombre_Relacion.IdNombre IN ($placeholders) 
                    OR Nombre_Relacion.IdNombreRel IN ($placeholders)
                )
                    AND Nombre.EstadoRegistro = 1
                    AND Nombre.IdNombre NOT IN ($placeholders)
                GROUP BY 
                    Tipo_Relacion.IdTipoRelacion, 
                    Tipo_Relacion.RutaIcono, 
                    Tipo_Relacion.Descripcion,
                    Nombre.IdNombre, 
                    Nombre.NombreCompleto, 
                    Nombre.Estatus, 
                    Nombre.SistClasCatDicc,
                    Nombre.NombreAutoridad, 
                    CategoriaTaxonomica.IdNivel2, 
                    CategoriaTaxonomica.RutaIcono,
                    Nombre_Relacion.FechaCaptura, 
                    Nombre_Relacion.FechaModificacion, 
                    Nombre_Relacion.Observaciones, 
                    Nombre_Relacion.IdNombre, 
                    Nombre_Relacion.IdNombreRel
                ORDER BY 
                    Tipo_Relacion.IdTipoRelacion ASC, 
                    Nombre.NombreCompleto ASC
            ", $params);

        return collect($relaciones)->groupBy('IdNombre');
    }

    /**
     * Procesa relaciones en batch
     */
    private function relacionNombreBatch($relaciones)
    {
        if ($relaciones->isEmpty()) {
            return [];
        }

        $resultado = [];
        foreach ($relaciones as $relacion) {
            $resultado[] = [
                'IdTipoRelacion' => $relacion->IdTipoRelacion,
                'TipoRelIcono' => $relacion->TipoRelIcono,
                'Descripcion' => $relacion->Descripcion,
                'IdNombre' => $relacion->IdNombre,
                'NombreCompleto' => $relacion->NombreCompleto,
                'Estatus' => $relacion->Estatus,
                'SistClasCatDicc' => $relacion->SistClasCatDicc,
                'NombreAutoridad' => $relacion->NombreAutoridad,
                'Biblio' => $relacion->Biblio,
                'IdNivel2' => $relacion->IdNivel2,
                'CategIcono' => $relacion->CategIcono,
                'FechaCaptura' => $relacion->FechaCaptura,
                'FechaModificacion' => $relacion->FechaModificacion,
                'Observaciones' => $relacion->Observaciones,
                'RelIdNom' => $relacion->RelIdNom,
                'RelIdNomRel' => $relacion->RelIdNomRel
            ];
        }
        
        return $resultado;
    }

    /**
     * Procesa datos batch para respuesta
     */
    public function procesarNombresBatch($nombres)
    {
        if ($nombres->isEmpty()) {
            return [];
        }

        // Filtrar solo los activos
        $nombresActivos = $nombres->where('EstadoRegistro', 1);
        
        if ($nombresActivos->isEmpty()) {
            return [];
        }

        $idsNombres = $nombresActivos->pluck('IdNombre')->toArray();
        
        // Obtener referencias en batch
        $referenciasBatch = $this->obtenerReferenciasBatch($idsNombres);
        
        // Obtener relaciones en batch  
        //$relacionesBatch = $this->obtenerRelacionesBatch($idsNombres);
        
        // Obtener conteos en batch
        //$conteosBatch = $this->obtenerConteosEjemplaresBatch($idsNombres);

        $data = [];
        foreach ($nombresActivos as $nombre) {
            $status = $this->determinarEstatusOptimizado($nombre);
            
            $nomCat = $nombre->categoria->NombreCategoriaTaxonomica .
                    " - Autor taxón Estatus Sist. Clas./Catálogo de autoridad/Diccionario ";
            
            $etiqueta = $nombre->NombreCompleto . " " . $nombre->NombreAutoridad . 
                       " - " . $status . " - " . $nombre->SistClasCatDicc;

            // Datos batch
            $referencias = $referenciasBatch->get($nombre->IdNombre, collect());
            //$relaciones = $relacionesBatch->get($nombre->IdNombre, collect());
            //$conteo = $conteosBatch->get($nombre->IdNombre, 0);

            $data[] = [
                'id' => $nombre->IdNombre,
                'label' => $etiqueta,
                'children' => [],
                'texto' => $nomCat,
                'estatus' => $status,
                //'numEjemp' => $conteo,
                'referencias' => $referencias,
                //'relaciones' => $this->relacionNombreBatch($relaciones),
                'completo' => $nombre
            ];
        }

        return $data;
    }
}