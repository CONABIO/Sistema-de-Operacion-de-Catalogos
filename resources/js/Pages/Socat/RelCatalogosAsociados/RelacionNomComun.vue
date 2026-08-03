<template>
    <div>
        <el-card class="box-card">
            <div class="common-layout">
                <el-container style="height: 72vh;">
                    <el-main style="padding: 15px; background: #fff; overflow: hidden;">
                        <div style="height: 650px;height: 560px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                            <el-splitter lazy>
                                <el-splitter-panel min="50">
                                    <div class="table-wrapper">
                                        <TablaFiltrable v-model:datos="tablaNomComun" v-model:totalItems="contRegNomCom"
                                            endpoint="/busca-nombre-comun" :columnas="columnasDefinidasNomCom"
                                            :opciones-filtro="opcionesFiltroNomComun" :itemsPerPage=100
                                            :mostrarAcci="false" :alturaTabla=452 :highlight-current-row="true"
                                            :mostrarBiblio="false" :mostrarNuevo="false" :mostrarEditar="false"
                                            :mostrarBorrar="false" :mostrarSalir="false" :mostrarNomComun="true"
                                            @row-click="clickNomComun" @abrirNomComun="abirNomCom">
                                            <template #expand-column>
                                                <el-table-column type="expand">
                                                    <template #default="{ row }">
                                                        <div class="expand-content-detail">
                                                            <p><strong>IdNomComun:</strong> {{ row.IdNomComun }}</p>
                                                            <p><strong>IdOriginal:</strong> {{ row.IdOriginal }}</p>
                                                            <p><strong>Catalogo:</strong> {{ row.Catalogo }}</p>
                                                            <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                                                            <p><strong>FechaModificacion:</strong> {{
                                                                row.FechaModificacion }}</p>
                                                        </div>
                                                    </template>
                                                </el-table-column>
                                            </template>
                                        </TablaFiltrable>
                                    </div>
                                </el-splitter-panel>
                                <el-splitter-panel min="50">
                                    <div style="height: 650px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                        <el-splitter layout="vertical" style="height:100%;">
                                            <el-splitter-panel>
                                                <el-card class="panel-card list-panel" shadow="never">
                                                    <template #header>
                                                        <div class="header-container">
                                                            <span class="details-header-title">Tipo de región</span>
                                                            <el-button style="background-color: springgreen;" circle
                                                                @click="handleManageTiposRegion">
                                                                <IconoMundo />
                                                            </el-button>
                                                        </div>
                                                    </template>
                                                    <div class="demo-tree panel-nombre">
                                                        <el-tree v-if="tiposRegionTreeData.length"
                                                            ref="tiposRegionTreeRef" :key="treeKey"
                                                            :data="tiposRegionTreeData"
                                                            :props="{ children: 'children', label: 'Descripcion' }"
                                                            node-key="IdTipoRegion" :highlight-current="true"
                                                            :expand-on-click-node="true"
                                                            :default-expanded-keys="expandedTipoRegionKeys"
                                                            :node-class="getTipoRegionNodeClass"
                                                            @node-click="handleTipoRegionSelected"
                                                            @node-expand="handleTipoRegionExpand"
                                                            @node-collapse="handleTipoRegionCollapse"
                                                            class="custom-element-tree">
                                                            <template #default="{ data }">
                                                                <span
                                                                    :class="[
                                                                        'nodo-tipo-region',
                                                                        { 'is-active-path': activePathIds.includes(data.IdTipoRegion) },
                                                                        { 'is-selected-node': selectedTipoRegionNode?.IdTipoRegion === data.IdTipoRegion }]">
                                                                    {{ data.Descripcion }}
                                                                </span>
                                                            </template>
                                                        </el-tree>
                                                    </div>
                                                </el-card>
                                            </el-splitter-panel>
                                            <el-splitter-panel>
                                                <el-card class="panel-card list-panel" shadow="never">
                                                    <template #header>
                                                        <div class="header-container">
                                                            <span class="details-header-title">Región</span>
                                                            <BotonRegiones @click="abrirReg"
                                                                style="flex-shrink: 0; min-width: max-content;" />
                                                        </div>
                                                    </template>
                                                    <div class="demo-tree panel-nombre">
                                                        <el-tree v-show="filteredRegionsTree.length" ref="treeRef"
                                                            :key="treeKey" :data="filteredRegionsTree"
                                                            :props="{ children: 'children', label: 'NombreRegion' }"
                                                            node-key="IdRegion"
                                                            :current-node-key="selectedNode?.IdRegion"
                                                            :highlight-current="true" :expand-on-click-node="true"
                                                            @node-click="handleNodeSelected"
                                                            class="custom-element-tree">
                                                            <template #default="{ node, data }">
                                                                <span :id="'region-node-' + data.IdRegion"
                                                                    class="nodo-texto">
                                                                    {{ node.label }}
                                                                </span>
                                                            </template>
                                                        </el-tree>
                                                    </div>
                                                </el-card>
                                            </el-splitter-panel>
                                        </el-splitter>
                                    </div>
                                </el-splitter-panel>
                            </el-splitter>
                        </div>
                    </el-main>
                </el-container>
            </div>
        </el-card>
        <Teleport to="body">
            <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
                @close="cerrarNotificacion" />
        </Teleport>
        <DialogForm v-model="dialogFormVisibleNomCom" :botCerrar="true" :pressEsc="false" :width="'83%'">
            <CuerpoNombreCom :modal="true" @cerrar="cerrarNomCom" />
        </DialogForm>
        <DialogForm v-model="esModalTipoRegionVisible" :bot-cerrar="true" :press-esc="true" width="90%">
            <CuerpoTipoRegion :treeDataProp="tiposRegionTreeData" :flatTreeDataProp="todosLosTiposDeRegion"
                :isModal="true" @cerrar-modal="cerrarModalTipoRegion" />
        </DialogForm>
        <DialogForm v-model="dialogFormVisibleReg" :botCerrar="true" :pressEsc="false" :width="'83%'">
            <CuerpoRegion :modal="true" @cerrar="cerrarReg" :treeDataProp="todosLosTiposDeRegion"
                :tiposDeRegionTreeProp="tiposRegionTreeData" :tiposDeRegionProp="todosLosTiposDeRegionTree" />
        </DialogForm>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, h, onUnmounted, nextTick } from "vue";
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import BotonTraspaso from '@/Components/Biotica/BtnTraspaso.vue';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import CuerpoNombreCom from '@/Pages/Socat/Nombres/CuerpoNombreComun.vue';
import CuerpoTipoRegion from '@/Pages/Socat/TipoRegion/CuerpoTipoRegion.vue';
import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
import IconoMundo from '@/Components/Biotica/IconoMundo.vue';
import CuerpoRegion from '@/Pages/Socat/Regiones/CuerpoRegion.vue';
import BotonRegiones from '@/Components/Biotica/BtnRegiones.vue';

const emit = defineEmits(['cerrar']);

//Variables declaradas para region
const tiposRegion = ref([]);
const tiposRegionTreeData = ref([]);
const selectedTipoRegionNode = ref(null);
const filterText = ref('');
const activePathIds = ref([]);
const tiposRegionTreeRef = ref(null);
const localTreeData = ref([]);
const selectedNode = ref(null);
const treeRef = ref(null);
const treeKey = ref(0);
let ultimoEventoExpandTime = 0;
const expandedTipoRegionKeys = ref([]);
const esModalTipoRegionVisible = ref(false);
const todosLosTiposDeRegion = ref([]);
const todosLosTiposDeRegionTree = ref([]);
const dialogFormVisibleReg = ref(false);

//Variables declaradas para nombre comun
const tablaNomComun = ref([]);
const contRegNomCom = ref(0);
const dialogFormVisibleNomCom = ref(false);

const opcionesFiltroNomComun = ref([
    { label: 'NombreComun', value: 'NomComun' },
    { label: 'Lengua', value: 'Lengua' },
    { label: 'Observaciones', value: 'Observaciones' }
]);

//Variables para funcionalidad de asignacion de relaciones
const idNomComun = ref([0]);
const idTipoReg = ref([0]);
const idRegion = ref([0]);
const idNombre = ref([0]);

const props = defineProps({
    taxonActual: { type: Object, required: true, default: () => ({}) },
});

//Varibles definidas para enviar mensajes
const notificacionTitulo = ref("");
const notificacionVisible = ref(false);
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);

//Definición de funciones
//Funciones definidas para region
//********************************************************************************************* */
const defaultProps = {
    children: 'children',
    label: 'Descripcion',
}

const seleccionarPrimeroPorDefault = () => {
    if (tiposRegionTreeData.value && tiposRegionTreeData.value.length > 0) {
        const primerNodo = tiposRegionTreeData.value[0];
        seleccionarTipoRegionBase(primerNodo);
    }
}

const seleccionarTipoRegionBase = (data) => {
    if (!data) return;
    filterText.value = '';
    selectedTipoRegionNode.value = data;
    activePathIds.value = findPathInTree(tiposRegionTreeData.value, data.IdTipoRegion) || [];

    nextTick(() => {
        tiposRegionTreeRef.value?.setCurrentKey(data.IdTipoRegion);
        if (filteredRegionsTree.value && filteredRegionsTree.value.length > 0) {
            const primerNodo = filteredRegionsTree.value[0];
            handleNodeSelected(primerNodo);
            const el = document.getElementById('region-node-' + primerNodo.IdRegion);

            if (el) {
                const contenedor = el.closest('.panel-nombre');

                if (contenedor) {
                    contenedor.scrollTop =
                        el.offsetTop - contenedor.clientHeight / 2;
                }
            }
        } else {
            selectedNode.value = null;
            if (treeRef.value) treeRef.value.setCurrentKey(null);
        }
    });
};

const findPathInTree = (nodes, targetId, path = []) => {
    for (const node of nodes) {
        const currentPath = [...path, node.IdTipoRegion];

        if (node.IdTipoRegion === targetId) {
            return currentPath;
        }
        if (node.children && node.children.length > 0) {
            const found = findPathInTree(node.children, targetId, currentPath);
            if (found) return found;
        }
    }
    return null;
};

const filteredRegionsTree = computed(() => {
    if (!selectedTipoRegionNode.value) return [];

    const targetTypeId = selectedTipoRegionNode.value.IdTipoRegion;


    const findTypePath = (nodes, targetId) => {
        for (const node of nodes) {
            if (node.IdTipoRegion === targetId) return [node.IdTipoRegion];
            if (node.children && node.children.length > 0) {
                const path = findTypePath(node.children, targetId);
                if (path) return [node.IdTipoRegion, ...path];
            }
        }
        return null;
    };
    const allowedTypeIds = findTypePath(tiposRegionTreeData.value, targetTypeId) || [];
    const filterAndPruneTree = (nodes) => {
        return nodes.reduce((accumulator, node) => {
            const nodeTypeId = node.IdTipoRegion;
            if (allowedTypeIds.includes(nodeTypeId)) {
                const newNode = { ...node, children: [] };
                if (nodeTypeId !== targetTypeId) {
                    if (node.children && node.children.length > 0) {
                        newNode.children = filterAndPruneTree(node.children);
                    }
                    accumulator.push(newNode);
                } else {
                    newNode.children = [];
                    accumulator.push(newNode);
                }
            }
            else if (node.children && node.children.length > 0) {
                const filteredChildren = filterAndPruneTree(node.children);
                if (filteredChildren.length > 0) {
                    accumulator.push({ ...node, children: filteredChildren });
                }
            }
            return accumulator;
        }, []);
    };

    return filterAndPruneTree(JSON.parse(JSON.stringify(localTreeData.value)));
});

const handleManageTiposRegion = () => {
    esModalTipoRegionVisible.value = true;
};

const cerrarModalTipoRegion = () => {
    cargaRegiones();
    esModalTipoRegionVisible.value = false;
}

const handleNodeSelected = (data) => {
    idTipoReg.value = data;
    selectedNode.value = data;
    nextTick(() => {
        treeRef.value?.setCurrentKey(data.IdRegion);
    });
};

const getTipoRegionNodeClass = (data) => {
    let classes = [];
    if (activePathIds.value.includes(data.IdTipoRegion)) {
        classes.push('fila-activa-completa');
    }
    return classes.join(' ');
};

const handleTipoRegionSelected = (data) => {
    const targetId = data.IdTipoRegion;
    const tree = tiposRegionTreeRef.value;
    const ahora = Date.now();
    idTipoReg.value = targetId;
    if (selectedTipoRegionNode.value?.IdTipoRegion === targetId) {
        if (ahora - ultimoEventoExpandTime < 100) return;
        filterText.value = '';
        if (activePathIds.value.length > 1) {
            const parentId = activePathIds.value[activePathIds.value.length - 2];
            const parentNode = findNodeInTipoRegionTree(tiposRegionTreeData.value, parentId);
            selectedTipoRegionNode.value = parentNode;
            activePathIds.value = activePathIds.value.slice(0, -1);
            tree?.setCurrentKey(parentId);
        } else {
            selectedTipoRegionNode.value = null;
            activePathIds.value = [];
            tree?.setCurrentKey(null);
        }
    } else {
        seleccionarTipoRegionBase(data);
    }
    selectedNode.value = null;
};

const handleTipoRegionExpand = (data) => {
    ultimoEventoExpandTime = Date.now();

    if (!expandedTipoRegionKeys.value.includes(data.IdTipoRegion)) {
        expandedTipoRegionKeys.value.push(data.IdTipoRegion);
    }
    seleccionarTipoRegionBase(data);
};

const handleTipoRegionCollapse = (data) => {
    ultimoEventoExpandTime = Date.now();
    expandedTipoRegionKeys.value = expandedTipoRegionKeys.value.filter(key => key !== data.IdTipoRegion);
    seleccionarTipoRegionBase(data);
};

function findNodeInTipoRegionTree(nodes, id) {
    for (const node of nodes) {
        if (node.IdTipoRegion === id) return node;
        if (node.children) {
            const found = findNodeInTipoRegionTree(node.children, id);
            if (found) return found;
        }
    }
    return null;
}

const cargaRegiones = async () => {

    const respRegiones = await axios.get('/carga-regiones');

    if (respRegiones.status === 200) {
        todosLosTiposDeRegion.value = respRegiones.data.treeData;//respRegiones.data.tiposDeRegionTree;
        todosLosTiposDeRegionTree.value = respRegiones.data.todosLosTiposDeRegion;
        tiposRegionTreeData.value = JSON.parse(JSON.stringify(respRegiones.data.tiposDeRegionTree));

        const existeSeleccion = selectedTipoRegionNode.value
            ? findNodeInTipoRegionTree(tiposRegionTreeData.value, selectedTipoRegionNode.value.IdTipoRegion)
            : null;
        if (tiposRegionTreeData.value.length > 0 && (!selectedTipoRegionNode.value || !existeSeleccion)) {
            seleccionarPrimeroPorDefault();
        }


        localTreeData.value = JSON.parse(JSON.stringify(respRegiones.data.treeData));
        treeKey.value++;
        if (selectedTipoRegionNode.value) {
            nextTick(() => {
                if (filteredRegionsTree.value && filteredRegionsTree.value.length > 0 && !selectedNode.value) {
                    handleNodeSelected(filteredRegionsTree.value[0]);
                }
            });
        }
    }
}

const abrirReg = async () => {
    dialogFormVisibleReg.value = true;
}

const cerrarReg = () => {
    cargaRegiones();
    dialogFormVisibleReg.value = false;
}

watch(activePathIds, (newPath) => {
    const padres = newPath.slice(0, -1);
    padres.forEach(id => {
        if (!expandedTipoRegionKeys.value.includes(id)) {
            expandedTipoRegionKeys.value.push(id);
        }
    });
}, { immediate: true });
//Funciones definidas para nombre comun
//******************************************************************************************** */
const columnasDefinidasNomCom = ref([
    {
        prop: 'NomComun', label: 'Nombre común', minWidth: '120',
        sortable: true, filtrable: true, align: 'left'
    },
    {
        prop: 'Lengua', label: 'Lengua', minWidth: '150',
        sortable: true, filtrable: true, align: 'left'
    },
    {
        prop: 'Observaciones', label: 'Observaciones', minWidth: '150',
        sortable: true, filtrable: true, align: 'left'
    },
]);

const clickNomComun = (row) => {

    idNomComun.value = row.IdNomComun;

    if (!props.taxonActual) {
        return;
    }

    idNombre.value = props.taxonActual.id
}

const cerrarNomCom = () => {
    cargaNomComun();
    dialogFormVisibleNomCom.value = false;
}

const abirNomCom = () => {
    dialogFormVisibleNomCom.value = true;
}

const cargaNomComun = async () => {
    const respNomCom = await axios.get('/cargaCatNomComun');

    if (respNomCom.status === 200) {
        tablaNomComun.value = respNomCom.data.data;
        contRegNomCom.value = respNomCom.data.total;
    }

}

//Funciones en general del traslado de datos
//******************************************************************************************** */
const onCreaRelacion = async () => {

    if (idTipoReg.value != idRegion.value['IdRegion']) {
        mostrarNotificacionError(
            "Error",
            "El tipo de región seleccionado y la región seleccionada no coinciden; por favor, seleccione una región apropiada.",
            "Error",
            5000
        );
    }
    else if (idNomComun.value <= 0 || idTipoReg.value <= 0 || idRegion.value <= 0) {
        mostrarNotificacionError(
            "Error",
            "La selección de nombre común, tipo de región y región debe ser seleccionada de manera forzosa.",
            "Error",
            5000
        );
    }

    const params = {
        idNombre: idNombre.value,
        idNomComun: idNomComun.value,
        idTipoReg: idTipoReg.value.IdTipoRegion,
        idRegion: idTipoReg.value.IdRegion
    }

    try {
        const response = await axios.post(`/alta-relNom-Nomcomun`, params);

        if (response.status === 200) {
            mostrarNotificacion('Aviso', response.data.message, 'success');
        }

    }
    catch (error) {
        if (error.response.status === 422) {
            const errorMessages = Object.values(error.response.data.errors).flat();
            errorMessages.forEach(msg => {
                mostrarNotificacionError(
                    "Error",
                    msg,
                    "Error",
                    5000
                );
            });
        }
    }
}

const mostrarNotificacion = (titulo, mensaje, tipo = "info", duracion = 5000) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = duracion;
    notificacionVisible.value = true;
};


const mostrarNotificacionError = (titulo, mensaje, tipo = "info", duracion = 5000) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = 5000;
    notificacionVisible.value = true;
};

const cerrarNotificacion = () => {
    notificacionVisible.value = false;
};

const closeDialog = () => {
    emit('cerrar');
};



onMounted(async () => {

    cargaNomComun();

    cargaRegiones();

})
</script>

<style scoped>
.details-header-title {
    font-weight: 600;
    color: #303133;
}


.panel-nombre {
    flex: 1;
    min-height: 0;
    overflow: auto;
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

:deep(.el-card__body) {
    padding: 10px;
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.panel-nombre {
    flex: 1;
    overflow: auto;
    min-height: 0;
}

.tree-card {
    width: 100%;
    max-width: 1600px;
    max-height: 590px;
    display: flex;
    flex-direction: column;
}

.table-wrapper :deep(.el-table__body tr.current-row > td) {
    background-color: #ddf6dd !important;
    color: #0d6efd !important;
    font-weight: bold;
}

/* Cabecera de la tabla */
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1px 16px;
    background: #d9e1eb;
    border-bottom: 1px solid #f5ebeb;
}

.table-title {
    font-size: 14px;
    font-weight: 600;
    color: #303133;
}

.demo-panel {
    width: 100%;
    height: 100%;
}

.demo-tree {
    flex: 1;
    overflow: auto;
    min-height: 0;
}

.custom-element-tree {
    display: inline-block;
    min-width: max-content;
}

.tree-full {
    width: 100%;
    height: 100%;
    overflow: auto;
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

.is-active-path {
    color: #007bff !important;
    font-weight: bold !important;
    background-color: transparent !important;
    transition: color 0.3s ease;
}

.nodo-tipo-region {
    font-size: 14px;
    padding: 2px 0;
}
</style>
