<template>
    <div>
        <el-card class="box-card">
            <div class="common-layout">
                <el-container style="height: 72vh;">
                    <el-header class="header">
                        <div class="header-content">
                            <h1 class="titulo">Asociación taxón nombre comun</h1>
                        </div>
                    </el-header>
                    <el-main style="padding: 15px; background: #fff; overflow: hidden;">
                        Boton de traspaso
                        <div style="height: 650px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                            <el-splitter lazy>
                                <el-splitter-panel min="50">
                                    <TablaFiltrable 
                                        :columnas = "columnasDefinidasNomCom" 
                                        v-model:datos = "tablaNomComun"
                                        :opciones-filtro = "opcionesFiltroRegCaract"
                                        v-model:totalItems = "contRegNomCom"
                                        :itemsPerPage = 100                                        
                                        endpoint="/busca-nombre-comun" 
                                        :mostrarAcci = "false"
                                        :alturaTabla = 502
                                        :highlight-current-row = "true"
                                        :mostrarBiblio = "false"
                                        :mostrarNuevo = "false"
                                        :mostrarEditar = "false"
                                        :mostrarBorrar = "false"
                                        :mostrarSalir = "false">  
                                        <template #expand-column>
                                            <el-table-column type="expand">
                                                <template #default="{ row }">
                                                    <div class="expand-content-detail">
                                                        <p><strong>IdNomComun:</strong> {{ row.IdNomComun }}</p>
                                                        <p><strong>IdOriginal:</strong> {{ row.IdOriginal }}</p>
                                                        <p><strong>Catalogo:</strong> {{ row.Catalogo }}</p>
                                                        <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                                                        <p><strong>FechaModificacion:</strong> {{ row.FechaModificacion }}</p>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                        </template>                                              
                                    </TablaFiltrable>
                                </el-splitter-panel>
                                <el-splitter-panel min="50">
                                    <div style="height: 650px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                        <el-splitter layout="vertical">
                                            <el-splitter-panel>
                                                <el-card class="panel-card list-panel" shadow="never">
                                                    <template #header>
                                                        <div class="header-container">
                                                            <span class="details-header-title">Tipo de región</span>
                                                        </div>
                                                    </template>
                                                    <div class="demo-tree panel-nombre">
                                                        <!--el-tree
                                                            class="tree-full"
                                                            :data="tiposRegion"
                                                            :props="defaultProps"> 
                                                            <template #default="{ node, data }">                                                                                                                
                                                                <span>
                                                                    {{ data.Descripcion }}
                                                                    <template v-if="data.TipoDistribucion">
                                                                        ({{ data.TipoDistribucion.descripcion }})
                                                                    </template>
                                                                </span>
                                                            </template>
                                                        </el-tree-->
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
                            </el-splitter>
                        </div>
                    </el-main>
                </el-container>
            </div>
        </el-card>
    </div>
</template>

<script setup>
    import { ref, computed, watch, onMounted, h, onUnmounted, nextTick } from "vue";
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";

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

    //Variables declaradas para nombre comun
    const tablaNomComun = ref ([]);
    const contRegNomCom = ref(0);

    //Definición de funciones 
    //Funciones definidas para region 
    
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
                setTimeout(() => {
                    const el = document.getElementById('region-node-' + primerNodo.IdRegion);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
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

    const handleNodeSelected = (data) => {
        //if (esModalVisible.value) return;
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

    //Funciones definidas para nombre comun
    const columnasDefinidasNomCom = ref([
        { prop: 'NomComun', label: 'Nombre común', minWidth: '120', 
          sortable: true, filtrable: true, align: 'left' },
        { prop: 'Lengua', label: 'Lengua', minWidth: '150', 
          sortable: true, filtrable: true, align: 'left' },
        { prop: 'Observaciones', label: 'Observaciones', minWidth: '150', 
          sortable: true, filtrable: true, align: 'left' },
    ]);

    onMounted( async () => {
        const respNomCom = await axios.get('/cargaCatNomComun');

        if(respNomCom.status === 200)
        {
            tablaNomComun.value = respNomCom.data.data;
            contRegNomCom.value = respNomCom.data.total;
        }

        const respRegiones = await axios.get('/carga-regiones');

        if(respRegiones.status === 200)
        {
            //tiposRegionTreeData.value =  respRegiones.data.tiposDeRegionTree;
            tiposRegionTreeData.value = JSON.parse(JSON.stringify(respRegiones.data.tiposDeRegionTree));
            const existeSeleccion = selectedTipoRegionNode.value
                ? findNodeInTipoRegionTree(tiposRegionTreeData.value, selectedTipoRegionNode.value.IdTipoRegion)
                : null;
            if (tiposRegionTreeData.value.length > 0 && (!selectedTipoRegionNode.value || !existeSeleccion)) {
                seleccionarPrimeroPorDefault();
            }
            
            //console.log("Esta es la carga inicial de Regiones: ", respRegiones);
            //console.log("Esta es la carga inicial de Regiones: ", respRegiones.data.treeData);

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

        console.log("Esta es la carga inicial de Regiones: ", tiposRegionTreeData);
    })
</script>
<style scoped>
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
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
    }

    .tree-full {
        width: 100%;
        height: 100%;
        overflow: auto;
    }


</style>
