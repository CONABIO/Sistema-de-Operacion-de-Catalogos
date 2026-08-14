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
                                    inactive-text="No georreferido"
                                />
                            </el-row>
                        </div>
                        <div style="height: 573px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                            <el-splitter>
                                <el-splitter-panel min="50" size="27%">
                                    <el-card class="panel-card list-panel" shadow="never" 
                                             :class="{ 'card-disabled': arbolDeshabilitado }">
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
                                                    <span :id="'caracteristica-node-' + node.IdCatNombre" class="nodo-texto">
                                                        {{ node.label }}
                                                    </span>
                                                </template>
                                            </el-tree>
                                        </div>
                                    </el-card>
                                </el-splitter-panel>
                                <el-splitter-panel min="50" size="30%" v-if="georeferido">
                                    <div style="height: 573px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                        <el-splitter layout="vertical" style="height:100%;">
                                            <el-splitter-panel>
                                                <el-card class="panel-card list-panel" shadow="never"
                                                         :class="{ 'card-disabled': arbolDeshabilitado }">
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
                                                <el-card class="panel-card list-panel" shadow="never"
                                                         :class="{ 'card-disabled': arbolDeshabilitado }">
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
                                <el-splitter-panel min="50">
                                    <el-splitter layout="vertical" style="height:100%;">
                                        <el-splitter-panel size = "78%">
                                            <el-card class="panel-card list-panel" shadow="never">
                                                <template #header>
                                                    <div class="header-container">
                                                        <span class="details-header-title">Características asociadas al taxón</span>  
                                                        <div style="display: flex; gap: 5px; justify-content: flex-end;">   
                                                            <BotonTraspaso @traspasa="onCreaRelacion" /> 
                                                            <EditarButton  @editar="onEditar" :disabled = "habEdiBorr"/>
                                                            <GuardarButton @click="Guardar" :disabled = "habGuardado"
                                                                            style="flex-shrink: 0; min-width: max-content;"/>
                                                            <EliminarButton @eliminar="onEliminar" :habActTax = "habEdiBorr" />
                                                            <div>
                                                                <el-tooltip class="item" effect="dark" content="Bibliografia">
                                                                    <el-button @click="abrirBiblioCaract" circle style="flex-shrink: 0;
                                                                background-color: #509165; color: white;">
                                                                        <el-icon>
                                                                            <Management />
                                                                        </el-icon>
                                                                    </el-button>
                                                                </el-tooltip>
                                                            </div>
                                                            <BotonTipoDist @click="abrirTipoDist" style="flex-shrink: 0; min-width: max-content;" />    
                                                        </div>                                                                                 
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
                                             <div
                                                style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                                <p
                                                    style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                                    {{ tituloObservaciones }}</p>
                                                <div style="display: flex; align-items: flex-start; gap: 10px;">
                                                    <el-input v-model="ObsCaracteristicas" type="textarea" :rows="3"
                                                        :disabled = "actObsCaractReg" placeholder="Observaciones"
                                                        style="flex: 1;" />
                                                </div>
                                            </div>
                                        </el-splitter-panel>
                                    </el-splitter>                                   
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

        <!--Aqui van las funciones de bibliografia-->
        <DialogForm v-model="dialogFormVisibleBiblioCaract" :botCerrar="true" :pressEsc="false" :width="'83%'">
            <BiblioCaract :taxonActual = "props.taxonActual", :cargarCaract = "dialogFormVisibleBiblioCaract"/>
        </DialogForm>

    </div>
</template>

<script setup>
    import { ref, computed, watch, onMounted, h, onUnmounted, nextTick, onBeforeUnmount } from "vue";
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
    import Logo from '@/Components/Biotica/LogoCategoria.vue';
    import EditarButton from '@/Components/Biotica/EditarButton.vue';
    import EliminarButton from '@/Components/Biotica/EliminarButton.vue';
    import GuardarButton from '@/Components/Biotica/GuardarButton.vue';
    import { Management } from '@element-plus/icons-vue';
    import { ElMessageBox } from 'element-plus';
    import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
    import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
    import BiblioCaract from '@/Pages/Socat/RelCatalogosAsociados/BiblioRelacionCaracteristicas.vue';

    const emit = defineEmits(['cerrar']);
    const georeferido = ref(true);

    const tablaTipoDistRef = ref(null);

    const dialogFormVisibleBiblioCaract = ref(false);

    //Varibles declaradas para para caracteristicas asocidas al taxón
    const modoEdicion = ref(false);
    const modoEdic = ref(false);
    const nodoSeleccionado = ref(null);
    const nodoPendiente = ref(null);
    const treeCaractRef = ref(null);
    const tituloObservaciones= ref('Observaciones');
    const idRegionAnt = ref(0);
    const actObsCaractReg = ref(true);
    const ObsCaracteristicas = ref("");
    const arbolDeshabilitado = ref(false);
    const idTipoRegion = ref(0);
    const idCatNombre = ref(0);

    const dialogResumenCaractSoloVisible = ref(false);

    const habEdiBorr = ref(true);
    const habGuardado = ref(true); 
    const ramaSelecc = ref(null);
    
    //Variables declaradas para Tipo de distribucion 
    const tablaTipoDist = ref([]);
    const contRegTipDist = ref(0);
    const columnasDefinidasTipoDist = ref([
            { prop: 'Descripcion', label: 'Descripción', minWidth: '120', sortable: true, filtrable: true, align: 'left' }
        ]);
    const dialogFormVisibleTiposDist = ref(false); 
    const idTipoDist = ref(0);
    const idTipoDistAnt = ref(0);

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
    const CaracteristicasTaxon = ref([]);

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

    const seleccionarTipoRegionBase = async (data) => {
        if (!data) return;
        filterText.value = '';
        selectedTipoRegionNode.value = data;
        activePathIds.value = findPathInTree(tiposRegionTreeData.value, data.IdTipoRegion) || [];

        await nextTick();
        
        tiposRegionTreeRef.value?.setCurrentKey(data.IdTipoRegion);

        if (filteredRegionsTree.value.length > 0) {
            const primerNodo = filteredRegionsTree.value[0];
            handleNodeSelected(primerNodo);
        } else {
            selectedNode.value = null;
            treeRef.value?.setCurrentKey(null);
        }

        await nextTick();
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

    const handleTipoRegionSelected = async(data) => {
        console.log("Esto es lo que llega a data de tipo de region: ", data);
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
            await seleccionarTipoRegionBase(data);
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

    //Funcion para buscar el tipo de region en el arbol 
    async function irATipoRegion(idTipoRegion) {

        const tree = tiposRegionTreeRef.value;

        const node = tree.getNode(idTipoRegion);

        if (!node) {
            console.log("No existe el nodo");
            return;
        }

        // Expandir todos los padres
        let padre = node.parent;

        while (padre) {
            padre.expanded = true;
            padre = padre.parent;
        }

        await nextTick();

        // Seleccionar el nodo
        tree.setCurrentKey(idTipoRegion);

        // Si utilizas selectedTipoRegionNode
        selectedTipoRegionNode.value = node.data;

        await nextTick();

        // Llevarlo al centro del scroll
        tree.$el
            .querySelector(".el-tree-node.is-current")
            ?.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });
    }

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

    const obtenerRutaRegion = (idRegion) => {
        const ruta = [];

        const buscar = (nodos) => {
            if (!nodos) return false;

            for (const nodo of nodos) {

                // Encontramos el nodo buscado
                if (nodo.IdRegion == idRegion) {
                    ruta.push(nodo.NombreRegion);
                    return true;
                }

                // Buscar dentro de sus hijos
                if (nodo.children && nodo.children.length) {
                    if (buscar(nodo.children)) {
                        // El hijo fue encontrado, agregamos este padre
                        ruta.unshift(nodo.NombreRegion);
                        return true;
                    }
                }
            }

            return false;
        };

        buscar(filteredRegionsTree.value);

        return ruta.join('/');
    };

    const obtenerRutaCaracteristica = (idCaracteristica) => {
        const ruta = [];

        const buscar = (nodos, idBuscado) => {
            for (const nodo of nodos) {

                // Encontramos el nodo seleccionado
                if (nodo.IdCatNombre == idBuscado) {
                    ruta.unshift(nodo.Descripcion);

                    // Si tiene padre, seguimos buscando hacia arriba
                    if (nodo.IdAscendente != null) {
                        buscar(datosCaracteristicas.value, nodo.IdAscendente);
                    }

                    return true;
                }

                // Buscar dentro de los hijos
                if (nodo.children && nodo.children.length > 0) {
                    if (buscar(nodo.children, idBuscado)) {
                        return true;
                    }
                }
            }

            return false;
        };

        buscar(datosCaracteristicas.value, idCaracteristica);

        return ruta.join('/');
    };

    //Funciones en general del traslado de datos
    //******************************************************************************************** */
    const onCreaRelacion = async (data) => {  

        
        idNombre.value = props.taxonActual.id;

        if(georeferido.value){
            
            habGuardado.value= false;
        
            if(idCaracteristica.value <= 0 || idTipoReg.value.IdTipoRegion <= 0 
                || idTipoReg.value.IdRegion <=0 ){
                mostrarNotificacionError(
                    "Error",
                    "Cuando la característica a relacionar es georreferida, se debe seleccionar tipo de región y la región para continuar.",
                    "Error",
                    5000
                );
            } else {
                console.log("Esto es lo que voy a pasar de datos: ", idTipoReg.value);
                idRegion.value = idTipoReg.value.IdRegion;
                idTipoRegion.value = idTipoReg.value.IdTipoRegion;
                idCatNombre.value = idCaracteristica.value;

                const cadRegion = obtenerRutaRegion(idTipoReg.value.IdRegion);

                const caracteristica = CaracteristicasTaxon.value.find(
                    item => item.IdCatNombre == idCaracteristica.value
                );

                if(caracteristica) {
                    const nuevaRegion = {
                        IdRegion: idTipoReg.value.IdRegion,
                        Region: cadRegion,
                        Observaciones: null,
                        Biblio: {
                            url: "/storage/images/Libro_Rojo.svg"
                        },
                        TipDistribucion: {
                            id: null
                        }
                    };

                    caracteristica.Regiones.push(nuevaRegion);

                }else{
                    const cadCaract = obtenerRutaCaracteristica(idCaracteristica.value);
                    
                    const nuevaCaract = {
                        BiblioCaract:{
                            texto:"",
                            url:"/storage/images/Libro_Rojo.svg"
                        },
                        Caracteristica: cadCaract,
                        IdCatNombre: idCaracteristica.value,
                        Observaciones:"",
                        Regiones:[
                            {
                                IdRegion: idTipoReg.value.IdRegion,
                                Region: cadRegion,
                                Observaciones: null,
                                Biblio: {
                                    url: "/storage/images/Libro_Rojo.svg"
                                },
                                TipDistribucion: {
                                    id: null
                                }
                            }
                        ]
                    }

                    CaracteristicasTaxon.value.push(nuevaCaract);
                }

                await nextTick();

                const nuevaRegionTree = datosTree.value
                    .find(item => item.id == idCaracteristica.value)
                    ?.children.find(
                        item => item.id == idTipoReg.value.IdRegion
                );

                if(nuevaRegionTree){
                    nodoPendiente.value = nuevaRegionTree;
                    nodoSeleccionado.value = nuevaRegionTree;
                    modoEdicion.value = true;
                    arbolDeshabilitado.value = true;
                }   
            }
        } else {            
            if(idCaracteristica.value <= 0){
                mostrarNotificacionError(
                        "Error",
                        "Se debe de seleccionar una caracteristica para continuar",
                        "Error",
                        5000
                    );
            } else {

                const params = { idNombre: idNombre.value,
                                 idCaract: idCaracteristica.value,
                    };
                try{

                    const response = await axios.post(`/alta-relTaxon-Caract`, params);

                    if(response.status === 200)
                    {  
                        console.log("Esta es la respuesta: ", response);
                        mostrarNotificacion('Aviso', response.data.message, 'success');
                        idCaracteristica.value = 0;
                        treeCaracteristicas.value.setCurrentKey(null);

                        cargaCaractAsocTaxon();
                    }
                }
                catch(error){
                    if (error.response?.status === 422) {
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

    //Funciones para las caracteristicas 

    const cargaCaracteristicas = async() =>{

        const respCaract = await axios.get('/cargar-caracteristicas');

        if(respCaract.status === 200)
        {
            datosCaracteristicas.value = respCaract.data.treeDataProp;

            console.log("lista de caracteristicas :", datosCaracteristicas.value);
        }
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

    onMounted(() => {
        window.addEventListener('keydown', manejarEscape );
    });

    onBeforeUnmount(() => {
        window.removeEventListener('keydown', manejarEscape );
    });

  
    function buscarPorRegion(nodes, idRegion) {

        for (const node of nodes) {

            if (node.IdRegion === idRegion) {
                return node;
            }

            if (node.children && node.children.length > 0) {

                const encontrado = buscarPorRegion(node.children, idRegion);

                if (encontrado) {
                    return encontrado;
                }
            }
        }

        return null;
    }

    function buscarPorTipoRegion(nodes, idTipoRegion) {

        for (const node of nodes) {

            if (node.IdTipoRegion === idTipoRegion) {
                return node;
            }

            if (node.children && node.children.length > 0) {

                const encontrado = buscarPorTipoRegion(node.children, idTipoRegion);

                if (encontrado) {
                    return encontrado;
                }
            }
        }

        return null;
    }

    //Funcion para marcar la region
    const seleccionarNodo = async (treeRef, id, prefix) => {
        console.log("treeRef", treeRef);
        console.log("id", id);
        console.log("prefix", prefix);
        await nextTick();

        const tree = treeRef.value;
        if (!tree) return;

        const node = tree.getNode(id);

        if (!node) {
            console.warn("No se encontró el nodo", id);
            return;
        }

        let parent = node.parent;
        while (parent) {
            parent.expanded = true;
            parent = parent.parent;
        }

        await nextTick();
        await new Promise(resolve => setTimeout(resolve, 100));

        tree.setCurrentKey(id);

        await nextTick();

        document
            .getElementById(`${prefix}${id}`)
            ?.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
    };

    const tipDistSelecc = (data) => {
        idTipoDist.value = data.tipDistribucion;
    }

    const onCurrentChange = async(data) => {

        ramaSelecc.value = data;
        
        habEdiBorr.value = false;
        if(nodoPendiente.value && modoEdicion.value){
            if(data !== nodoPendiente.value){
                if(!modoEdic.value){
                    mostrarNotificacion("Aviso",
                                        "Debe seleccionar el tipo de distribución del nuevo registro y guardar los cambios antes de seleccionar otro nodo.",
                                        "warning"
                    );
                }else{
                    mostrarNotificacion("Aviso",
                                        "Deben de guardar los cambios para continuar o presionar ESC para cancelar.",
                                        "warning"
                    );
                }
                await nextTick();
                treeCaractRef.value?.setCurrentKey(
                    nodoPendiente.value.treeKey
                );

                return;
            }
        }

        nodoSeleccionado.value = data;

        if(data.tipo === 'caracteristica'){
            await seleccionarNodo(treeCaracteristicas, data.id, 'caracteristica-node-');

            tituloObservaciones.value = "Observaciones de las características asociadas al taxón.";

            ObsCaracteristicas.value = data.observaciones;

            seleccionarPrimeroPorDefault();
            idRegionAnt.value = 0;
            idCaracteristica.value =  data.id;

        }else if (data.tipo === 'region'){

            const idCaract = data.treeKey.split("-")[1];
            
            const region = buscarPorRegion(localTreeData.value, data.id);

            const tipoRegion = buscarPorTipoRegion(tiposRegionTreeData.value,  region.IdTipoRegion)

            if(idRegionAnt.value != region.IdTipoRegion)
            {
                await handleTipoRegionSelected(tipoRegion);
            }

            nodoPendiente.value = nodoSeleccionado.value;

            idRegionAnt.value = region.IdTipoRegion;

            await seleccionarNodo(treeRef, region.IdRegion, 'region-node-');

            await seleccionarNodo(treeCaracteristicas, idCaract, 'caracteristica-node-');

            //irATipoRegion()
            tituloObservaciones.value = "Observaciones de la región asociada a la característica asociada al taxón.";
            idCaracteristica.value =  data.id;
            
            ObsCaracteristicas.value = data.observaciones;
            if(modoEdicion.value && idTipoDistAnt.value === 0){
                idTipoDistAnt.value = data.tipDistribucion;
            }
            console.log("Este es el tipo de distribucion: ", data.tipDistribucion);
        }
    }    

    const onEliminar = () => {

        const procederConEliminacion = async () => {

        try {
            ElMessageBox.close();

            if(ramaSelecc.value.tipo === 'caracteristica'){ 
                const response = await axios.delete('/eliminar-Caract-Taxon', { data: {
                                        idNombre: props.taxonActual.id,
                                        idCaract: ramaSelecc.value.id 
                                    }
                                });  
            }else{
                const idCaracteristica = ramaSelecc.value.treeKey.split('-')[1];
              
                const response = await axios.delete('/eliminar-Caract-Taxon-Reg', { data: {
                                        idNombre: props.taxonActual.id,
                                        idCaract: idCaracteristica,
                                        idRegion: ramaSelecc.value.id,
                                        idTipoDist: ramaSelecc.value.tipDistribucion
                                    }
                                });  
            }

            const treeKey = ramaSelecc.value.treeKey;

            const nodo = treeCaractRef.value.getNode(treeKey);

            if (nodo) {
                treeCaractRef.value.remove(nodo);
            }

            mostrarNotificacion('Eliminación exitosa', `La relación se a eliminado correctamente.`, 'success');
        } catch (apiError) {
            mostrarNotificacionError('Aviso', `La relación de: ${ramaSelecc.value.label} no se puede eliminar.`, 'success');
        }
        };
        const cancelarEliminacion = () => {
            ElMessageBox.close();
        };

        let mensaje = "";

        if(ramaSelecc.value.tipo === 'caracteristica'){
            mensaje = `La relación de la caracteristica "${ramaSelecc.value.label}" sus regiones y bibliografia
                         seran eliminadas. ¿Realmente desea realizarlo?. Esta acción no se puede revertir`;
        }else{
            mensaje = `La relación de la region "${ramaSelecc.value.label}" y bibliografia
                         seran eliminadas. ¿Realmente desea realizarlo?. Esta acción no se puede revertir`;
        }

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
    }

    const onEditar = () => {
        modoEdicion.value = true
        actObsCaractReg.value = false;
        arbolDeshabilitado.value = true;
        nodoPendiente.value = nodoSeleccionado.value;
        modoEdic.value = true;
        habGuardado.value = false;
    }

    const cancelarEdicion = async() => {
        console.log("Nodo seleccionado al cancelar: ", nodoSeleccionado.value);
        console.log("Este es el id tipo distribucion anterior: ", idTipoDistAnt.value);
        nodoSeleccionado.value.tipDistribucion = idTipoDistAnt.value;
        modoEdic.value = false;
        nodoPendiente.value = null;
        modoEdicion.value = false;
        actObsCaractReg.value = true;
        ObsCaracteristicas.value = "";
        arbolDeshabilitado.value = false;
        habGuardado.value = true;
        idTipoDistAnt.value = 0;
    }

    const manejarEscape = (event) => {
        if (event.key === 'Escape' && modoEdicion.value) {
            cancelarEdicion();
        }
    };

    const Guardar = async () => {
        if(modoEdic.value){
            console.log("Nodo seleccionado: ", nodoSeleccionado.value);
            console.log("idNombre: ", props.taxonActual.id);
            if(nodoSeleccionado.value.tipo === "caracteristica"){
                const params = { idNombre: props.taxonActual.id,
                                 idCatNombre: nodoSeleccionado.value.id,
                                 observaciones: ObsCaracteristicas.value,
                    };
                
                const resp = await axios.put(`/actualiza-Caract-Taxon`, params);
                    console.log("Esta es la respuesta: ", resp);
                if(resp.status === 200){
                    mostrarNotificacion('Aviso', resp.data.message, 'success');
                    idTipoDist.value = 0;
                    idTipoReg.value = null;
                    idTipoDist.value = 0;
                    idCaracteristica.value = 0;
                    idCatNombre.value = 0;
                    idTipoRegion.value = 0;
                    idRegion.value = 0;
                    arbolDeshabilitado.value = false;
                    nodoSeleccionado.value = null;
                    modoEdicion.value = false;
                    nodoPendiente.value = null;
                    actObsCaractReg.value = true;
                }
            }else{                
                
                const idCaract = nodoSeleccionado.value.treeKey.split("-")[1];

                const params = { idNombre: props.taxonActual.id,
                                 idCatNombre: idCaract,
                                 idRegion: nodoSeleccionado.value.id,
                                 idTipoDistAct: idTipoDistAnt.value,
                                 idTipoDistNue: nodoSeleccionado.value.tipDistribucion,
                                 observaciones: ObsCaracteristicas.value
                    }
                
                const resp = await axios.put(`/actualiza-Caract-Taxon-Reg`, params);
                console.log("Estos son los parametros: ", params);
                if(resp.status === 200){
                    mostrarNotificacion('Aviso', resp.data.message, 'success');
                    nodoSeleccionado.value.observaciones = ObsCaracteristicas.value;
                    idTipoDist.value = 0;
                    idTipoReg.value = null;
                    idTipoDist.value = 0;
                    idCaracteristica.value = 0;
                    idCatNombre.value = 0;
                    idTipoRegion.value = 0;
                    idRegion.value = 0;
                    arbolDeshabilitado.value = false;
                    nodoSeleccionado.value = null;
                    modoEdicion.value = false;
                    nodoPendiente.value = null;
                    actObsCaractReg.value = true;
                }
            }
        }else{
            if(idTipoDist.value <= 0)
            {
                mostrarNotificacion("Error",
                                    "Debe seleccionar el tipo de distribución del nuevo registro para poder guardar.",
                                    "error"
                );

                await nextTick();
                treeCaractRef.value?.setCurrentKey(
                    nodoPendiente.value.treeKey
                );

                return;
            }

            const params = { idNombre: idNombre.value,
                                    idCaract: idCatNombre.value,
                                    idTipoRegion: idTipoRegion.value,
                                    idRegion: idRegion.value,
                                    idTipoDistribucion: idTipoDist.value,
                                    observaciones: ObsCaracteristicas.value
                    };

            console.log("Estos son los parametros ", params);
            
            try{

                const response = await axios.post(`/alta-relTaxon-Caract-Reg`, params);

                if(response.status === 200)
                {  
                    mostrarNotificacion('Aviso', response.data.message, 'success');
                    idTipoDist.value = 0;
                    idTipoReg.value = null;
                    idTipoDist.value = 0;
                    idCaracteristica.value = 0;
                    idCatNombre.value = 0;
                    idTipoRegion.value = 0;
                    idRegion.value = 0;
                    arbolDeshabilitado.value = false;
                    nodoSeleccionado.value = null;
                    modoEdicion.value = false;
                    nodoPendiente.value = null;
                }
            }
            catch(error){
                        if (error.response?.status === 422) {
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
    }

    const cargaCaractAsocTaxon = async() =>{
        console.log("Entre a carga caracteristicas");
        const listCaract = await axios.get(`/cargaCaracTaxon/${props.taxonActual.id}`);

        if (listCaract.status === 200) {
            console.log("Esta es la respuesta de lista de caracteristicas", listCaract);
            CaracteristicasTaxon.value = listCaract.data;
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
        if (!row) {
            idTipoDist.value = 0;
            return;
        }

        idTipoDist.value = row.IdTipoDistribucion;

    }

    const cerrarTipDist = () =>{
        cargaTiposDistribucion();
        dialogFormVisibleTiposDist.value = false;
    }

    const abrirBiblioCaract = () =>{
        console.log("Voy a entrar a bibliografia para hacer los cambios");
        dialogFormVisibleBiblioCaract.value = true
    }

    onMounted(() => {
        window.addEventListener('keydown', manejarEscape);
    });

    onBeforeUnmount(() => {
        window.removeEventListener('keydown', manejarEscape);
    });

    //Funciones al montado del componente 
    onMounted( async () => {

        cargaTiposDistribucion();

        cargaRegiones();

        cargaCaracteristicas();

        cargaCaractAsocTaxon();
        
    })
</script>

<style scoped>

    .card-disabled {
        opacity: 0.6 !important;
        pointer-events: none !important;
        user-select: none;
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
