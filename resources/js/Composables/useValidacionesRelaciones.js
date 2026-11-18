export function useValidacionesRelaciones(
    getTaxonAct,
    getTaxonRel,
    mostrarNotificacion
) {

    // Obtener los taxones actualizados
    const obtenerTaxones = () => ({
        taxonAct: getTaxonAct(),
        taxonRel: getTaxonRel()
    });

    // Función general para mostrar errores
    const mostrarError = (mensaje, titulo = 'Error') => {
        mostrarNotificacion(
            titulo,
            mensaje,
            'error',
            7000
        );
        return false;
    };

    // Obtener relaciones de un taxón
    const obtenerRelaciones = (taxon) => {
        return Object.values(taxon?.relaciones ?? {}).flat();
    };


    // ==========================================
    // VALIDACIÓN DE SINÓNIMOS
    // ==========================================
    const validacionSinonimos = async () => {

        const { taxonAct, taxonRel } = obtenerTaxones();

        const valTaxAct = obtenerRelaciones(taxonAct)
            .find(item =>
                item.Estatus === 2 &&
                (item.IdTipoRelacion === 1 || item.IdTipoRelacion === 2)
            );

        const valTaxRel = obtenerRelaciones(taxonRel)
            .find(item =>
                item.Estatus === 2 &&
                (item.IdTipoRelacion === 1 || item.IdTipoRelacion === 2)
            );

        // Validar que los taxones no tengan el mismo estatus
        if (taxonRel.completo.Estatus === taxonAct.completo.Estatus) {

            return mostrarError(
                'El taxón actual y el taxón a relacionar no pueden tener el mismo estatus.'
            );

        } else if (valTaxAct !== undefined || valTaxRel !== undefined) {

            return mostrarError(
                'El taxón sinónimo seleccionado ya cuenta con un válido relacionado.'
            );

        } else if (taxonAct.completo.Estatus === 'ND') {

            return mostrarError(
                'El taxón actual tiene estatus ND, por lo cual no puede tener relaciones de sinonimia.'
            );

        } else if (
            taxonAct.completo.categoria.IdNivel1 < 5 ||
            taxonRel.completo.categoria.IdNivel1 < 5
        ) {

            return mostrarError(
                'No se pueden tener relaciones de sinonimia en taxones de categoría superior a familia.'
            );
        }

        return true;
    };


    // ==========================================
    // VALIDACIÓN DE BASONIMIA
    // ==========================================
    const validacionBasonimos = async () => {

        const { taxonAct, taxonRel } = obtenerTaxones();

        const valTaxAct = obtenerRelaciones(taxonAct)
            .find(item => item.IdTipoRelacion === 2);

        const valTaxRel = obtenerRelaciones(taxonRel)
            .find(item => item.IdTipoRelacion === 2);

        if (valTaxAct !== undefined || valTaxRel !== undefined) {

            return mostrarError(
                'La relación de basonimia no puede ser generada, ya que alguno de los taxones ya cuenta con una relación de este tipo.'
            );

        } else if (
            taxonAct.completo.categoria.IdNivel1 < 7 ||
            taxonRel.completo.categoria.IdNivel1 < 7
        ) {

            return mostrarError(
                'El taxón actual es de categoría superior a especie, por lo cual no se puede generar la relación de basonimia.'
            );
        }

        return true;
    };


    // ==========================================
    // VALIDACIÓN DE EQUIVALENCIA
    // ==========================================
    const validacionEquivalencia = async () => {

        const { taxonAct, taxonRel } = obtenerTaxones();

        const sistemaAct = taxonAct.completo.SistClasCatDicc;
        const sistemaRel = taxonRel.completo.SistClasCatDicc;

        if (sistemaAct === sistemaRel) {

            return mostrarError(
                'No se puede generar la relación, ya que el sistema de clasificación es el mismo en ambos taxones.'
            );
        }

        if (
            ['NA', 'ND'].includes(sistemaAct) ||
            ['NA', 'ND'].includes(sistemaRel)
        ) {

            return mostrarError(
                'No se puede generar la relación porque el sistema de clasificación de uno de los taxones es NA o ND.'
            );
        }

        if (
            taxonAct.completo.categoria.NombreCategoriaTaxonomica !==
            taxonRel.completo.categoria.NombreCategoriaTaxonomica
        ) {

            return mostrarError(
                'No se puede generar la relación porque la categoría taxonómica no es la misma en ambos taxones.',
                'Alerta'
            );
        }

        if (
            !(taxonAct.completo.categoria.IdNivel1 < 7 &&
              taxonAct.completo.categoria.IdNivel3 === 0) ||

            !(taxonRel.completo.categoria.IdNivel1 < 7 &&
              taxonRel.completo.categoria.IdNivel3 === 0)
        ) {

            return mostrarError(
                'No se puede generar la relación porque la categoría debe ser género o superior.'
            );
        }

        return true;
    };


    // ==========================================
    // VALIDACIÓN DE HUÉSPED
    // ==========================================
    const validacionHuesped = async () => {

        const { taxonAct, taxonRel } = obtenerTaxones();

        const gruposPara = [
            'ARACH', 'COLEO', 'DIPTE', 'HYMEN', 'INSEC',
            'NEMAT', 'ACANT', 'ANNEL', 'CESTO', 'CRUST',
            'MONOG', 'PROT', 'MYXOZ', 'TREMA'
        ];

        const gruposVert = [
            'ANFIB', 'AVES', 'MAMIF', 'PECES', 'REPTI'
        ];

        const estatusPermitidos = ['Válido', 'Aceptado'];

        if (
            !estatusPermitidos.includes(taxonAct.estatus) ||
            !estatusPermitidos.includes(taxonRel.estatus)
        ) {

            return mostrarError(
                'No se puede generar la relación, ya que uno o ambos taxones tienen estatus diferente de válido/correcto.'
            );
        }

        const grupoAct = taxonAct.completo.scat.grupo_scat.GrupoAbreviado;
        const grupoRel = taxonRel.completo.scat.grupo_scat.GrupoAbreviado;

        const relacionValida = (
            gruposPara.includes(grupoAct) &&
            gruposVert.includes(grupoRel)
        ) || (
            gruposVert.includes(grupoAct) &&
            gruposPara.includes(grupoRel)
        );

        if (!relacionValida) {

            return mostrarError(
                'El vertebrado o parásito que seleccionó no pertenece a un grupo válido. Vertebrados válidos (ANFIB, AVES, MAMIF, PECES, REPTI). Parásitos válidos (ARACH, COLEO, DIPTE, HYMEN, INSEC, NEMAT, ACANT, ANNEL, CESTO, CRUST, MONOG, PROT, MYXOZ, TREMA).'
            );
        }

        return true;
    };


    // ==========================================
    // VALIDACIÓN DE PARENTAL
    // ==========================================
    const validacionParental = async () => {

        const { taxonAct, taxonRel } = obtenerTaxones();

        if (
            taxonAct.completo.categoria.NombreCategoriaTaxonomica !== 'híbrido'
        ) {

            return mostrarError(
                'No es posible asociar un parental a un taxón que no es un híbrido.'
            );
        }

        const categPerm = ['género', 'especie', 'híbrido'];

        if (
            !categPerm.includes(taxonAct.completo.categoria.NombreCategoriaTaxonomica) ||
            !categPerm.includes(taxonRel.completo.categoria.NombreCategoriaTaxonomica)
        ) {

            return mostrarError(
                'No es posible asociar un parental a un taxón cuya categoría taxonómica sea diferente de género o especie.'
            );
        }

        return true;
    };


    // ==========================================
    // VALIDACIÓN DE HOMÓNIMOS
    // ==========================================
    const validaHomonimos = async () => {

        const { taxonAct, taxonRel } = obtenerTaxones();

        if (taxonAct.id === taxonRel.id) {

            return mostrarError(
                'Está tratando de relacionar el nombre consigo mismo, lo cual no es posible.',
                'Alerta'
            );
        }

        if (
            taxonAct.completo.TaxonCompleto !==
            taxonRel.completo.TaxonCompleto
        ) {

            return mostrarError(
                'Está tratando de relacionar dos taxones con diferente nombre; esto no es posible.'
            );
        }

        return true;
    };


    // ==========================================
    // RETORNAR VALIDACIONES
    // ==========================================
    return {
        validacionSinonimos,
        validacionBasonimos,
        validacionEquivalencia,
        validacionHuesped,
        validacionParental,
        validaHomonimos
    };
}