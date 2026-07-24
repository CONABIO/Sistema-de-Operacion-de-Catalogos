<template>
    <div>
        <el-card class="box-card">
            <div class="common-layout">
                <el-container style="height: 72vh;">
                    <el-header class="header">
                        <div class="header-content">
                            <h1 class="titulo">Asociación Taxón-Características-Región</h1>
                        </div>
                    </el-header>
                    <el-main style="padding: 15px; background: #fff; overflow: auto;">
                        <div style=" margin-bottom: 20px;">
                            <el-row :gutter="21">
                                <el-col :span="16">
                                    <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                        {{ props.taxonActual.label }} 
                                    </span>
                                </el-col>
                                <el-col :span="8" >
                                    <div style="display: flex; gap: 5px; justify-content: flex-end;">                                
                                        <BotonTraspaso @traspasa="onCreaRelacion" />
                                                                
                                        <BotonSalir accion="cerrar" @salir="closeDialog"
                                                        style="flex-shrink: 0; min-width: max-content;"/>
                                    </div>
                                </el-col>
                            </el-row>
                            <el-row :gutter="21">
                                <el-switch
                                    v-model="georeferido"
                                    class="ml-2"
                                    inline-prompt
                                    style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                                    active-text="Georreferido"
                                    inactive-text="No Georreferido"
                                />
                            </el-row>
                        </div>
                        <div style="height: 573px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                            <el-splitter>
                                <el-splitter-panel min="50">
                                    <el-card class="panel-card list-panel" shadow="never">
                                        <template #header>
                                            <div class="header-container">
                                                <span class="details-header-title">Características</span>
                                                <BotonCaract @click="abrirCaract"
                                                    style="flex-shrink: 0; min-width: max-content;"/>
                                            </div>
                                        </template>
                                        <div class="demo-tree panel-nombre">
                                            <el-tree
                                                ref="treeCaracteristicas"
                                                style="max-width: 600px"
                                                :data="datosCaracteristicas"
                                                :props="caractProps"
                                                node-key="IdCatNombre"
                                                highlight-current
                                                @node-click="handleNodeClickCarac"
                                                class="tree-caracteristicas">
                                                <template #default="{ node }">
                                                    <span class="nodo-texto">
                                                        {{ node.label }}
                                                    </span>
                                                </template>
                                            </el-tree>
                                        </div>
                                    </el-card>
                                </el-splitter-panel>
                                <el-splitter-panel min="50" v-if="georeferido">
                                    <div style="height: 573px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                        <el-splitter layout="vertical" style="height:100%;">
                                            <el-splitter-panel>
                                                <el-card class="panel-card list-panel" shadow="never">
                                                    <template #header>
                                                        <div class="header-container">
                                                            <span class="details-header-title">Tipo de región</span>
                                                            <el-button style="background-color: springgreen;" circle @click="handleManageTiposRegion">
                                                                <IconoMundo />
                                                            </el-button>
                                                        </div>
                                                    </template>
                                                    <div class="demo-tree panel-nombre">
                                                        <el-tree v-if="tiposRegionTreeData.length" ref="tiposRegionTreeRef" :key="treeKey"
                                                            :data="tiposRegionTreeData" :props="{ children: 'children', label: 'Descripcion' }"
                                                            node-key="IdTipoRegion" :highlight-current="true" :expand-on-click-node="true"
                                                            :default-expanded-keys="expandedTipoRegionKeys" :node-class="getTipoRegionNodeClass"
                                                            @node-click="handleTipoRegionSelected" @node-expand="handleTipoRegionExpand"
                                                            @node-collapse="handleTipoRegionCollapse" class="custom-element-tree">
                                                            <template #default="{ data }">
                                                                <span :class="[
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
                                                            <div class="header-buscador">
                                                                <el-input v-model="filterText"  placeholder="Escriba para buscar" clearable 
                                                                          :disabled="buscadorDeshabilitado" @keyup.enter="irAlNodoBuscado" />
                                                            </div>
                                                            <BotonRegiones @click="abrirReg"
                                                                style="flex-shrink: 0; min-width: max-content;"/>
                                                        </div>
                                                    </template>
                                                    <div class="demo-tree panel-nombre">                                                        
                                                        <el-tree v-show="filteredRegionsTree.length" ref="treeRef" :key="treeKey" :data="filteredRegionsTree"
                                                            :props="{ children: 'children', label: 'NombreRegion' }" node-key="IdRegion"
                                                            :current-node-key="selectedNode?.IdRegion" :highlight-current="true" :expand-on-click-node="true"
                                                            @node-click="handleNodeSelected" class="custom-element-tree">
                                                            <template #default="{ node, data }">
                                                                <span :id="'region-node-' + data.IdRegion" class="nodo-texto">
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
                                <el-splitter-panel min="50" v-if="georeferido">
                                    <el-card class="panel-card list-panel" shadow="never">
                                        <template #header>
                                            <div class="header-container">
                                                <span class="details-header-title">Tipo de distribución</span>
                                                 <BotonTipoDist @click="abrirTipoDist"
                                                        style="flex-shrink: 0; min-width: max-content;" />
                                            </div>
                                        </template>
                                        <div class="table-wrapper">
                                            <TablaFiltrable 
                                                v-model:datos = "tablaTipoDist"
                                                v-model:totalItems = "contRegTipDist"                                    
                                                endpoint="/busca-tipo-distribucion" 
                                                :columnas = "columnasDefinidasTipoDist" 
                                                :itemsPerPage = 100 
                                                :mostrarAcci = "false"
                                                :alturaTabla = 380
                                                :highlight-current-row = "true"
                                                :mostrarBiblio = "false"
                                                :mostrarNuevo = "false"
                                                :mostrarEditar = "false"
                                                :mostrarBorrar = "false"
                                                :mostrarSalir = "false"
                                                :mostrarNomComun = "false"
                                                :mostrarTipoDist = "false"
                                                @row-click="clickTipDist"> 
                                                <template #expand-column>
                                                    <el-table-column type="expand">
                                                        <template #default="{ row }">
                                                            <div class="expand-content-detail">
                                                                <p><strong>IdTipoDistribucion:</strong> {{ row.IdTipoDistribucion }}</p>
                                                                <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                                                                <p><strong>FechaModificacion:</strong> {{ row.FechaModificacion }}</p>
                                                            </div>
                                                        </template>
                                                    </el-table-column>
                                                </template>                                           
                                            </TablaFiltrable>
                                        </div>
                                    </el-card>
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
        <DialogForm v-model="esModalTipoRegionVisible" :bot-cerrar="true" :press-esc="true" width="90%">
            <CuerpoTipoRegion :treeDataProp="tiposRegionTreeData" :flatTreeDataProp="todosLosTiposDeRegion"
                    :isModal="true" @cerrar-modal="cerrarModalTipoRegion" />
        </DialogForm>
        <DialogForm v-model="dialogFormVisibleReg" :botCerrar="true" :pressEsc="false" :width="'83%'">
            <CuerpoRegion :modal="true" @cerrar="cerrarReg"
                :treeDataProp = "todosLosTiposDeRegion"
                :tiposDeRegionTreeProp = "tiposRegionTreeData"
                :tiposDeRegionProp = "todosLosTiposDeRegionTree"
            />
        </DialogForm>
        <DialogForm v-model="dialogFormVisibleCaract" :botCerrar="true" :pressEsc="false" :width="'83%'">
            <CuerpoCaracteristicas :modal="true" @cerrar="cerrarCarac"
                :treeDataProp="treeDataProp"
                :flatTreeDataProp="flatTreeDataProp"
            />
        </DialogForm>
        <DialogForm v-model="dialogFormVisibleTiposDist" :botCerrar="true" :pressEsc="false" :width="'83%'">
            <CuerpoTipos :modal="true" @cerrar="cerrarTipDist"/>
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
    import BotonCaract from '@/Components/Biotica/BtnCaracteristicas.vue';
    import BotonTipoDist from '@/Components/Biotica/BtnTipoDist.vue';
    import CuerpoCaracteristicas from '@/Pages/Socat/Caracteristicas/CuerpoCaracteristicas.vue';
    import CuerpoTipos from '@/Pages/Socat/TiposDistribucion/CuerpoTipoDistribucion.vue';

    const emit = defineEmits(['cerrar']);
    const georeferido = ref(true);

    //Variables declaradas para Tipo de distribucion 
    const tablaTipoDist = ref([]);
    const contRegTipDist = ref(0);
    const columnasDefinidasTipoDist = ref([
            { prop: 'Descripcion', label: 'Descripción', minWidth: '120', sortable: true, filtrable: true, align: 'left' }
        ]);
    const dialogFormVisibleTiposDist = ref(false); 
    const idTipoDist = ref(0);

    //Variables declaradas para caracteristicas 
    const datosCaracteristicas = ref ([]);
    const caractProps = {
        children: 'children',
        label: 'Descripcion'
    };
    const treeCaracteristicas = ref(null);
    const flatTreeDataProp = ref([]);
    const treeDataProp = ref([]);
    const dialogFormVisibleCaract = ref(false);
    const idCaracteristica = ref(0);

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

    //Variables para funcionalidad de asignacion de relaciones
    const idTipoReg = ref([0]);
    const idRegion = ref([0]);
    const idNombre = ref([0]);

    const props = defineProps({
        taxonActual: { type: Object, required: true, default: () => ({}) },
    });

    //Variables definidas para enviar mensajes 
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

    const cargaRegiones = async() =>{

        const respRegiones = await axios.get('/carga-regiones');

        if(respRegiones.status === 200)
        {
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

    const buscadorDeshabilitado = computed(() => {
        if (!selectedTipoRegionNode.value) return true;
        const esNivelRaiz = selectedTipoRegionNode.value.Descripcion.toUpperCase() === 'PAÍS';
        if (!esNivelRaiz && !selectedNode.value) {
            return true;
        }
        return false;
    });

    const irAlNodoBuscado = () => {

        if (!filterText.value || !treeRef.value) return;

        const textoBusqueda = filterText.value.toLowerCase();
        const idTipoTarget = selectedTipoRegionNode.value?.IdTipoRegion;
        const nombreTipoTarget = selectedTipoRegionNode.value?.Descripcion || "este nivel";

        const encontrarEnArbol = (nodos) => {
            for (const nodo of nodos) {
                const coincideNombre = (nodo.NombreRegion || "").toLowerCase().includes(textoBusqueda);
                const coincideTipo = nodo.IdTipoRegion === idTipoTarget;

                if (coincideNombre && coincideTipo) return nodo;

                if (nodo.children?.length) {
                    const encontrado = encontrarEnArbol(nodo.children);
                    if (encontrado) return encontrado;
                }
            }
            return null;
        };

        let match = null;
        let ambitoBusquedaNombre = "el catálogo";
        if (selectedNode.value) {
            ambitoBusquedaNombre = `"${selectedNode.value.NombreRegion}"`;
            const nodoActual = treeRef.value.getNode(selectedNode.value.IdRegion);
            if (nodoActual && nodoActual.data.children) {
                match = encontrarEnArbol(nodoActual.data.children);
            }
        } else {
            match = encontrarEnArbol(filteredRegionsTree.value);
        }
        if (match) {
            selectedNode.value = match;
            treeRef.value.setCurrentKey(match.IdRegion);
            let nodeInTree = treeRef.value.getNode(match.IdRegion);
            if (nodeInTree) {
                let parent = nodeInTree.parent;
                while (parent) {
                    parent.expanded = true;
                    parent = parent.parent;
                }
            }

            nextTick(() => {
                const el = document.getElementById('region-node-' + match.IdRegion);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        } else {
            mostrarNotificacion(
                "Aviso",
                `No se encontró "${filterText.value}" como ${nombreTipoTarget}.`,
                "warning"
            );
        }
    };

    const abrirReg = async () => {
        console.log("Esta es la funcion de abrir regiones");
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

    //Funciones en general del traslado de datos
    //******************************************************************************************** */
    const onCreaRelacion = async () => {        

        let response;

        if(georeferido.value){
            if(idCaracteristica <= 0 || idTipoReg.value.IdTipoRegion <= 0 
                || idTipoReg.value.IdRegion || idTipoDist.value <= 0){
                mostrarNotificacionError(
                    "Error",
                    "Cuando la característica a relacionar es georreferida, se debe seleccionar tipo de región, la región y el tipo de distribución para continuar.",
                    "Error",
                    5000
                );
            } else {
                console.log("Aqui se debe de hacer el llamado para guardar los cambios ");
            }
        } else {
            console.log("No se va a georeferir");  
            if(idCaracteristica <= 0){
                mostrarNotificacionError(
                        "Error",
                        "Se debe de seleccionar una caracteristica para continuar",
                        "Error",
                        5000
                    );
            } else {
                console.log("Aqui se debe de hacer el llamado para guardar los cambios ");
            }         
        }

        /*if(idTipoReg.value != idRegion.value['IdRegion'])
        {
            mostrarNotificacionError(
                        "Error",
                        "El tipo de región seleccionado y la región seleccionada no coinciden; por favor, seleccione una región apropiada.",
                        "Error",
                        5000
                    );
        }
        else if(idTipoReg.value <= 0 || idRegion.value <= 0){
            mostrarNotificacionError(
                        "Error",
                        "La selección de nombre común, tipo de región y región debe ser seleccionada de manera forzosa.",
                        "Error",
                        5000
                    );           
        }

         const params = { idNombre: idNombre.value,
                          idTipoReg: idTipoReg.value.IdTipoRegion,
                          idRegion: idTipoReg.value.IdRegion
          }

        try{
            const response = await axios.post(`/alta-relNom-Nomcomun`, params);
                  
            if(response.status === 200)
            {  
                mostrarNotificacion('Aviso', response.data.message, 'success');
            }

        }
        catch(error){
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
        }*/
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

    //Funciones para las caracteristicas 

    const cargaCaracteristicas = async() =>{

        const respCaract = await axios.get('/cargar-caracteristicas');

        if(respCaract.status === 200)
        {
            datosCaracteristicas.value = respCaract.data.treeDataProp;
            treeCaracteristicas.value.setCurrentKey(datosCaracteristicas.value[0].IdCatNombre);
            handleNodeClickCarac(datosCaracteristicas.value[0])
        }
    }

    const abrirCaract = async () => {
        const respCarac = await axios.get('/cargar-caracteristicas');

        if(respCarac.status === 200){

            flatTreeDataProp.value = respCarac.data.flatTreeDataProp;
            treeDataProp.value = respCarac.data.treeDataProp;
        }

        dialogFormVisibleCaract.value = true;
    }

    const cerrarCarac = () => {
        cargaCaracteristicas();
        dialogFormVisibleCaract.value = false;
    }

    const handleNodeClickCarac = (row) =>{
        idCaracteristica.value = row.IdCatNombre;
    }

    watch(datosCaracteristicas, async (nuevoValor) => {
        if (nuevoValor.length > 0) {
            await nextTick()

            treeCaracteristicas.value.setCurrentKey(nuevoValor[0].IdCatNombre)

            handleNodeClickCarac(nuevoValor[0])
        }
    })

    //Funciones para el tipo de distribucion 
    const cargaTiposDistribucion = async() =>{

        const respTipDist = await axios.get('/carga-tipos-distribucion', {
                params: {
                    origen: 'caracteristicas'
                }
            });

        if(respTipDist.status === 200){
            
            tablaTipoDist.value = respTipDist.data;
            contRegTipDist.value = respTipDist.data.length;
        }

    }

    const abrirTipoDist = () =>{
        dialogFormVisibleTiposDist.value = true;
    }

    const clickTipDist = (row) =>{
        idTipoDist.value = row.IdTipoDistribucion;
        console.log("Este es el valor del row seleccionado: ", row.IdTipoDistribucion);
    }

    const cerrarTipDist = () =>{
        cargaTiposDistribucion();
        dialogFormVisibleTiposDist.value = false;
    }

    //Funciones al montado del componente 
    onMounted( async () => {

        cargaRegiones();

        cargaCaracteristicas();

        cargaTiposDistribucion();
        
    })
</script>

<style scoped>
  
    .table-wrapper :deep(.el-table__body tr.current-row > td) {
      background-color: #ddf6dd !important;
      color: #0d6efd !important;
      font-weight: bold;
    }

    .details-header-title {
        font-weight: 600;
        color: #303133;
    }

    .panel-nombre {
        flex: 1;
        min-height: 0;
        overflow: auto;
    }

    .panel-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .tree-caracteristicas {
        height: 100%;
    }

    .list-panel {
        flex: 1;
        min-width: 300px;
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 8px;
    }

    :deep(.el-splitter__bar) {
        width: 2px !important;
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
        margin-right: 6px;
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
