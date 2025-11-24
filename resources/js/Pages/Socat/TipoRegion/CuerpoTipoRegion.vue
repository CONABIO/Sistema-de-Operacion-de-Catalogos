<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import NuevoButton from "@/Components/Biotica/NuevoButton.vue";
import EditarButton from "@/Components/Biotica/EditarButton.vue";
import EliminarButton from "@/Components/Biotica/EliminarButton.vue";
import GuardarButton from "@/Components/Biotica/GuardarButton.vue";
import DialogGeneral from "@/Components/Biotica/DialogGeneral.vue";
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from "@/Components/Biotica/BotonAceptar.vue";
import BotonCancelar from "@/Components/Biotica/BotonCancelar.vue";
import { ref, computed, watch, onMounted, nextTick, h } from "vue";
import { ElTree, ElMessage, ElMessageBox, ElInput, ElRadioGroup, ElRadio, ElForm, ElFormItem } from "element-plus";
import { router, usePage } from "@inertiajs/vue3";
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';

const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);
const treeRef = ref(null);
const expandedNodeIds = ref(new Set());
const nodeIdToFocus = ref(null);
const localTreeData = ref([]);
const selectedNode = ref(null);
const esModalVisible = ref(false);
const formModalRef = ref(null);
const modalMode = ref("");
const opcionNivel = ref("mismo");
const nodoEnModal = ref(null);

const formModal = ref({
    Descripcion: "",
});

const props = defineProps({
    treeDataProp: { type: Array, required: true, default: () => [] },
    flatTreeDataProp: { type: Array, required: true, default: () => [] },
    isModal: {
        type: Boolean,
        default: false
    }
});

const handleNodeExpand = (data) => {
    expandedNodeIds.value.add(data.IdTipoRegion);
};

const handleNodeCollapse = (data) => {
    expandedNodeIds.value.delete(data.IdTipoRegion);
};

const findNodeInTree = (nodes, nodeIdToFind) => {
    if (!nodes || !Array.isArray(nodes)) return null;
    for (const node of nodes) {
        if (String(node.IdTipoRegion) === String(nodeIdToFind)) return node;
        if (node.children && node.children.length > 0) {
            const found = findNodeInTree(node.children, nodeIdToFind);
            if (found) return found;
        }
    }
    return null;
};

const deepCopy = (data) => JSON.parse(JSON.stringify(data));

watch(() => props.treeDataProp, (newVal) => {
    localTreeData.value = deepCopy(newVal);
}, { immediate: true, deep: true });

onMounted(() => {
    if (localTreeData.value.length > 0) {
        selectedNode.value = localTreeData.value[0];
        treeRef.value?.setCurrentKey(localTreeData.value[0].IdTipoRegion);
    }
});

const handleNodeSelected = (data) => {
    if (esModalVisible.value) {
        ElMessage.info("Hay una operación en curso. Por favor, guarde o cancele.");
        treeRef.value?.setCurrentKey(selectedNode.value?.IdTipoRegion);
        return;
    }
    selectedNode.value = data;
};

const modalTitle = computed(() => modalMode.value === "editar" ? "Modificar el tipo de región seleccionado" : "Ingresar un nuevo tipo de región");

const modalRules = {
    Descripcion: [{ required: true, message: "La descripción es un dato obligatorio.", trigger: "blur" }],
};

const abrirModalParaInsertar = () => {
    modalMode.value = "insertar";
    formModal.value = { Descripcion: "" };
    opcionNivel.value = selectedNode.value ? "mismo" : "raiz";
    esModalVisible.value = true;
    nextTick(() => formModalRef.value?.clearValidate());
};

const abrirModalParaEditar = () => {
    if (!selectedNode.value) {
        return ElMessage.warning("Por favor, seleccione un nodo para editar.");
    }
    modalMode.value = "editar";
    nodoEnModal.value = { ...selectedNode.value };
    formModal.value = { Descripcion: selectedNode.value.Descripcion };
    esModalVisible.value = true;
    nextTick(() => formModalRef.value?.clearValidate());
};

const cerrarModalOperacion = () => {
    esModalVisible.value = false;
    nodoEnModal.value = null;
};

const mostrarNotificacion = (titulo, mensaje, tipo, duracion = 5000) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = duracion;
    notificacionVisible.value = true;
};

const guardarDesdeModal = async () => {
    if (!formModalRef.value) return;
    const isValid = await formModalRef.value.validate();
    if (!isValid) return;

    const onSuccess = () => {
        cerrarModalOperacion();
        mostrarNotificacion("¡Éxito!", "La operación se completó correctamente.", "success");
    };
    const onError = (errors) => {
        mostrarNotificacion("Error", Object.values(errors).flat().join("\n"), "error");
    };

    if (modalMode.value === "editar") {
        const datosUpdate = {
            Descripcion: formModal.value.Descripcion.trim(),
            isModal: props.isModal
        };
        const nodeId = nodoEnModal.value.IdTipoRegion;
        router.put(`/tipos-region/${nodeId}`, datosUpdate, { preserveState: true, preserveScroll: true, onSuccess, onError });
    } else {
        const calculoNiveles = calcularNivelesParaNuevoNodo(selectedNode.value, opcionNivel.value, props.flatTreeDataProp);
        if (!calculoNiveles) return;
        const datosInsert = {
            Descripcion: formModal.value.Descripcion.trim(),
            ...calculoNiveles.niveles,
            isModal: props.isModal
        };
        router.post("/tipos-region", datosInsert, { preserveState: true, preserveScroll: true, onSuccess, onError });
    }
};

const handleEliminar = () => {
    if (!selectedNode.value) return ElMessage.warning("Por favor, seleccione un nodo para eliminar.");
    if (selectedNode.value.children && selectedNode.value.children.length > 0) {
        return mostrarNotificacion("Acción no permitida", "No se puede eliminar porque tiene regiones dependientes.", "error");
    }

    const nombre = selectedNode.value.Descripcion;
    const mensaje = `¿Está seguro de eliminar el tipo de región "${nombre}"? Esta acción no se puede revertir.`;

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
                h(BotonAceptar, { texto: "Sí, Eliminar", onClick: () => proceedWithDeletion(selectedNode.value.IdTipoRegion, nombre) }),
            ]),
        ]),
    }).catch(() => { });
};

const proceedWithDeletion = (nodeId, nombre) => {
    ElMessageBox.close();
    router.delete(`/tipos-region/${nodeId}`, {
        data: { isModal: props.isModal },
        preserveScroll: true,
        onSuccess: () => {
            mostrarNotificacion("¡Eliminación Exitosa!", `El elemento "${nombre}" ha sido eliminado.`, "success");
            selectedNode.value = null;
        },
        onError: (error) => mostrarNotificacion("Error al Eliminar", error.message || "Ocurrió un error.", "error"),
    });
};

const MAX_NIVELES = 5;
const calcularNivelesParaNuevoNodo = (nodoReferencia, opcion, todosLosNodos) => {
    const nuevosNiveles = {};
    for (let i = 1; i <= MAX_NIVELES; i++) nuevosNiveles[`Nivel${i}`] = 0;
    if (opcion === "raiz") {
        let max = 0;
        todosLosNodos.forEach(n => { if (n.Nivel1 > 0 && n.Nivel2 === 0) max = Math.max(max, n.Nivel1); });
        nuevosNiveles.Nivel1 = max + 1;
        return { niveles: nuevosNiveles };
    }
    if (!nodoReferencia) {
        mostrarNotificacion("Error", "Se necesita un nodo de referencia.", "error");
        return null;
    }
    if (opcion === "inferior") {
        let p = 0;
        for (let i = 1; i <= MAX_NIVELES; i++) {
            if (nodoReferencia[`Nivel${i}`] > 0) {
                nuevosNiveles[`Nivel${i}`] = nodoReferencia[`Nivel${i}`];
                p = i;
            } else break;
        }
        const s = p + 1;
        if (s > MAX_NIVELES) {
            mostrarNotificacion("Error", "Profundidad máxima de niveles excedida.", "error");
            return null;
        }
        let max = 0;
        todosLosNodos.forEach(n => {
            let esHijo = true;
            for (let i = 1; i <= p; i++) if (n[`Nivel${i}`] !== nuevosNiveles[`Nivel${i}`]) esHijo = false;
            if (esHijo) for (let i = s + 1; i <= MAX_NIVELES; i++) if (n[`Nivel${i}`] > 0) esHijo = false;
            if (esHijo) max = Math.max(max, n[`Nivel${s}`] || 0);
        });
        nuevosNiveles[`Nivel${s}`] = max + 1;
        return { niveles: nuevosNiveles };
    }
    if (opcion === 'mismo') {
        let p = 0;
        for (let i = 1; i <= MAX_NIVELES; i++) if (nodoReferencia[`Nivel${i}`] > 0) p = i; else break;
        if (p === 1) return calcularNivelesParaNuevoNodo(null, 'raiz', todosLosNodos);
        for (let i = 1; i < p; i++) nuevosNiveles[`Nivel${i}`] = nodoReferencia[`Nivel${i}`];
        let max = 0;
        todosLosNodos.forEach(n => {
            let esHermano = true;
            for (let i = 1; i < p; i++) if (n[`Nivel${i}`] !== nuevosNiveles[`Nivel${i}`]) esHermano = false;
            if (esHermano) for (let i = p + 1; i <= MAX_NIVELES; i++) if (n[`Nivel${i}`] > 0) esHermano = false;
            if (esHermano) max = Math.max(max, n[`Nivel${p}`] || 0);
        });
        nuevosNiveles[`Nivel${p}`] = max + 1;
        return { niveles: nuevosNiveles };
    }
    return null;
};

const isAccionDependienteDeNodoDeshabilitada = computed(() => !selectedNode.value || esModalVisible.value);



const handleNodeDoubleClick = (data) => {
    if (props.isModal) {
        // 'window.parent' es la ventana que contiene el iframe (indexRegion.vue)
        // 'postMessage' es la forma segura de enviar datos entre ventanas.
        window.parent.postMessage({
            type: 'tipoRegionSeleccionado',
            payload: {
                id: data.IdTipoRegion,
                descripcion: data.Descripcion,
            }
        }, '*');
    }
};
</script>

<template>
    <LayoutCuerpo :usar-app-layout="false" tituloPag="Autoridades Taxonómicas" tituloArea="Catálogo de tipos de región">

        <el-card class="box-card tree-card">
            <template #header>
                <div class="header-container">
                    <div class="left-header-content"></div>
                    <div class="right-header-content">
                        <div class="action-group">
                            <NuevoButton @crear="abrirModalParaInsertar" toolPosicion="bottom"
                                :disabled="esModalVisible" />
                            <EditarButton @editar="abrirModalParaEditar" toolPosicion="bottom"
                                :disabled="isAccionDependienteDeNodoDeshabilitada" />
                            <EliminarButton @eliminar="handleEliminar" toolPosicion="bottom"
                                :disabled="isAccionDependienteDeNodoDeshabilitada" />

                            <BotonSalir v-if="!isModal" />
                        </div>
                    </div>
                </div>
            </template>

            <el-tree v-if="localTreeData && localTreeData.length" ref="treeRef" :data="localTreeData"
                :props="{ children: 'children', label: 'Descripcion' }" node-key="IdTipoRegion"
                :current-node-key="selectedNode?.IdTipoRegion" :highlight-current="true" :expand-on-click-node="true"
                @node-expand="handleNodeExpand" @node-collapse="handleNodeCollapse" @node-click="handleNodeSelected"
                @node-dblclick="handleNodeDoubleClick" class="custom-element-tree">
                <template #default="{ node, data }">
                    <span :id="`tree-node-${data.IdTipoRegion}`" class="custom-tree-node-content">
                        <span>{{ node.label }}</span>
                    </span>
                </template>
            </el-tree>
            <div v-else class="no-data-message">
                No hay datos de tipos de región para mostrar.
            </div>
        </el-card>



        <Teleport to="body">
            <DialogGeneral v-model="esModalVisible" :bot-cerrar="true" :press-esc="true" @close="cerrarModalOperacion">
                <div class="dialog-header">
                    <h3>{{ modalTitle }}</h3>
                </div>
                <div class="dialog-body-container">
                    <el-form :model="formModal" ref="formModalRef" :rules="modalRules" label-position="top"
                        @submit.prevent="guardarDesdeModal">
                        <div class="form-actions">
                            <GuardarButton @click="guardarDesdeModal" />
                            <BotonSalir accion="cerrar" @salir="cerrarModalOperacion" />
                        </div>

                        <div v-if="modalMode === 'insertar' && selectedNode" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Posición:</label>
                            <el-radio-group v-model="opcionNivel">
                                <el-radio value="mismo">Mismo nivel</el-radio>
                                <el-radio value="inferior">Nivel inferior</el-radio>
                            </el-radio-group>
                        </div>

                        <el-form-item prop="Descripcion" label="Descripción del Tipo de Región:">
                            <el-input v-model="formModal.Descripcion" placeholder="Ingrese la descripción" clearable
                                maxlength="255" show-word-limit />
                        </el-form-item>

                    </el-form>
                </div>

            </DialogGeneral>

            <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
                @close="notificacionVisible = false" />
        </Teleport>

    </LayoutCuerpo>
</template>

<style>
.message-box-diseno-limpio .el-message-box__header {
    border-bottom: none;
}

.message-box-diseno-limpio .el-message-box__content {
    padding: 10px 20px 20px 20px;
}

.custom-message-content {
    display: flex;
    flex-direction: column;
}

.body-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.text-container p {
    margin: 0;
    line-height: 1.5;
    color: #606266;
}

.custom-warning-icon-container {
    flex-shrink: 0;
}

.custom-warning-circle {
    width: 30px;
    height: 30px;
    border-radius: 90%;
    background-color: #f56c6c;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    line-height: 1;
}

.footer-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 25px;
}

.custom-element-tree {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    font-size: 14px;
    line-height: 20px;
}

.custom-element-tree .el-tree-node__content {
    padding: 0px 4px;
    border-bottom: none;
    height: auto;
    margin-bottom: 0;
    border-radius: 4px;
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
    height: v-bind("isModal ? 'auto' : '726px'");
    min-height: v-bind("isModal ? '400px' : 'auto'");
    display: flex;
    flex-direction: column;
}

.modal-content-wrapper {
    padding: 1rem;
    height: 100%;
    overflow-y: auto;
    box-sizing: border-box;
}


:deep(.el-card__header) {
    padding: 16px 24px !important;
    border-bottom: 1px solid #e4e7ed !important;
    flex-shrink: 0;
}

:deep(.el-card__body) {
    padding: 0;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
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

:deep(.el-dialog__body) {
    padding: 0 !important;
}

.dialog-header {
    background-color: #f1f7ff;
    padding: 20px 24px;
    border-bottom: 1px solid #e4e7ed;
    text-align: left;
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
    gap: 10px;
}

:deep(.el-form-item) {
    margin-bottom: 22px;
}

:deep(.el-form-item__label) {
    padding-bottom: 4px !important;
    line-height: normal !important;
    font-size: 0.9em;
    color: #606266;
}

.custom-tree-node-content {
    display: flex;
    align-items: center;
    gap: 4px;
}
</style>