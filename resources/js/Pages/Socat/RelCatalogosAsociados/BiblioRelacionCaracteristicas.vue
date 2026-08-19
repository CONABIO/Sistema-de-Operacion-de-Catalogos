<template>
    <div>
        <div style="height: 830px; padding: 10px; background-color: #fff; display: flex; flex-direction: column;">

            <el-header class="header">
                <div class="header-content">
                    <h1 class="titulo">Asociación de característica - bibliografía</h1>
                </div>
            </el-header>
            <el-row :gutter="21">
                <el-col :span="18">
                    <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                        {{ props.taxonActual.label }}
                    </span>
                </el-col>
                <el-col :span="5" style="display: flex; justify-content: flex-end;">
                    <div style="display: flex; gap: 5px;">
                        <!--BotonSalir :accion="cerrar" @salir="closeDialog"
                                            style="flex-shrink: 0; min-width: max-content;" /-->
                        <BotonSalir accion="cerrar" @salir="closeDialog" style="flex-shrink: 0;" />
                    </div>
                </el-col>
            </el-row>
            <div style="flex: 1; min-height: 0;">
                <el-splitter style="height: 100%; border: 1px solid #ddd; border-radius: 8px;">
                    <el-splitter-panel :min="30" :size="'35%'">
                        <el-card class="panel-card list-panel" shadow="never">
                            <template #header>
                                <div class="header-container">
                                    <span class="details-header-title">
                                        Características asociadas al taxón
                                    </span>                                                                          
                                </div>  
                            </template>
                            <div class="demo-tree panel-nombre">
                                <el-tree :data = "datosTree"
                                          ref="treeCaractRef"
                                          node-key ="treeKey"
                                         :props="{ label: 'label', children: 'children' }"
                                          @node-click="onCurrentChange"                                                                                                
                                          :highlight-current="true"
                                          class="custom-element-tree">
                                    <template #default="{ data }">
                                        <div class="tree-node-wrapper">
                                            <Logo class="tree-node-logo" :rutaCategoria="data.biblio" />
                                            <span class="nodo-texto">{{ data.label }}</span>
                                            <el-select 
                                                v-if="data.tipo === 'region'"
                                                v-model="data.tipDistribucion"
                                                :disabled = "true"
                                                @change="tipDistSelecc(data)"
                                                placeholder="Tipo distribucón"
                                                 size="small"
                                                style="width:180px; margin-left:15px;"
                                            >
                                                <el-option
                                                    v-for="item in tablaTipoDist"
                                                    :key="item.IdTipoDistribucion"
                                                    :label="item.Descripcion"
                                                    :value="item.IdTipoDistribucion"
                                                />
                                            </el-select>
                                        </div>
                                    </template>
                                </el-tree>                                               
                            </div>                                        
                        </el-card>
                    </el-splitter-panel>
                    <el-splitter-panel>
                        <el-splitter layout="vertical" style="height:100%;">
                            <el-splitter-panel :size="500">
                                <el-card class="panel-card list-panel" shadow="never">
                                    <template #header>
                                        <div class="header-container">
                                            <span class="details-header-title">
                                                {{ compTitulo }}
                                            </span>                                                             
                                        </div>  
                                    </template>     
                                    <div style="flex: 1; padding: 10px; overflow: auto;">
                                        <TablaFiltrable :columnas="colDefBiblioFinal" :datos="tablaBibliografiasRel"
                                            :totalItems="totalBibliografiasRel" :alturaTabla="300"
                                            :highlight-current-row="true" 
                                            :mostrarNuevo="true"
                                            :mostrarEditar="true" 
                                            :mostrarBorrar="true" 
                                            :mostrarGuardar="true" 
                                            :mostrarSalir="false"
                                            :itemsPerPage = 15
                                            @nuevo-item="abrirBiblio" @eliminar-item="eliminarBiblioRel"
                                            @row-click="clickBiblioRel" @editar-item="clickEditarObs" 
                                            @guardar="clickGuardar"/>
                                    </div>             
                                </el-card>
                            </el-splitter-panel>
                            <el-splitter-panel :size="140">
                                <div
                                    style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                    <p style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                        Cita completa</p>
                                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                                        <el-input v-model="citaBiblio" type="textarea" :rows="3" disabled
                                            placeholder="Cita completa" style="flex: 1;" />
                                    </div>
                                </div>
                            </el-splitter-panel>
                            <el-splitter-panel :size="140">
                                <div
                                    style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                    <p style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                        Observaciones</p>
                                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                                        <el-input v-model="observaciones" type="textarea" :rows="3" :disabled = "habObservaciones"
                                            placeholder="Observaciones" style="flex: 1;" />
                                    </div>
                                </div>
                            </el-splitter-panel>
                        </el-splitter>
                    </el-splitter-panel>
                </el-splitter>
            </div>
        </div>

        <DialogForm v-model="dialogFormVisibleBiblio" :botCerrar="true" :pressEsc="false" :width="'85%'">
            <CuerpoBibliografia :isModal="true" :traspaso="true" :biblioAct="idsBibliografiasActuales"
                @cerrarBiblio="cerrarBiblio" @asociar="vincularInmediato" />
        </DialogForm>
    </div>
</template>
<script setup>
    import { ref, computed, watch, onMounted, h} from "vue";
    //import { ref, computed, watch, onMounted, h, onUnmounted, nextTick, onBeforeUnmount } from "vue";
    import Logo from '@/Components/Biotica/LogoCategoria.vue';
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
    import CuerpoBibliografia from '@/Pages/Socat/Bibliografia/CuerpoBibliografia.vue';
    import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
    import { ElMessageBox } from 'element-plus';
    import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
    import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
    import BotonSalir from '@/Components/Biotica/SalirButton.vue';

    const CaracteristicasTaxon = ref([]);
    const tablaTipoDist = ref([]);

    const compTitulo = ref("Bibliografias");

    const tablaBibliografiasRel = ref([]);
    const totalBibliografiasRel = ref(0); 
    const observaciones = ref(""); 
    const citaBiblio = ref("");
    const dialogFormVisibleBiblio = ref(false);
    const caractActual = ref([]);
    const regActual = ref([]);
    const tipoSelect = ref("");
    const habObservaciones = ref(true);
    const biblioSelect = ref(null);

    const notificacionTitulo = ref("");
    const notificacionMensaje = ref("");
    const notificacionTipo = ref("info");
    const notificacionDuracion = ref(5000);
    const notificacionVisible = ref(false);

    const emit = defineEmits([
        'cerrar'
    ]);

    const colDefBiblioFinal = ref([
        {
            prop: 'Autor', label: 'Autor', minWidth: '120',
            align: 'left', tipo: 'texto', filtrable: true
        },
        {
            prop: 'Anio', label: 'Año', minWidth: '100',
            align: 'center', tipo: 'texto', filtrable: true
        },
        {
            prop: 'TituloPublicacion', label: 'Titulo de la publicación', minWidth: '200',
            align: 'left', tipo: 'texto', filtrable: true
        },
        {
            prop: 'TituloSubPublicacion', label: 'Titulo de la sub-publicación', minWidth: '200',
            align: 'left', tipo: 'texto', filtrable: true
        }
    ]);

    const props = defineProps({
        taxonActual: { type: Object, required: true, default: () => ({}) },
        cargarCaract: { type: Boolean, default: false }
    });

    const idsBibliografiasActuales = computed(() => {
        return tablaBibliografiasRel.value.map(b => b.IdBibliografia || b.id);
    });

    const closeDialog = () =>{
        emit('cerrar');
    }

    const abrirBiblio = () => {
        dialogFormVisibleBiblio.value = true;
    };

    const clickEditarObs = (row) => {
        biblioSelect.value = row; 
        habObservaciones.value = false;
    }

    const clickGuardar = async() =>{
        let resp;
         if(tipoSelect.value === 'caracteristica'){
            resp = await axios.post("/actualizar-obs-biblio-caract", {
                params: {
                    idNombre: props.taxonActual.id,
                        idCaract: caractActual.value.id,
                        Biblio: biblioSelect.value.IdBibliografia,
                        observaciones: observaciones.value
                }
            })

            if(resp.status === 200){
                await cargaBiblioCaract(caractActual.value.id);
                habObservaciones.value = true;
            }
        }else{
            resp = await axios.post("/actualizar-obs-biblio-caract-Reg", {
                params: {
                        idNombre: props.taxonActual.id,
                        idCaract: caractActual.value.id,
                        idRegion: regActual.value.id,
                        idTipDis: regActual.value.tipDistribucion,
                        Biblio: biblioSelect.value.IdBibliografia,
                        observaciones: observaciones.value
                    }
            })

            if(resp.status === 200){
                cargarBibliografiasRelCaract(caractActual.value.id, regActual.value);
                habObservaciones.value = true;
            }
        }
    }

    const cerrarBiblio = async (idsSeleccionados) => {
        cargaCaractAsocTaxon();
        if(tipoSelect.value === 'caracteristica'){
            cargaBiblioCaract(caractActual.value.id);
        }else{
            cargarBibliografiasRelCaract(caractActual.value.id, regActual.value);
        }
        dialogFormVisibleBiblio.value = false;
    };

    const vincularInmediato = async (idBiblio) => {
        let resp;

        if (tipoSelect.value === 'caracteristica') {
            resp = await axios.post('/asociar-biblio-caract-solo', {
                  params: {
                        idNombre: props.taxonActual.id,
                        idCaract: caractActual.value.id,
                        Biblio: idBiblio,
                        observaciones: ""
                    }
            });
        }else{
            resp = await axios.post('/asociar-biblio-caract-region', {
                  params: {
                        idNombre: props.taxonActual.id,
                        idCaract: caractActual.value.id,
                        idRegion: regActual.value.id,
                        idTipDis: regActual.value.tipDistribucion,
                        Biblio: idBiblio,
                        observaciones: ""
                    }
            });
        }
    };

    const onCurrentChange = (data, nodo) =>{
        citaBiblio.value= "";
        observaciones.value= "";
        if(data.tipo === "caracteristica"){
            compTitulo.value =  "Bibliografías asociadas a: " + data.label;
            caractActual.value = data;
            tipoSelect.value = data.tipo;
            cargaBiblioCaract(data.id);
        }else{
            compTitulo.value = "Bibliografías asocidas a: " + nodo.parent.label +
                                " - " + data.label;
            caractActual.value = nodo.parent.data;
            regActual.value = data;
            tipoSelect.value = data.tipo;
            
            cargarBibliografiasRelCaract(nodo.parent.data.id, data);
        }
    }

    const cargarBibliografiasRelCaract = async (idCaract, data) => {
        try {
            const response = await axios.get('/obtener-biblio-caract-region', {
                params: {
                    IdNombre: props.taxonActual.id,
                    IdCatNombre: idCaract,
                    IdRegion: data.id,
                    IdTipoDistribucion: data.tipDistribucion
                }
            });
            if (response.status === 200) {
                tablaBibliografiasRel.value = response.data;
                totalBibliografiasRel.value = response.data.length;
            }
        } catch (error) {
            console.error("Error en la petición:", error);
        }
    };

    const cargaBiblioCaract = async(idCaract) =>{
        try {
            const response = await axios.get('/obtener-biblio-caract-solo', {
                params: {
                    IdNombre: props.taxonActual.id,
                    IdCatNombre: idCaract,
                }
            });

            if (response.status === 200) {
                tablaBibliografiasRel.value = response.data;
                totalBibliografiasRel.value = response.data.length;
               
            }
        } catch (error) {
            console.error("Error al cargar bibliografía general de la característica:", error);
        }
    }

    const clickBiblioRel = async(row) =>{
        habObservaciones.value = true;
        observaciones.value = row.Observaciones; 
        citaBiblio.value = row.CitaCompleta;
    }

    const eliminarBiblioRel = async(row) =>{
        const procederConEliminacion = async () => {

        try {
            ElMessageBox.close();

            let resp;
            
            if(tipoSelect.value === 'caracteristica'){
                resp = await axios.post('/eliminar-biblio-caract-solo', {
                    params: {
                        IdNombre: props.taxonActual.id,
                        IdCatNombre: caractActual.value.id,
                        Biblio: row.IdBibliografia
                    }
                });
                if(resp.status === 200)
                {
                   await cargaBiblioCaract(caractActual.value.id);
                }
            }else{

                resp = await axios.delete('/eliminar-biblio-caract-region', {
                    params: {
                        idNombre: props.taxonActual.id,
                        idCatNombre: caractActual.value.id,
                        idRegion: regActual.value.id,
                        idTipDist: regActual.value.tipDistribucion,
                        biblio: row.IdBibliografia
                    }
                });
                if(resp.status === 200)
                {
                    await cargarBibliografiasRelCaract(caractActual.value.id, regActual.value);
                }
            }

            mostrarNotificacion('Eliminación exitosa', `La relación se a eliminado correctamente.`, 'success');
        } catch (apiError) {
            mostrarNotificacionError('Aviso', `La relación no se puede eliminar.`, 'success');
        }
        };
        const cancelarEliminacion = () => {
            ElMessageBox.close();
        };

        let mensaje = "";

        mensaje = `La relación con bibliografia sera eliminada. ¿Realmente desea realizarlo?. Esta acción no se puede revertir`;

        ElMessageBox({
        title: 'Confirmar eliminación', showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
        message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
            h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
            h('div', { class: 'text-container' }, [h('p', null, mensaje)])
            ]),
            h('div', { class: 'footer-buttons' }, [
            h(BotonCancelar, { onClick: cancelarEliminacion }),
            h(BotonAceptar, { onClick: procederConEliminacion }),
            ])
        ])
        }).catch(() => { });
        //props.taxonActual.id
    }

    const datosTree = computed(() => {

        const caracteristicasOrdenadas = [...(CaracteristicasTaxon.value || [])]
            .sort((a, b) => {

                const nombreA = a.Caracteristica || '';
                const nombreB = b.Caracteristica || '';

                return nombreA.localeCompare(
                    nombreB,
                    'es',
                    {
                        sensitivity: 'base'
                    }
                );
            });

        return caracteristicasOrdenadas.map(caract => {

            const regionesOrdenadas = [...(caract.Regiones || [])]
                .filter(region => region)
                .sort((a, b) => {

                    const partesA = (a.Region || '').split('/');
                    const partesB = (b.Region || '').split('/');

                    const niveles = Math.max(
                        partesA.length,
                        partesB.length
                    );

                    for (let i = 0; i < niveles; i++) {

                        const nivelA = partesA[i] || '';
                        const nivelB = partesB[i] || '';

                        const resultado = nivelA.localeCompare(
                            nivelB,
                            'es',
                            {
                                sensitivity: 'base'
                            }
                        );

                        if (resultado !== 0) {
                            return resultado;
                        }
                    }

                    return 0;
                });

            return {
                treeKey: `C-${caract.IdCatNombre}`,
                id: caract.IdCatNombre,
                tipo: 'caracteristica',
                label: caract.Caracteristica,
                biblio: caract.BiblioCaract?.url || '',
                observaciones: caract.Observaciones || '',

                children: regionesOrdenadas.map(region => ({
                    treeKey: `C-${caract.IdCatNombre}-R-${region.IdRegion}`,
                    id: region.IdRegion,
                    tipo: 'region',
                    label: region.Region,
                    biblio: region.Biblio?.url || '',
                    observaciones: region.Observaciones || '',
                    tipDistribucion: region.TipDistribucion?.id ?? null
                }))
            };
        });
    });

    //Funciones para el tipo de distribucion 
    const cargaTiposDistribucion = async() =>{

        const respTipDist = await axios.get('/carga-tipos-distribucion', {
                params: {
                    origen: 'caracteristicas'
                }
            });

        if(respTipDist.status === 200){
            tablaTipoDist.value = respTipDist.data;
        }

    }

    const cargaCaractAsocTaxon = async() =>{
        
        const listCaract = await axios.get(`/cargaCaracTaxon/${props.taxonActual.id}`);

        if (listCaract.status === 200) {
            
            CaracteristicasTaxon.value = listCaract.data;
        }
    }

    const mostrarNotificacionError = (titulo, mensaje, tipo = "info", duracion = 5000) => {
        notificacionTitulo.value = titulo;
        notificacionMensaje.value = mensaje;
        notificacionTipo.value = tipo;
        notificacionDuracion.value = 5000;
        notificacionVisible.value = true;
    };


    watch(
        () => props.cargarCaract,
        (nuevoValor, valorAnterior) => {

            if (nuevoValor === true) {
                // El diálogo acaba de abrirse
                cargaCaractAsocTaxon();
                cargaTiposDistribucion();
            }
        }
    );

    onMounted( async () => {

        cargaCaractAsocTaxon();
        cargaTiposDistribucion();
        
    })
</script>

<style scope>
    .panel-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 8px;
    }

    .panel-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .list-panel {
        flex: 1;
        min-width: 300px;
    }

    .details-header-title {
        font-weight: 600;
        color: #303133;
    }

    .demo-tree {
        flex: 1;
        overflow: auto;
        min-height: 0;
    }

    .panel-nombre {
        flex: 1;
        min-height: 0;
        overflow: auto;
    }

    .custom-element-tree {
        display: inline-block;
        min-width: max-content;
    }

    .tree-node-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        font-size: 14px;
    }
  
    .tree-node-logo {
        width: 25px;
        height: 25px;
        flex-shrink: 0;
    }

    :deep(.el-tree-node.is-current > .el-tree-node__content) {
        background-color: #ddf6dd !important;
    }

    :deep(.el-tree-node.is-current .nodo-texto) {
        color: #007bff !important;
        font-weight: bold !important;
    }

    :deep(.el-tree-node.is-current:hover > .el-tree-node__content) {
        background-color: #c9eec9 !important;
    }

    :deep(.fila-activa-completa > .el-tree-node__content) {
        background-color: #ddf6dd !important;
        border-radius: 4px;
        margin-bottom: 1px;
    }

    :deep(.el-tree-node__content:has(.is-active-path)) {
        background-color: #ddf6dd !important;
        border-radius: 4px;
        margin: 1px 0;
    }


</style>