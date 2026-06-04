<template>
    <div>
        <el-card class="box-card">
            <div class="common-layout">
                <el-container style="height: 90vh;">
                    <el-header class="header">
                        <div class="header-content">
                            <h1 class="titulo">Asociación de catalogos</h1>
                        </div>
                    </el-header>
                    <el-main style="padding: 15px; background: #fff; overflow: hidden;">
                        <el-row justify="space-between" align="middle">

                            <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                {{ props.taxonAct.label }} 
                            </span>

                            <div style="display: flex; gap: 50px;">                                
                                <BotonSalir accion="cerrar" @salir="cerrarDialogo"
                                                style="flex-shrink: 0; min-width: max-content;"/>
                            </div>
                        </el-row>
                        <el-tabs type="card" v-model="tabInicial">
                            <el-tab-pane label="Características" name="taxon">
                                <!--Aqui debo de pasar los datos del arbol para mostrar la informacion-->
                                <div class="dual-panel-container">
                                    <el-card class="box-card tree-card" shadow="never">
                                        <el-tree v-if="localTreeData && localTreeData.length" ref="treeRef" :data="localTreeData"
                                            :props="{ children: 'children', label: 'Descripcion' }" node-key="IdCatNombre"
                                            :current-node-key="selectedNode?.IdCatNombre" :highlight-current="true" :expand-on-click-node="true"
                                            :default-expanded-keys="expandedKeysArray" @node-expand="handleNodeExpand" @node-collapse="handleNodeCollapse"
                                            @node-click="handleNodeSelected" class="custom-element-tree">
                                            <template #default="{ node, data }">
                                            <span :id="`tree-node-${data.IdCatNombre}`" class="custom-tree-node-content">
                                                <span>{{ node.label }}</span>
                                            </span>
                                            </template>
                                        </el-tree>
                                        <div v-else class="no-data-message">
                                            No hay datos de características para mostrar.
                                        </div>
                                    </el-card>
                                    <!-- Panel de botones intermedios -->
                                    <div style="position: sticky; 
                                                top: 20%; 
                                                left: 10px;
                                                transform: translateY(15%);
                                                display: flex;
                                                flex-direction: column;
                                                gap: 16px;">
                                        <el-tooltip effect="dark" content="Relaciona taxón" placement="top">
                                        <el-button @click="traspasaDatos" circle color="#8e44ad" :disabled="habTraspaso"
                                                    style="margin-left: 10px;">
                                            <el-icon>
                                            <iconoTraspaso />
                                            </el-icon>
                                        </el-button>
                                        </el-tooltip>
                                        <el-tooltip effect="dark" content="Cambio de relación" placement="right">
                                        <el-button @click="CambioBasSin" circle type="warning" 
                                                    style="margin-left: 10px;"
                                                    :disabled = "habCambioSinBas">
                                            <el-icon>
                                            <reemplazo />
                                            </el-icon>
                                        </el-button>
                                        </el-tooltip>
                                        <el-tooltip effect="dark" content="Traspaso de información" placement="right">
                                        <el-button @click="CambioBasSin" circle  
                                                    style="margin-left: 10px; background: rgb(145, 184, 88); border: none;"
                                                    :disabled = "habCambioSinBas">
                                            <img :src = "'/storage/images/TraspasoInformacion.png'" style="width: 25px; height: 28px">
                                        </el-button>
                                        </el-tooltip>
                                    </div>
                                    <el-card class="box-card tree-card" shadow="never">
                                        <el-tree v-if="localTreeData && localTreeData.length" ref="treeRef" :data="localTreeDataTaxon"
                                            :props="{ children: 'children', label: 'Descripcion' }" node-key="IdCatNombre"
                                            :current-node-key="selectedNode?.IdCatNombre" :highlight-current="true" :expand-on-click-node="true"
                                            :default-expanded-keys="expandedKeysArray" @node-expand="handleNodeExpand" @node-collapse="handleNodeCollapse"
                                            @node-click="handleNodeSelected" class="custom-element-tree">
                                            <template #default="{ node, data }">
                                            <span :id="`tree-node-${data.IdCatNombre}`" class="custom-tree-node-content">
                                                <span>{{ node.label }}</span>
                                            </span>
                                            </template>
                                        </el-tree>
                                        <div v-else class="no-data-message">
                                            No hay datos de características para mostrar.
                                        </div>
                                    </el-card>
                                </div>
                            </el-tab-pane>
                            <el-tab-pane label="Nombre(s) común(es)" name="taxon">

                            </el-tab-pane>
                            <el-tab-pane label="Regiones" name="taxon">

                            </el-tab-pane>
                        </el-tabs>
                    </el-main>                
                </el-container>
            </div>
        </el-card>
    </div>
</template> 
<script setup>
    import BotonSalir from '@/Components/Biotica/SalirButton.vue';
    import { onMounted, ref } from 'vue';

    const props = defineProps({
        taxonAct: {
            type: Object
        },
    });

    const localTreeData = ref([]);
    const localTreeDataTaxon = ref([]);
    
    const deepCopy = (data) => JSON.parse(JSON.stringify(data));

    const sortNodesAlphabetically = (nodes) => {
        if (!nodes || !Array.isArray(nodes) || nodes.length === 0) return;
        nodes.sort((a, b) =>
            (a.Descripcion || "").localeCompare(b.Descripcion || "", undefined, {
            sensitivity: "base",
            })
        );
        nodes.forEach((node) => {
            if (node.children && node.children.length)
            sortNodesAlphabetically(node.children);
        });
    };

    const emit = defineEmits(['cerrar']);

    const cerrarDialogo = () => {
        emit('cerrar');
    };
/*
    watch(
  () => props.treeDataProp,
  (newVal) => {
    const copiedData = deepCopy(newVal);
    sortNodesAlphabetically(copiedData);
    localTreeData.value = copiedData;

    let idToProcess = null;
    if (nodeIdToSelectAfterInsert.value) {
      idToProcess = String(nodeIdToSelectAfterInsert.value);
    } else if (nodeIdToFocus.value) {
      idToProcess = String(nodeIdToFocus.value);
    }

    if (idToProcess) {
      selectAndFocusNode(idToProcess);
    }
  },
  { immediate: true, deep: true }
);
*/

    onMounted( async () => {
        const response = await axios.get('/cargar-caracteristicas');

        if(response.status === 200)
        {
            const copiedData = deepCopy(response.data.treeDataProp);
            sortNodesAlphabetically(copiedData);
            localTreeData.value = copiedData;
        }
    });
</script>

<style>
    .custom-element-tree {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 14px;
        line-height: 20px;
    }

    .custom-element-tree .el-tree-node__content {
        height: auto !important;
        min-height: 10px !important;
        display: flex;
        align-items: center;
        padding: 2px 0;
        white-space: normal;
    }


    .custom-tree-node-content {
        flex: 1;
        min-width: 0;
        line-height: 1.4;
        padding-right: 15px;
        word-break: normal;
        overflow-wrap: anywhere;
    }

    .custom-element-tree .el-tree-node__content:hover {
        background-color: #f4f6f8;
    }

    .custom-element-tree .el-tree-node.is-current>.el-tree-node__content {
        background-color: #e4f5e1;
        font-weight: 500;
        color: #007bff;
    }

    .custom-element-tree .el-tree-node__expand-icon {
        font-size: 1.2em;
        color: #909399;
    }

    .custom-element-tree .el-tree-node__expand-icon:hover {
        color: #606266;
    }

    .tree-card>.el-card__body {
        overflow-y: auto;
        flex-grow: 1;
        padding: 10px;
        border: 1px solid #ebeef5;
        border-radius: 4px;
        margin: 0 24px 24px 24px;
    }
</style>

<style scoped>
    .tree-card {
        width: 100%;
        max-width: 1600px;
        max-height: 726px;
        display: flex;
        flex-direction: column;
    }
</style>
