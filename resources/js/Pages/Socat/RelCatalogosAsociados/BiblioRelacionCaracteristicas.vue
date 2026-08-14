<template>
    <div>
        <div style="height: 830px; padding: 10px; background-color: #fff; display: flex; flex-direction: column;">

            <el-header class="header">
                <div class="header-content">
                    <h1 class="titulo">Asociación de característica - bibliografía</h1>
                </div>
            </el-header>
            <div style="padding: 15px 5px;">
                <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                    {{ props.taxonActual.label }}
                </span>
            </div>
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
                                                :disabled = "!(modoEdicion && nodoSeleccionado === data)"
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
                        <el-splitter layout="vertical">
                            <el-splitter-panel>
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
                                            :totalItems="totalBibliografiasRel" :alturaTabla="320"
                                            :highlight-current-row="true" :mostrarNuevo="true"
                                            :mostrarEditar="false" :mostrarBorrar="true" :mostrarSalir="false"
                                            @nuevo-item="abrirBiblio" @eliminar-item="eliminarBiblioRel"
                                            @row-click="clickBiblioRel" />
                                    </div>             
                                </el-card>
                            </el-splitter-panel>
                            <el-splitter-panel>
                                <div
                                    style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                    <p style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                        Observaciones</p>
                                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                                        <el-input v-model="observaciones" type="textarea" :rows="3" disabled
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
    import { ref, computed, watch, onMounted } from "vue";
    import Logo from '@/Components/Biotica/LogoCategoria.vue';
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";

    const CaracteristicasTaxon = ref([]);
    const tablaTipoDist = ref([]);

    const compTitulo = ref("Bibliografias");

    const tablaBibliografiasRel = ref([]);
    const totalBibliografiasRel = ref(0); 
    const observaciones = ref(""); 
    const dialogFormVisibleBiblio = ref(false);

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
            prop: 'CitaCompleta', label: 'Cita completa', minWidth: '200',
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

    const abrirBiblio = () => {
        dialogFormVisibleBiblio.value = true;
    };

    const onCurrentChange = (data, nodo) =>{
        console.log("Nodo actual: ", data);
        console.log("Padre: ", nodo.parent.data.id);
        if(data.tipo === "caracteristica"){
            compTitulo.value =  "Bibliografías asociadas a: " + data.label;

            cargaBiblioCaract(data.id);
        }else{
            compTitulo.value = "Bibliografías asocidas a: " + nodo.parent.label +
                                " - " + data.label
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
            console.log("Esta es la respuesta de bibliografias regiones: ", response);
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

            console.log("Esta es la respuesta: ", response);

            if (response.status === 200) {
                tablaBibliografiasRel.value = response.data;
                totalBibliografiasRel.value = response.data.length;
               
            }
        } catch (error) {
            console.error("Error al cargar bibliografía general de la característica:", error);
        }
    }

    const clickBiblioRel = async(row) =>{
        observaciones.value = row.Observaciones; 
    }

    const datosTree = computed(() =>
        CaracteristicasTaxon.value.map(caract => ({
            treeKey: `C-${caract.IdCatNombre}`,
            id: caract.IdCatNombre,
            tipo: 'caracteristica',
            label: caract.Caracteristica,
            biblio: caract.BiblioCaract.url,
            observaciones: caract.Observaciones,
            children: caract.Regiones.map(region => ({
                treeKey: `C-${caract.IdCatNombre}-R-${region.IdRegion}`,
                id: region.IdRegion,
                tipo: 'region',
                label: region.Region,
                biblio: region.Biblio.url,
                observaciones: region.Observaciones,
                tipDistribucion: region.TipDistribucion.id
            }))
        }))
    );

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
        console.log("Entre a carga caracteristicas");
        const listCaract = await axios.get(`/cargaCaracTaxon/${props.taxonActual.id}`);

        if (listCaract.status === 200) {
            console.log("Esta es la respuesta de lista de caracteristicas", listCaract);
            CaracteristicasTaxon.value = listCaract.data;
        }
    }

    watch(
        () => props.cargarCaract,
        (nuevoValor, valorAnterior) => {
            console.log('Dialog:', valorAnterior, '->', nuevoValor);

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