<script setup>
import { ref, computed, watch, onMounted, h, onUnmounted } from "vue";
import { ElTree, ElMessage, ElMessageBox, ElInput, ElRadioGroup, ElRadio, ElForm, ElFormItem, ElSelect, ElOption, ElButton } from "element-plus";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import NuevoButton from "@/Components/Biotica/NuevoButton.vue";
import EditarButton from "@/Components/Biotica/EditarButton.vue";
import EliminarButton from "@/Components/Biotica/EliminarButton.vue";
import GuardarButton from "@/Components/Biotica/GuardarButton.vue";
import DialogGeneral from "@/Components/Biotica/DialogGeneral.vue";
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from "@/Components/Biotica/BotonAceptar.vue";
import BotonCancelar from "@/Components/Biotica/BotonCancelar.vue";
import BotonSalir from '@/Components/Biotica/SalirButton.vue';
import IconoMundo from '@/Components/Biotica/IconoMundo.vue';

// --- PROPS ---
const props = defineProps({
    treeDataProp: { type: Array, required: true, default: () => [] },
    tiposDeRegionProp: { type: Array, required: true, default: () => [] },
    tiposDeRegionTreeProp: { type: Array, required: true, default: () => [] },
});


const tiposRegionTreeRef = ref(null);
const tiposRegionTreeData = ref([]);
const selectedTipoRegionNode = ref(null);
const treeRef = ref(null);
const localTreeData = ref([]);
const selectedNode = ref(null);
const filterText = ref('');
const listaTiposDeRegion = ref([]);
const esModalVisible = ref(false);
const formModalRef = ref(null);
const modalMode = ref("");
const opcionNivel = ref("mismo");
const nodoEnModal = ref(null);
const formModal = ref({ NombreRegion: "", IdTipoRegion: null, ClaveRegion: "", Abreviado: "" });
const esModalTipoRegionVisible = ref(false);
const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");

const isTipoRegionReadonly = computed(() => {
    // Solo es readonly si estamos insertando y se elige "mismo nivel"
    return modalMode.value === 'insertar' && opcionNivel.value === 'mismo';
});


const opcionesTipoRegionDisponibles = computed(() => {
    // Para modo edición, o si no hay un nodo de referencia, mostrar todo.
    if (modalMode.value === 'editar' || !selectedNode.value) {
        return listaTiposDeRegion.value;
    }

    // --- Lógica para modo Inserción ---

    if (opcionNivel.value === 'mismo') {
        // En "mismo nivel", el select está deshabilitado.
        // Nos aseguramos de que la lista contenga SOLO el tipo de región del nodo de referencia.
        // Esto garantiza que el select pueda mostrar el nombre correcto.
        const tipoRegionId = selectedNode.value.IdTipoRegion;
        return listaTiposDeRegion.value.filter(tipo => tipo.IdTipoRegion === tipoRegionId);
    }

    if (opcionNivel.value === 'inferior') {
        // En "nivel inferior", buscamos el "Tipo de Región" del nodo seleccionado en el árbol de tipos...
        const tipoRegionActual = findNodeInTipoRegionTree(tiposRegionTreeData.value, selectedNode.value.IdTipoRegion);
        // ...y devolvemos solo sus hijos directos.
        return tipoRegionActual?.children || [];
    }

    // Caso por defecto (ej. al insertar en la raíz)
    return listaTiposDeRegion.value;
});

watch(() => props.treeDataProp, (newVal) => { localTreeData.value = JSON.parse(JSON.stringify(newVal)); }, { immediate: true, deep: true });
watch(() => props.tiposDeRegionTreeProp, (newVal) => { tiposRegionTreeData.value = JSON.parse(JSON.stringify(newVal)); }, { immediate: true, deep: true });
watch(() => props.tiposDeRegionProp, (newVal) => { listaTiposDeRegion.value = newVal; }, { immediate: true, deep: true });
watch(filterText, (val) => {
    // Solo intentar filtrar si la referencia al árbol existe
    if (treeRef.value) {
        treeRef.value.filter(val);
    }
});

const onOpcionNivelChange = (newVal) => {
    if (newVal === 'mismo' && selectedNode.value) {
        formModal.value.IdTipoRegion = selectedNode.value.IdTipoRegion;
    } else {
        formModal.value.IdTipoRegion = null;
    }
};


// MODIFICAMOS la función de filtro para que sea más robusta
const filterNodeMethod = (value, data) => {
    if (!value) return true;
    // Nos aseguramos de que 'data.NombreRegion' exista antes de llamar a toLowerCase()
    return data.NombreRegion && data.NombreRegion.toLowerCase().includes(value.toLowerCase());
};


function findNodeById(nodes, id) {
    for (const node of nodes) {
        if (node.IdRegion === id) return node;
        if (node.children) {
            const found = findNodeById(node.children, id);
            if (found) return found;
        }
    }
    return null;
}

// Función para buscar un nodo en el árbol de TIPOS de región por su ID
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

// --- NUEVA PROPIEDAD COMPUTADA ---
// Determina si la opción "Nivel inferior" debe estar disponible
const puedeTenerNivelInferior = computed(() => {
    // Si no hay nodo seleccionado, no se puede determinar.
    if (!selectedNode.value) {
        return false;
    }
    // Buscamos el tipo de región del nodo actual en el árbol de tipos
    const tipoRegionActual = findNodeInTipoRegionTree(tiposRegionTreeData.value, selectedNode.value.IdTipoRegion);

    // La opción solo está disponible si el tipo de región actual tiene 'children' (hijos)
    return !!(tipoRegionActual && tipoRegionActual.children && tipoRegionActual.children.length > 0);
});


const getAllChildIds = (node) => {
    let ids = [node.IdTipoRegion];
    if (node.children?.length) {
        node.children.forEach(child => {
            ids = ids.concat(getAllChildIds(child));
        });
    }
    return ids;
};

const filteredRegionsTree = computed(() => {
    if (!selectedTipoRegionNode.value) {
        return [];
    }
    const allowedIds = getAllChildIds(selectedTipoRegionNode.value);
    const filterTree = (nodes) => {
        return nodes.reduce((acc, node) => {
            const filteredChildren = node.children?.length ? filterTree(node.children) : [];
            if (allowedIds.includes(node.IdTipoRegion) || filteredChildren.length > 0) {
                acc.push({ ...node, children: filteredChildren });
            }
            return acc;
        }, []);
    };

    return filterTree(JSON.parse(JSON.stringify(localTreeData.value)));
});


onMounted(() => {
    const handleMessageFromIframe = (event) => {
        if (event.data && event.data.type === 'tipoRegionSeleccionado') {
            const { id, descripcion } = event.data.payload;
            formModal.value.IdTipoRegion = id;
            esModalTipoRegionVisible.value = false;
            ElMessage.success(`Se ha seleccionado: "${descripcion}"`);
            router.reload({ only: ['tiposDeRegionProp'] });
        }
    };
    window.addEventListener('message', handleMessageFromIframe);
    onUnmounted(() => {
        window.removeEventListener('message', handleMessageFromIframe);
    });
});

const handleTipoRegionSelected = (data) => {
    if (selectedTipoRegionNode.value?.IdTipoRegion === data?.IdTipoRegion) {
        selectedTipoRegionNode.value = null;
        tiposRegionTreeRef.value?.setCurrentKey(null);
    } else {
        selectedTipoRegionNode.value = data;
    }
    selectedNode.value = null;
    treeRef.value?.setCurrentKey(null);
};

const handleNodeSelected = (data) => {
    if (esModalVisible.value) return;
    selectedNode.value = data;
};

const handleManageTiposRegion = () => { esModalTipoRegionVisible.value = true; };
const cerrarModalTipoRegion = () => { window.location.reload(); };

const modalTitle = computed(() => modalMode.value === "editar" ? "Modificar Región" : "Ingresar Nueva Región");
const modalRules = {
    NombreRegion: [{ required: true, message: "El nombre es obligatorio.", trigger: "blur" }],
    IdTipoRegion: [{ required: true, message: "El tipo de región es obligatorio.", trigger: "change" }],
};

const abrirModalParaInsertar = () => {
    modalMode.value = "insertar";
    formModal.value = { NombreRegion: "", IdTipoRegion: null, ClaveRegion: "", Abreviado: "" };
    opcionNivel.value = selectedNode.value ? "mismo" : "raiz";
    if (opcionNivel.value === 'mismo' && selectedNode.value) {
        formModal.value.IdTipoRegion = selectedNode.value.IdTipoRegion;
    }
    esModalVisible.value = true;
};

const abrirModalParaEditar = () => {
    if (!selectedNode.value) return ElMessage.warning("Seleccione una región para editar.");
    modalMode.value = "editar";
    nodoEnModal.value = { ...selectedNode.value };
    formModal.value = {
        NombreRegion: selectedNode.value.NombreRegion,
        IdTipoRegion: selectedNode.value.IdTipoRegion,
        ClaveRegion: selectedNode.value.ClaveRegion,
        Abreviado: selectedNode.value.Abreviado,
    };
    esModalVisible.value = true;
};

const cerrarModalOperacion = () => { esModalVisible.value = false; };
const mostrarNotificacion = (titulo, mensaje, tipo) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionVisible.value = true;
};

const guardarDesdeModal = async () => {
    if (!formModalRef.value) return;
    await formModalRef.value.validate();
    const onSuccess = () => {
        cerrarModalOperacion();
        mostrarNotificacion("¡Éxito!", "La operación se completó correctamente.", "success");
    };
    const onError = (errors) => {
        mostrarNotificacion("Error del Servidor", Object.values(errors).flat().join("\n"), "error");
    };

    if (modalMode.value === "editar") {
        router.put(`/regiones/${nodoEnModal.value.IdRegion}`, formModal.value, { preserveState: true, preserveScroll: true, onSuccess, onError });
    } else {
        const payload = { ...formModal.value, opcionNivel: opcionNivel.value, idNodoReferencia: selectedNode.value ? selectedNode.value.IdRegion : null };
        router.post("/regiones", payload, { preserveState: true, preserveScroll: true, onSuccess, onError });
    }
};

const handleEliminar = () => {
    if (!selectedNode.value) return ElMessage.warning("Por favor, seleccione una región para eliminar.");
    if (selectedNode.value.children?.length > 0) {
        return mostrarNotificacion("Acción no permitida", "No se puede eliminar porque tiene regiones dependientes.", "error");
    }

    const nombre = selectedNode.value.NombreRegion;
    const mensaje = `¿Está seguro de eliminar la región "${nombre}"? Esta acción no se puede revertir.`;

    ElMessageBox({
        title: "Confirmar eliminación",
        showConfirmButton: false, showCancelButton: false, customClass: "message-box-diseno-limpio",
        message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
                h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
                h('div', { class: 'text-container' }, [h('p', null, mensaje)])
            ]),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                h(BotonAceptar, { texto: "Sí, Eliminar", onClick: () => proceedWithDeletion(selectedNode.value.IdRegion, nombre) }),
            ]),
        ]),
    }).catch(() => { });
};

const proceedWithDeletion = (nodeId, nombre) => {
    ElMessageBox.close();
    router.delete(`/regiones/${nodeId}`, {
        preserveScroll: true,
        onSuccess: () => {
            mostrarNotificacion("¡Eliminación Exitosa!", `El elemento "${nombre}" ha sido eliminado.`, "success");
            selectedNode.value = null;
        },
        onError: (error) => mostrarNotificacion("Error al Eliminar", error.message || "Ocurrió un error.", "error"),
    });
};
</script>

<template>
    <AppLayout title="Regiones">
        <LayoutCuerpo :usar-app-layout="false" titulo-pag="Regiones" titulo-area="Catálogo de regiones geográficas">

            <div class="split-container">
                <el-card class="panel-card list-panel">
                    <template #header>
                        <div class="header-container">
                            <span class="details-header-title">Filtrar por tipo de región</span>
                            <el-button style="background-color: springgreen;" circle @click="handleManageTiposRegion">
                                <IconoMundo />
                            </el-button>
                        </div>
                    </template>

                    <el-tree v-if="tiposRegionTreeData.length" ref="tiposRegionTreeRef" :data="tiposRegionTreeData"
                        :props="{ children: 'children', label: 'Descripcion' }" node-key="IdTipoRegion"
                        :current-node-key="selectedTipoRegionNode?.IdTipoRegion" :highlight-current="true"
                        :expand-on-click-node="true" @node-click="handleTipoRegionSelected"
                        class="custom-element-tree" />
                    <div v-else class="no-data-message">
                        No hay tipos de región para mostrar.
                    </div>
                </el-card>

                <el-card class="panel-card details-panel">
                    <template #header>
                        <div class="header-container">
                            <div class="header-buscador">
                                <el-input v-model="filterText" placeholder="Buscar región..." clearable />
                            </div>
                            <span class="details-header-title"></span>
                            <div class="right-header-content">
                                <div class="action-group">
                                    <NuevoButton @crear="abrirModalParaInsertar" />
                                    <EditarButton @editar="abrirModalParaEditar" :disabled="!selectedNode" />
                                    <EliminarButton @eliminar="handleEliminar" :disabled="!selectedNode" />
                                    <BotonSalir />
                                </div>
                            </div>
                        </div>
                    </template>

                    <el-tree v-if="filteredRegionsTree.length" ref="treeRef" :data="filteredRegionsTree"
                        :props="{ children: 'children', label: 'NombreRegion' }" node-key="IdRegion"
                        :current-node-key="selectedNode?.IdRegion" :highlight-current="true"
                        :expand-on-click-node="true" @node-click="handleNodeSelected"
                        :filter-node-method="filterNodeMethod" class="custom-element-tree">
                    </el-tree>
                    <div v-else class="no-data-message">
                        <span v-if="!selectedTipoRegionNode">Seleccione un tipo de región de la izquierda para
                            comenzar.</span>
                        <span v-else>No hay regiones que coincidan con el filtro.</span>
                    </div>
                </el-card>

            </div>

        </LayoutCuerpo>

        <Teleport to="body">
            <DialogGeneral v-model="esModalVisible" :bot-cerrar="true" :press-esc="true" @close="cerrarModalOperacion">
                <div class="dialog-header">
                    <h3>{{ modalTitle }}</h3>
                </div>
                <div class="form-actions">
                    <GuardarButton @click="guardarDesdeModal" />
                    <BotonSalir accion="cerrar" @salir="cerrarModalOperacion" />
                </div>
                <div class="dialog-body-container">
                    <el-form :model="formModal" ref="formModalRef" :rules="modalRules" label-position="top"
                        @submit.prevent="guardarDesdeModal">
                        <div v-if="modalMode === 'insertar'" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Posición:</label>
                            <el-radio-group v-model="opcionNivel" @change="onOpcionNivelChange">
                                <el-radio v-if="selectedNode" value="mismo">Mismo nivel</el-radio>
                                <el-radio v-if="selectedNode" value="inferior" :disabled="!puedeTenerNivelInferior">
                                    Nivel inferior
                                </el-radio>
                            </el-radio-group>
                        </div>

                        <el-form-item prop="IdTipoRegion" label="Tipo de región:" class="form-item-con-boton">
                            <el-select v-model="formModal.IdTipoRegion" class="main-input"
                                placeholder="Seleccione un tipo" :disabled="isTipoRegionReadonly">
                                <el-option v-for="tipo in opcionesTipoRegionDisponibles" :key="tipo.IdTipoRegion"
                                    :label="tipo.Descripcion" :value="tipo.IdTipoRegion" />
                            </el-select>

                        </el-form-item>

                        <el-form-item prop="NombreRegion" label="Nombre de la región:"><el-input
                                v-model="formModal.NombreRegion" clearable /></el-form-item>


                        <el-form-item label="Abreviado:" prop="Abreviado"><el-input v-model="formModal.Abreviado"
                                clearable /></el-form-item>

                        <el-form-item label="Clave:" prop="ClaveRegion"><el-input v-model="formModal.ClaveRegion"
                                clearable /></el-form-item>
                    </el-form>
                </div>
            </DialogGeneral>

            <DialogGeneral v-model="esModalTipoRegionVisible" :bot-cerrar="true" :press-esc="true" width="80%"
                @close="cerrarModalTipoRegion">
                <div class="dialog-header">
                    <h3>Gestionar Tipos de Región</h3>
                </div>
                <div class="dialog-body-iframe-container"><iframe :src="route('tipos-region.index', { modal: true })"
                        class="iframe-full" frameborder="0"></iframe></div>
            </DialogGeneral>

            <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                :mensaje="notificacionMensaje" :tipo="notificacionTipo" @close="notificacionVisible = false" />
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.split-container {
    display: flex;
    gap: 20px;
    height: 726px;
    width: 100%;
}

.panel-card {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.list-panel {
    flex: 1;
    min-width: 300px;
}

.details-panel {
    flex: 2;
}

:deep(.el-card__header) {
    padding: 16px 24px !important;
    border-bottom: 1px solid #e4e7ed !important;
    flex-shrink: 0;
}

:deep(.el-card__body) {
    padding: 10px;
    flex-grow: 1;
    overflow-y: auto;
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.action-group {
    display: flex;
    gap: 10px;
}

.details-header-title {
    font-weight: 600;
    color: #303133;
}

.no-data-message {
    text-align: center;
    color: #777;
    padding: 20px;
    font-style: italic;
    margin: auto;
}

.mb-4 {
    margin-bottom: 1rem;
}

/* Estilos para los modales */
:deep(.el-dialog__body) {
    padding: 0 !important;
}

.dialog-header {
    background-color: #f1f7ff;
    padding: 20px 24px;
    border-bottom: 1px solid #e4e7ed;
}

.dialog-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #303133;
}

.dialog-body-container {
    padding: 24px 30px 30px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 25px;
    padding: 0 24px 20px;
    margin-top: 20px;
}

:deep(.form-item-con-boton .el-form-item__content) {
    display: flex;
    align-items: center;
    gap: 18px;
}

.main-input {
    flex-grow: 1;
}

.dialog-body-iframe-container {
    height: 600px;
}

.iframe-full {
    width: 100%;
    height: 100%;
    border: none;
}
</style>