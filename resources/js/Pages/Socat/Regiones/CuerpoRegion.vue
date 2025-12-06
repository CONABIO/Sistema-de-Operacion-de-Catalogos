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
import CuerpoTipoRegion from '@/Pages/Socat/TipoRegion/CuerpoTipoRegion.vue';
import { Plus, Download } from '@element-plus/icons-vue';

const props = defineProps({
    treeDataProp: { type: Array, required: true, default: () => [] },
    tiposDeRegionProp: { type: Array, required: true, default: () => [] },
    tiposDeRegionTreeProp: { type: Array, required: true, default: () => [] },
});


const botonNuevoDeshabilitado = computed(() => {
    if (!selectedTipoRegionNode.value) return true;
    if (!filteredRegionsTree.value || filteredRegionsTree.value.length === 0) return true;
    if (filterText.value && filterText.value.trim() !== '') {
        const textoBusqueda = filterText.value.toLowerCase();
        const existeCoincidencia = (nodes) => {
            return nodes.some(node => {
                if (node.NombreRegion && node.NombreRegion.toLowerCase().includes(textoBusqueda)) {
                    return true;
                }
                if (node.children && node.children.length > 0) {
                    return existeCoincidencia(node.children);
                }
                return false;
            });
        };
        if (!existeCoincidencia(filteredRegionsTree.value)) {
            return true;
        }
    }
    return false;
});


const botonEditarDeshabilitado = computed(() => {
    if (!selectedTipoRegionNode.value) return true;
    if (!filteredRegionsTree.value || filteredRegionsTree.value.length === 0) return true;
    if (filterText.value && filterText.value.trim() !== '') {
        const textoBusqueda = filterText.value.toLowerCase();
        const existeCoincidencia = (nodes) => {
            return nodes.some(node => {
                if (node.NombreRegion && node.NombreRegion.toLowerCase().includes(textoBusqueda)) {
                    return true;
                }
                if (node.children && node.children.length > 0) {
                    return existeCoincidencia(node.children);
                }
                return false;
            });
        };
        if (!existeCoincidencia(filteredRegionsTree.value)) {
            return true;
        }
    }
    return false;
});


const botonEliminarDeshabilitado = computed(() => {
    if (!selectedTipoRegionNode.value) return true;
    if (!filteredRegionsTree.value || filteredRegionsTree.value.length === 0) return true;
    if (filterText.value && filterText.value.trim() !== '') {
        const textoBusqueda = filterText.value.toLowerCase();
        const existeCoincidencia = (nodes) => {
            return nodes.some(node => {
                if (node.NombreRegion && node.NombreRegion.toLowerCase().includes(textoBusqueda)) {
                    return true;
                }
                if (node.children && node.children.length > 0) {
                    return existeCoincidencia(node.children);
                }
                return false;
            });
        };
        if (!existeCoincidencia(filteredRegionsTree.value)) {
            return true;
        }
    }
    return false;
});

/* const botonNuevoDeshabilitado = computed(() => {

    if (filterText.value && filterText.value.trim() !== '') {
        const textoBusqueda = filterText.value.toLowerCase();
        const existeCoincidencia = (nodes) => {
            return nodes.some(node => {
                if (node.NombreRegion && node.NombreRegion.toLowerCase().includes(textoBusqueda)) return true;
                if (node.children && node.children.length > 0) return existeCoincidencia(node.children);
                return false;
            });
        };
        if (!existeCoincidencia(filteredRegionsTree.value)) {
            return true;
        }
    }

    return false; 
}); */


const intentarAbrirModalInsertar = () => {
    if (!selectedTipoRegionNode.value) {
        mostrarNotificacion(
            "Aviso",
            "Debe seleccionar un Tipo de Región del panel izquierdo antes de continuar.",
            "warning"
        );
        return;
    }
    if (!selectedNode.value) {
        mostrarNotificacion(
            "Acción no permitida",
            "Para agregar una nueva región, debe seleccionar una región existente del panel derecho.",
            "warning"
        );
        return;
    }
    abrirModalParaInsertar();
};

const intentarAbrirModalEditar = () => {
    if (!selectedTipoRegionNode.value) {
        mostrarNotificacion(
            "Aviso",
            "Debe seleccionar un Tipo de Región del panel izquierdo antes de continuar.",
            "warning"
        );
        return;
    }
    if (!selectedNode.value) {
        mostrarNotificacion(
            "Acción no permitida",
            "Para editar una nueva región, debe seleccionar una región existente del panel derecho.",
            "warning"
        );
        return;
    }
    abrirModalParaEditar();
};


const intentarAbrirModalEliminar = () => {
    if (!selectedTipoRegionNode.value) {
        mostrarNotificacion(
            "Aviso",
            "Debe seleccionar un Tipo de Región del panel izquierdo antes de continuar.",
            "warning"
        );
        return;
    }
    if (!selectedNode.value) {
        mostrarNotificacion(
            "Acción no permitida",
            "Para eliminar una nueva región, debe seleccionar una región existente del panel derecho.",
            "warning"
        );
        return;
    }
    handleEliminar();
};



const treeKey = ref(0);
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
    return modalMode.value === 'editar';
});


const opcionesTipoRegionDisponibles = computed(() => {
    if (modalMode.value === 'editar' || !selectedNode.value) {
        return listaTiposDeRegion.value;
    }
    if (opcionNivel.value === 'mismo') {
        const tipoRegionId = selectedNode.value.IdTipoRegion;
        return listaTiposDeRegion.value.filter(tipo => tipo.IdTipoRegion === tipoRegionId);
    }
    if (opcionNivel.value === 'inferior') {
        const tipoRegionActual = findNodeInTipoRegionTree(tiposRegionTreeData.value, selectedNode.value.IdTipoRegion);
        return tipoRegionActual?.children || [];
    }
    return listaTiposDeRegion.value;
});

watch(() => props.treeDataProp, (newVal) => { localTreeData.value = JSON.parse(JSON.stringify(newVal)); treeKey.value++; }, { immediate: true, deep: true });
watch(() => props.tiposDeRegionTreeProp, (newVal) => { tiposRegionTreeData.value = JSON.parse(JSON.stringify(newVal)); }, { immediate: true, deep: true });
watch(() => props.tiposDeRegionProp, (newVal) => { listaTiposDeRegion.value = newVal; }, { immediate: true, deep: true });
watch(filterText, (val) => {
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


const filterNodeMethod = (value, data) => {
    if (!value) return true;
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

function getAscendantId(nodeId, tree) {
    let ascendantId = null;
    function search(nodes, parentId = null) {
        for (const node of nodes) {
            if (node.IdRegion === nodeId) {
                ascendantId = parentId;
                return true;
            }
            if (node.children && node.children.length > 0) {
                if (search(node.children, node.IdRegion)) {
                    return true;
                }
            }
        }
        return false;
    }
    search(tree);
    return ascendantId;
}


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

const puedeTenerNivelInferior = computed(() => {
    if (!selectedNode.value) {
        return false;
    }
    const tipoRegionActual = findNodeInTipoRegionTree(tiposRegionTreeData.value, selectedNode.value.IdTipoRegion);
    return !!(tipoRegionActual && tipoRegionActual.children && tipoRegionActual.children.length > 0);
});

const filteredRegionsTree = computed(() => {
    if (!selectedTipoRegionNode.value) {
        return [];
    }

    const targetTypeId = selectedTipoRegionNode.value.IdTipoRegion;

    const filterAndPruneTree = (nodes) => {
        return nodes.reduce((accumulator, node) => {
            if (node.IdTipoRegion == targetTypeId) {
                accumulator.push({ ...node, children: [] });
                return accumulator;
            }
            if (node.children && node.children.length > 0) {
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
    const isNameTakenBySibling = (parentId, regionName, currentRegionId = null) => {
        let siblings = [];
        if (parentId === null) {
            siblings = localTreeData.value.filter(node =>
                node.IdRegionAsc === null || node.IdRegionAsc === undefined
            );
        } else {
            const parentNode = findNodeById(localTreeData.value, parentId);
            if (parentNode && parentNode.children) {
                siblings = parentNode.children;
            }
        }
        return siblings.some(node =>
            node.NombreRegion.toLowerCase() === regionName.toLowerCase() &&
            (currentRegionId === null || node.IdRegion !== currentRegionId)
        );
    };

    let parentIdForValidation = null;

    if (modalMode.value === "insertar") {
        if (opcionNivel.value === 'mismo' && selectedNode.value) {
            parentIdForValidation = getAscendantId(selectedNode.value.IdRegion, localTreeData.value);
        } else if (opcionNivel.value === 'inferior' && selectedNode.value) {
            parentIdForValidation = selectedNode.value.IdRegion;
        } else {
            parentIdForValidation = null;
        }
    } else if (modalMode.value === "editar" && nodoEnModal.value) {
        parentIdForValidation = getAscendantId(nodoEnModal.value.IdRegion, localTreeData.value);
    }

    if (isNameTakenBySibling(parentIdForValidation, formModal.value.NombreRegion, modalMode.value === 'editar' ? nodoEnModal.value.IdRegion : null)) {
        mostrarNotificacion("Error de Validación", "Ya existe una región con el mismo nombre en este nivel.", "error");
        return;
    }

    const onSuccess = () => {
        cerrarModalOperacion();
        mostrarNotificacion("¡Éxito!", "La operación se completó correctamente.", "success");

    };

    const onError = (errors) => {
        mostrarNotificacion("Error del Servidor", Object.values(errors).flat().join("\n"), "error");
    };

    if (modalMode.value === "editar") {
        router.put(`/regiones/${nodoEnModal.value.IdRegion}`, formModal.value, {
            preserveState: true,
            preserveScroll: true,
            onSuccess,
            onError
        });
    } else {
        const payload = {
            ...formModal.value,
            opcionNivel: opcionNivel.value,
            idNodoReferencia: selectedNode.value ? selectedNode.value.IdRegion : null
        };
        router.post("/regiones", payload, {
            preserveState: true,
            preserveScroll: true,
            onSuccess,
            onError
        });
    }
};

const handleEliminar = () => {
    if (!selectedNode.value) return ElMessage.warning("Por favor, seleccione una región para eliminar.");
    if (selectedNode.value.children?.length > 0) {
        return mostrarNotificacion("Acción no permitida", "No se puede borrar la región por qué tiene regiones asociadas.", "error");
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
            router.reload({ only: ['treeDataProp'] });
        },
        onError: (error) => mostrarNotificacion("Error", error.message || "Ocurrió un error.", "error"),
    });
};
</script>

<template>
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

                <el-tree v-if="tiposRegionTreeData.length" ref="tiposRegionTreeRef" :key="treeKey"
                    :data="tiposRegionTreeData" :props="{ children: 'children', label: 'Descripcion' }"
                    node-key="IdTipoRegion" :current-node-key="selectedTipoRegionNode?.IdTipoRegion"
                    :highlight-current="true" :expand-on-click-node="true" @node-click="handleTipoRegionSelected"
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
                                <el-button type="primary" circle @click="intentarAbrirModalInsertar"
                                    :disabled="botonNuevoDeshabilitado" title="Nuevo">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-box-arrow-in-down" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                            d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1h-2z" />
                                        <path fill-rule="evenodd"
                                            d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                                    </svg>
                                </el-button>

                                <el-button type="success" circle @click="intentarAbrirModalEditar"
                                    :disabled="botonEditarDeshabilitado" title="Nuevo">
                                    <el-icon>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                                            <path fill="currentColor"
                                                d="m199.04 672.64 193.984 112 224-387.968-193.92-112-224 388.032zm-23.872 60.16 32.896 148.288 144.896-45.696zM455.04 229.248l193.92 112 56.704-98.112-193.984-112-56.64 98.112zM104.32 708.8l384-665.024 304.768 175.936L409.152 884.8h.064l-248.448 78.336zm384 254.272v-64h448v64h-448z">
                                            </path>
                                        </svg>
                                    </el-icon>
                                </el-button>

                                <el-button type="danger" circle @click="intentarAbrirModalEliminar"
                                    :disabled="botonEliminarDeshabilitado" title="Nuevo">
                                    <el-icon>
                                        <el-icon>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                                                <path fill="currentColor"
                                                    d="M160 256H96a32 32 0 0 1 0-64h256V95.936a32 32 0 0 1 32-32h256a32 32 0 0 1 32 32V192h256a32 32 0 1 1 0 64h-64v672a32 32 0 0 1-32 32H192a32 32 0 0 1-32-32zm448-64v-64H416v64zM224 896h576V256H224zm192-128a32 32 0 0 1-32-32V416a32 32 0 0 1 64 0v320a32 32 0 0 1-32 32m192 0a32 32 0 0 1-32-32V416a32 32 0 0 1 64 0v320a32 32 0 0 1-32 32">
                                                </path>
                                            </svg>
                                        </el-icon>
                                    </el-icon>
                                </el-button>

                                <BotonSalir />
                            </div>
                        </div>
                    </div>
                </template>

                <el-tree v-if="filteredRegionsTree.length" ref="treeRef" :data="filteredRegionsTree"
                    :props="{ children: 'children', label: 'NombreRegion' }" node-key="IdRegion"
                    :current-node-key="selectedNode?.IdRegion" :highlight-current="true" :expand-on-click-node="true"
                    @node-click="handleNodeSelected" :filter-node-method="filterNodeMethod" class="custom-element-tree">
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
            <div class="dialog-body-container">
                <div class="form-actions">
                    <GuardarButton @click="guardarDesdeModal" />
                    <BotonSalir accion="cerrar" @salir="cerrarModalOperacion" />
                </div>
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
                        <el-select v-model="formModal.IdTipoRegion" class="main-input" placeholder="Seleccione un tipo"
                            :disabled="isTipoRegionReadonly">
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

        <DialogGeneral v-model="esModalTipoRegionVisible" :bot-cerrar="true" :press-esc="true" width="90%"
            @close="cerrarModalTipoRegion">

            <div class="dialog-body-container" style="padding: 10px; height: 800px; overflow: hidden;">
                <CuerpoTipoRegion :treeDataProp="tiposDeRegionTreeProp" :flatTreeDataProp="tiposDeRegionProp"
                    :isModal="true" @seleccionar-tipo="handleTipoRegionSeleccionadoComponente"
                    @cerrar-modal="esModalTipoRegionVisible = false" />
            </div>
        </DialogGeneral>

        <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
            :mensaje="notificacionMensaje" :tipo="notificacionTipo" @close="notificacionVisible = false" />
    </Teleport>
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

:deep(.el-dialog__body) {
    padding: 0 !important;
}

.dialog-header {
    background-color: #f5f5f5;
    padding: 20px 24px;
    border-bottom: 1px solid #e4e7ed;
    text-align: left;
    border-radius: 10px;
    margin-bottom: 10px;
}

.dialog-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #303133;
}

.dialog-body-container {
    background-color: #f3f3f3;
    padding: 20px 24px;
    border: 3px;
    text-align: left;
    border-radius: 10px;
    background-color: #ffffff;
    padding: 20px 24px;
    text-align: left;
    position: relative;
    z-index: 10;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.08);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 25px;

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