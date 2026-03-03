<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $IdAutorTaxon Identificador único del autor (asignación de un número consecutivo para cada registro adicionado).
 * @property string|null $NombreAutoridad
 * @property string|null $NombreCompleto Nombre completo de la autoridad que define el taxón.
 * @property string|null $GrupoTaxonomico Grupo taxonómico que estudia la autoridad.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property int|null $IdOriginal
 * @property string|null $Catalogo
 * @property string|null $NombreAutoridadOriginal
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon autoridad($nombre)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon grupo($grupo)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon nombre($nombre)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon whereCatalogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon whereGrupoTaxonomico($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon whereIdAutorTaxon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon whereIdOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon whereNombreAutoridad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon whereNombreAutoridadOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutorTaxon whereNombreCompleto($value)
 */
	class AutorTaxon extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdBibliografia Identificador único para cada elemento de la tabla Bibliografia.
 * @property string|null $Observaciones Observaciones acerca de la publicación.
 * @property string $Autor Autor(es) de la publicación.
 * @property string $Anio Indica el(los) año(s) o la fecha en que fue publicada la publicación.
 * @property string $TituloPublicacion Título de la publicación.
 * @property string|null $TituloSubPublicacion Titulo de la subpublicación.
 * @property string|null $EditorialPaisPagina Entidad que llevó a cabo la edición de la publicación, país o lugar de edición de la publicación y/o páginas de la publicación o subpublicación.
 * @property string|null $NumeroVolumenAnio Indica el número de la publicación, el número del volumen de la publicación y/ó páginas de la publicación ó subpublicación.
 * @property string|null $EditoresCompiladores Editores, compiladores y/o coordinadores de la publicación.
 * @property string|null $ISBNISSN Número ISBN (International Standard Book Number) y/o número ISSN (International Standard Serial Number) de la publicación.
 * @property string|null $CitaCompleta Cita bibliográfica completa.
 * @property string|null $OrdenCitaCompleta Orden de los datos que forman la cita bibliográfica completa.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property int|null $IdOriginal
 * @property string|null $Catalogo
 * @property string|null $AutorOriginal
 * @property string|null $usuario
 * @property string|null $Marca
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GrupoScat> $gruposScat
 * @property-read int|null $grupos_scat_count
 * @property-read \App\Models\RelacionBibliografia|null $relbibliografia
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RelBiblioGrupoSCAT> $relbibliogruposcat
 * @property-read int|null $relbibliogruposcat_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RelNombreBiblio> $relnombrebiblio
 * @property-read int|null $relnombrebiblio_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RelNombreCatalogoBiblio> $relnombrecatbiblio
 * @property-read int|null $relnombrecatbiblio_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RelNombreCatalogoRegionBiblio> $relnombrecatregbiblio
 * @property-read int|null $relnombrecatregbiblio_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RelNombreRegionBiblio> $relnombreregbiblio
 * @property-read int|null $relnombreregbiblio_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RelNomNomComunRegionBiblio> $relnomnomcomunregbiblio
 * @property-read int|null $relnomnomcomunregbiblio_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia buscarBiblio($texto, $opciones)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereAnio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereAutor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereAutorOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereCatalogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereCitaCompleta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereEditoresCompiladores($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereEditorialPaisPagina($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereISBNISSN($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereIdBibliografia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereIdOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereMarca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereNumeroVolumenAnio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereOrdenCitaCompleta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereTituloPublicacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereTituloSubPublicacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bibliografia whereUsuario($value)
 */
	class Bibliografia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdCatNombre Identificador único para cada catálogo/elemento (asignación de un número consecutivo para cada registro adicionado).
 * @property string $Descripcion Nombre del catálogo/elemento.
 * @property int $Nivel1 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel1.
 * @property int $Nivel2 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel2.
 * @property int $Nivel3 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel3.
 * @property int $Nivel4 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel4.
 * @property int $Nivel5 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos.
 * @property int $Nivel6
 * @property int $Nivel7
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property int|null $IdOriginal
 * @property string|null $Catalogo
 * @property int|null $IdAscendente
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CatalogoNombre> $hijos
 * @property-read int|null $hijos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RelNombreCatalogo> $relNombreCat
 * @property-read int|null $rel_nombre_cat_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre actList($act)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre ascendente($desc, $niv)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre buscar($desc)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre busqueda($busca)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre contDet($asc)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre descripcion($desc)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre lista()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre listaAnt($act)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre listaDet($asc)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereCatalogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereIdAscendente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereIdCatNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereIdOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereNivel1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereNivel2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereNivel3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereNivel4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereNivel5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereNivel6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatalogoNombre whereNivel7($value)
 */
	class CatalogoNombre extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdCategoriaTaxonomica Identificador único para cada elemento de la tabla Categoría Taxonómica (asignación de un número consecutivo para cada registro adicionado).
 * @property string $NombreCategoriaTaxonomica Nombre de la categoría taxonómica.
 * @property int $IdNivel1 Identificador consecutivo de la categoría.
 * @property int $IdNivel2 Indica el reino al que pertenece la categoría (0 .- división y 1.- phyllum).
 * @property int $IdNivel3 Identificador consecutivo de la categoría,  el 0 indica que se esta en una categoría taxonómica obligatoria.
 * @property int $IdNivel4 Identificador consecutivo de la categoría.
 * @property int $IdNivel5
 * @property int $IdNivel6
 * @property int $IdNivel7
 * @property int $IdNivel8
 * @property int $IdNivel9
 * @property int $IdNivel10
 * @property int $IdNivel11
 * @property int $IdNivel12
 * @property string|null $RutaIcono Se guarda la ruta en donde se encuentra el ícono asociado a la categoría.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property int|null $IdAscendente
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Nombre> $nombre
 * @property-read int|null $nombre_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas listaCat($categoria)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdAscendente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdCategoriaTaxonomica($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel10($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel11($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel12($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel7($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel8($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereIdNivel9($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereNombreCategoriaTaxonomica($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriasTaxonomicas whereRutaIcono($value)
 */
	class CategoriasTaxonomicas extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdGrupoSCAT
 * @property string $GrupoSCAT
 * @property string $GrupoAbreviado
 * @property string $FechaCaptura
 * @property string $GrupoSNIB
 * @property string|null $FechaModificacion
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GrupoScat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GrupoScat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GrupoScat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GrupoScat whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GrupoScat whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GrupoScat whereGrupoAbreviado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GrupoScat whereGrupoSCAT($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GrupoScat whereGrupoSNIB($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GrupoScat whereIdGrupoSCAT($value)
 */
	class GrupoScat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdMime Identificador del MIME (asignación de un número consecutivo para cada registro adicionado).
 * @property string $MIME Especifica el tipo de datos (texto, imágenes o audio) que contienen los archivos. P. ej. ACCESS, EXCEL. JPG, etc.
 * @property string|null $Extension Extensión del objeto (pdf, mdb, html, rtf, etc).
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property int|null $IdOriginal
 * @property string|null $Catalogo
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mime query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mime whereCatalogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mime whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mime whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mime whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mime whereIdMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mime whereIdOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mime whereMIME($value)
 */
	class Mime extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNomComun Identificador único del nombre común (asignación de un número consecutivo para cada registro adicionado).
 * @property string $NomComun Nombre común que recibe el taxón.
 * @property string|null $Observaciones Observaciones referentes al nombre común.
 * @property string $Lengua Indica la lengua o idioma del nombre común.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property int|null $IdOriginal
 * @property string|null $Catalogo
 * @property string|null $Marca
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun lengua($lengua)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun nomComun($nombre)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun observacion($observacion)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun whereCatalogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun whereIdNomComun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun whereIdOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun whereLengua($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun whereMarca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun whereNomComun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NomComun whereObservaciones($value)
 */
	class NomComun extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNombre Identificador único del taxón  (asignación de un número consecutivo para cada registro adicionado).
 * @property int $IdCategoriaTaxonomica Identificador de la categoria taxónomica que le corresponde al taxón.
 * @property int $IdNombreAscendente Clave del nombre  asignado al taxón que corresponde con una categoría taxónomica superior (inmediata). Puede o no coincidir con el 'ascedente obligatorio' (por ejemplo, el nombre del taxón ascedente para una especie  puede ser un género o un subgénero).
 * @property int $IdAscendObligatorio Clave del nombre  de la categoría superior, considerado obligatorio (es decir, de las categorías: reino, phylum o división, clase, orden, familia, género o especie).
 * @property string $Nombre Nombre del taxón.
 * @property int $Estatus Indica si el  taxón es aceptado/valido ó si es un sinonimo, 1.- Sinonimo, 2.-aceptado/valido, -9.- No Aplica, 6.- No Disponible.
 * @property string $Fuente Clave de proyecto apoyado por la CONABIO o nombre del proytecto.
 * @property string $NombreAutoridad Nombre del (los) autor(es) que define al taxón. Incluye el año de creación del mismo.
 * @property string|null $OtrasObservaciones Número asignado por el autor de la clasificación para establecer relaciones de parentesco entre taxones.
 * @property string|null $CitaNomenclatural Cita nomenclatural.
 * @property string $SistClasCatDicc Sistema de clasificación (cronquist 1981, brummit, etc) en el que se considera y define al taxón; ó catálogo ó diccionario en el que se considera al taxón.
 * @property string|null $Anotacion Es una observación al taxón en latín y abreviada, por ejemplo : sin. tax., sin. nom., nom. cons., nom. dub., etc.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property int|null $IdOriginal
 * @property string|null $Catalogo
 * @property string|null $IdCAT_GS003
 * @property string|null $Ascendentes
 * @property string|null $NombreCompleto
 * @property string|null $AscendentesObligatorios
 * @property string|null $SistClasCatDiccOriginal
 * @property string|null $ModificadoPor
 * @property string|null $NombreAutoridadOriginal
 * @property int $EstadoRegistro
 * @property string|null $FuenteOriginal
 * @property string|null $TaxonCompleto
 * @property string|null $Marca
 * @property-read Nombre $ascendOblig
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Nombre> $ascendObligHijos
 * @property-read int|null $ascend_oblig_hijos_count
 * @property-read \App\Models\CategoriasTaxonomicas $categoria
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Nombre> $hijos
 * @property-read int|null $hijos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Nombre_Relacion> $nombreRel
 * @property-read int|null $nombre_rel_count
 * @property-read \App\Models\Nombre_Relacion|null $nombreRelVal
 * @property-read Nombre $padre
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RelNombreAutor> $relNombreAutor
 * @property-read int|null $rel_nombre_autor_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RelNombreCatalogo> $relNombreCat
 * @property-read int|null $rel_nombre_cat_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RelNombreRegion> $relNombreRegion
 * @property-read int|null $rel_nombre_region_count
 * @property-read \App\Models\Scat|null $scat
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre cargaHijos($id)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre cargaReferencias($id)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre cargaRelaciones($id)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre filtraArbol($categ, $catalog)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre filtraArbolTaxCat($categ, $catalog, $taxon)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre nombres($nombres)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereAnotacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereAscendentes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereAscendentesObligatorios($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereCatalogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereCitaNomenclatural($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereEstadoRegistro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereEstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereFuente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereFuenteOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereIdAscendObligatorio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereIdCATGS003($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereIdCategoriaTaxonomica($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereIdNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereIdNombreAscendente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereIdOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereMarca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereModificadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereNombreAutoridad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereNombreAutoridadOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereNombreCompleto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereOtrasObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereSistClasCatDicc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereSistClasCatDiccOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre whereTaxonCompleto($value)
 */
	class Nombre extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNombre Identificador del nombre del taxón al que se relaciona IdNombreRel.
 * @property int $IdNombreRel Identificador del nombre del taxón que esta relacionado con IdNombre.
 * @property int $IdTipoRelacion Identificador del tipo de relación que hay entre taxones (sinónimo, basónimo, híbrido, etc).
 * @property string|null $Observaciones Observaciones referentes a la relación.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property string|null $Marca
 * @property string|null $Marca2
 * @property-read \App\Models\Nombre $nombre
 * @property-read \App\Models\Tipo_Relacion $tipoRelacion
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion tipoRelacion($idNom)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion whereIdNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion whereIdNombreRel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion whereIdTipoRelacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion whereMarca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion whereMarca2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Nombre_Relacion whereObservaciones($value)
 */
	class Nombre_Relacion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdObjetoExterno Identificador del objeto externo (asignación de un número consecutivo para cada registro adicionado).
 * @property int $IdMime Identificador del MIME.
 * @property string|null $NombreObjeto Nombre del archivo con extensión.
 * @property string|null $NombreSitio Nombre del sitio de la WWW o página web.
 * @property string|null $Ruta Ruta del objeto externo sin unidad, nombre ni extensión.
 * @property string $Protocolo Protocolos: http, https, NA, ND.
 * @property string|null $Usuario Clave del usuario. Cuando ésta sea necesaria para acceder a un sitio WWW.
 * @property string|null $Password Contraseña, cuando ésta sea necesaria.
 * @property string|null $UnidadLogica Unidad lógica: c, j, etc.
 * @property string|null $Titulo Titulo del objeto externo.
 * @property string|null $Autor Autor del objeto externo.
 * @property string|null $Institucion Institución propietaria del objeto externo.
 * @property \Illuminate\Support\Carbon|null $Fecha Fecha de creación del objeto externo.
 * @property string|null $Observaciones Observaciones acerca del objeto externo.
 * @property \Illuminate\Support\Carbon $FechaCaptura Fecha de captura del registro.
 * @property \Illuminate\Support\Carbon|null $FechaModificacion Fecha de modificación del registro.
 * @property int|null $IdOriginal
 * @property string|null $Catalogo
 * @property-read \App\Models\Mime $mime
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereAutor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereCatalogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereIdMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereIdObjetoExterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereIdOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereInstitucion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereNombreObjeto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereNombreSitio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereProtocolo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereRuta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereUnidadLogica($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjetoExterno whereUsuario($value)
 */
	class ObjetoExterno extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdRegion Identificador único de la tabla región  (asignación de un número consecutivo para cada registro adicionado).
 * @property string $NombreRegion Nombre de la Region.
 * @property int $IdTipoRegion Identificador que indica el tipo de region.
 * @property string|null $ClaveRegion Clave de la Region (Por ejemplo: Clave del Estado).
 * @property int $IdRegionAsc Identificador de la región donde se localiza NombreRegion.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property int|null $IdOriginal
 * @property string|null $Catalogo
 * @property int $DatoActivo
 * @property string|null $Abreviado Dato abreviado de la región
 * @property string|null $Marca
 * @property int|null $IdRegNvo
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereAbreviado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereCatalogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereClaveRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereDatoActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereIdOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereIdRegNvo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereIdRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereIdRegionAsc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereIdTipoRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereMarca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereNombreRegion($value)
 */
	class Region extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdBibliografia
 * @property int $IdGrupoSCAT
 * @property string|null $Observaciones
 * @property string $FechaCaptura
 * @property string|null $FechaModificacion
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelBiblioGrupoSCAT grupoBiblio($IdBiblio)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelBiblioGrupoSCAT newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelBiblioGrupoSCAT newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelBiblioGrupoSCAT query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelBiblioGrupoSCAT whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelBiblioGrupoSCAT whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelBiblioGrupoSCAT whereIdBibliografia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelBiblioGrupoSCAT whereIdGrupoSCAT($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelBiblioGrupoSCAT whereObservaciones($value)
 */
	class RelBiblioGrupoSCAT extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNombre Identificador del taxón.
 * @property int $IdAutorTaxon Identificador del autor de los taxones.
 * @property string|null $CadInicio Observaciones acerca de la asociación.
 * @property string|null $CadFinal
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreAutor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreAutor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreAutor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreAutor whereCadFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreAutor whereCadInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreAutor whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreAutor whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreAutor whereIdAutorTaxon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreAutor whereIdNombre($value)
 */
	class RelNombreAutor extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNombre Identificador del taxón.
 * @property int $IdBibliografia Identificador de la bibliografia.
 * @property string|null $Observaciones Observaciones acerca de la asociación.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property string|null $Marca
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreBiblio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreBiblio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreBiblio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreBiblio whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreBiblio whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreBiblio whereIdBibliografia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreBiblio whereIdNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreBiblio whereMarca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreBiblio whereObservaciones($value)
 */
	class RelNombreBiblio extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNombre Identificador del taxon referenciado.
 * @property int $IdCatNombre Identificador del elemento del catálogo asociado al taxón.
 * @property string|null $Observaciones Comentarios acerca de la asociación.
 * @property string $usuario
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property string|null $Marca
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogo whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogo whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogo whereIdCatNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogo whereIdNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogo whereMarca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogo whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogo whereUsuario($value)
 */
	class RelNombreCatalogo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdBibliografia Identificador de la bibliografía.
 * @property int $IdNombre Identificador del taxón.
 * @property int $IdCatNombre Identificador de la característica.
 * @property string|null $Observaciones Observaciones referentes a la asociación.
 * @property string $usuario
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property string|null $Marca
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio whereIdBibliografia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio whereIdCatNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio whereIdNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio whereMarca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoBiblio whereUsuario($value)
 */
	class RelNombreCatalogoBiblio extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNombre Identificador del taxón.
 * @property int $IdCatNombre Identificador de la característica.
 * @property int $IdRegion Identificador de la región.
 * @property int $IdTipoDistribucion Identificador del tipo de distribución (Original, Actual, etc).
 * @property int $IdBibliografia Identificador de la bibliografia.
 * @property string|null $Observaciones Observaciones referentes a la asociación.
 * @property string $usuario
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio whereIdBibliografia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio whereIdCatNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio whereIdNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio whereIdRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio whereIdTipoDistribucion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreCatalogoRegionBiblio whereUsuario($value)
 */
	class RelNombreCatalogoRegionBiblio extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNombre Identificador del taxón.
 * @property int $IdRegion Identificador de la región.
 * @property int $IdTipoDistribucion Identificador del tipo de distribución (Original, Actual, etc).
 * @property string|null $Observaciones Observaciones acerca de la relación.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property string|null $Marca
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegion whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegion whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegion whereIdNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegion whereIdRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegion whereIdTipoDistribucion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegion whereMarca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegion whereObservaciones($value)
 */
	class RelNombreRegion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNombre Identificador del taxón.
 * @property int $IdRegion Identificador de la región.
 * @property int $IdTipoDistribucion Identificador del tipo de distribución (Original, Actual, etc).
 * @property int $IdBibliografia Identificador de la bibliografia.
 * @property string|null $Observaciones Observaciones acerca de la relación.
 * @property int|null $NivConfEnd 0 = vacío, 1=Alto, 2=Medio, 3=Bajo
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio whereIdBibliografia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio whereIdNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio whereIdRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio whereIdTipoDistribucion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio whereNivConfEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelNombreRegionBiblio whereObservaciones($value)
 */
	class RelNombreRegionBiblio extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNombre Identificador del nombre del taxón al que se relaciona IdNombreRel.
 * @property int $IdNombreRel Identificador del nombre del taxón que esta relacionado con IdNombre.
 * @property int $IdTipoRelacion Identificador del tipo de relación que hay entre taxones (sinónimo, basónimo, híbrido, etc).
 * @property int $IdBibliografia Identificador de la publicación que complementa la información acerca de la relación entre los taxones.
 * @property string|null $Observaciones Observaciones referentes a la relación.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property string|null $Marca
 * @property-read \App\Models\Bibliografia $bibliografia
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia bibliografiaRelacion($idNombre, $idNombreRel, $idTipoRel)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia whereIdBibliografia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia whereIdNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia whereIdNombreRel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia whereIdTipoRelacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia whereMarca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelacionBibliografia whereObservaciones($value)
 */
	class RelacionBibliografia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdNombre
 * @property string $IDCAT Identificador único del taxón (IdNombre+Fuente (sin fecha). Ej. 595ANFIB
 * @property string|null $Nivel_de_revision Nivel de confiabilidad del registro
 * @property string|null $IdCITES Identificador único del taxón en ITIS (Taxonomic Serial Number)
 * @property string|null $HomonimiaSNIB Campo para indicar si se trata de un nombre homónimo. Se incluye IdCAT del nombre homónimo
 * @property int|null $IdIUCN Identificador único del taxón en WORMS
 * @property int|null $ListaInvasoras Dato del campo IDEspecie del taxón referido en la Lista de especies invasoras CONABIO
 * @property string|null $ValidacionSNIB
 * @property string|null $ObservacionesNR
 * @property string|null $IdCOL
 * @property int $Publico
 * @property string|null $IdCatAnterior
 * @property int|null $IdGrupoSCAT
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property string|null $ModificadoPor
 * @property-read \App\Models\GrupoScat|null $grupoScat
 * @property-read \App\Models\Nombre $nombre
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereHomonimiaSNIB($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereIDCAT($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereIdCITES($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereIdCOL($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereIdCatAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereIdGrupoSCAT($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereIdIUCN($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereIdNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereListaInvasoras($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereModificadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereNivelDeRevision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereObservacionesNR($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat wherePublico($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scat whereValidacionSNIB($value)
 */
	class Scat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdTipoDistribucion Clave única de la tabla TipoDistribucion (asignación de un número consecutivo para cada registro adicionado).
 * @property string $Descripcion Descripción del tipo de distribución (original, actual, histórico).
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoDistribucion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoDistribucion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoDistribucion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoDistribucion whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoDistribucion whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoDistribucion whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoDistribucion whereIdTipoDistribucion($value)
 */
	class TipoDistribucion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdTipoRegion Identificador único de la tabla TipoRegion (asignación de un número consecutivo por cada registro adicionado).
 * @property string $Descripcion Tipo de Región (Política o Geográfica, etc).
 * @property int $Nivel1 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel1.
 * @property int $Nivel2 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel2.
 * @property int $Nivel3 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel3.
 * @property int $Nivel4 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel4.
 * @property int $Nivel5 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property int|null $IdOriginal
 * @property string|null $Catalogo
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereCatalogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereIdOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereIdTipoRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereNivel1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereNivel2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereNivel3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereNivel4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoRegion whereNivel5($value)
 */
	class TipoRegion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $IdTipoRelacion Clave única de la tabla Tipo_Relacion (asignación de un número consecutivo para cada registro adicionado).
 * @property string $Descripcion Nombre del tipo de relación dada entre taxones.
 * @property int $Nivel1 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel1.
 * @property int $Nivel2 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel2.
 * @property int $Nivel3 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel3.
 * @property int $Nivel4 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos. Agrupa los elementos del Nivel4.
 * @property int $Nivel5 Identificador consecutivo del catálogo. Indica niveles jerárquicos entre los elementos.
 * @property int $Direccionalidad 1 = Unidireccional, 2= Reciproca, 3 = No aplica
 * @property string|null $RutaIcono Ruta física donde se guarda el ícono que representa la relación entre taxones.
 * @property string $FechaCaptura Fecha de captura del registro.
 * @property string|null $FechaModificacion Fecha de modificación del registro.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Nombre_Relacion> $nombreRel
 * @property-read int|null $nombre_rel_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion buscar($desc)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion nombresRel($ids)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereDireccionalidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereFechaCaptura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereFechaModificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereIdTipoRelacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereNivel1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereNivel2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereNivel3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereNivel4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereNivel5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tipo_Relacion whereRutaIcono($value)
 */
	class Tipo_Relacion extends \Eloquent {}
}

