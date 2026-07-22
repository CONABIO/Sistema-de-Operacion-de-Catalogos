<script setup>
import { ref, computed, watch, onMounted, h, onUnmounted, nextTick } from "vue";
import { ElTree, ElMessage, ElMessageBox, ElInput, ElRadioGroup, ElRadio, ElForm, ElFormItem, ElSelect, ElOption, ElButton } from "element-plus";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import TipoBusqueda from '@/Components/Biotica/TipoBusqueda.vue';
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

const tipoBusqueda = ref('inicia');

const nodoDuplicadoParaSeleccionar = ref(null);

const handleCerrarNotificacion = async () => {
    notificacionVisible.value = false;

    if (nodoDuplicadoParaSeleccionar.value) {
        const idTarget = nodoDuplicadoParaSeleccionar.value.IdRegion;
        const tipoTargetId = nodoDuplicadoParaSeleccionar.value.IdTipoRegion;
        nodoDuplicadoParaSeleccionar.value = null;
        if (selectedTipoRegionNode.value?.IdTipoRegion !== tipoTargetId) {
            const nodoTipo = findNodeInTipoRegionTree(tiposRegionTreeData.value, tipoTargetId);
            if (nodoTipo) seleccionarTipoRegionBase(nodoTipo);
        }
        filterText.value = '';
        treeKey.value++; 
        await nextTick();
        await new Promise(resolve => setTimeout(resolve, 200));
        const tree = treeRef.value;
        if (!tree) return;
        const nodeInTree = tree.getNode(idTarget);
        if (nodeInTree) {
            let p = nodeInTree.parent;
            while (p && p.level > 0) {
                p.expanded = true;
                p = p.parent;
            }
            selectedNode.value = nodeInTree.data;
            tree.setCurrentKey(idTarget);
            setTimeout(() => {
                const el = document.getElementById('region-node-' + idTarget);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const content = el.closest('.el-tree-node__content');
                    if (content) {
                        content.style.backgroundColor = "#ddf6dd";
                        content.style.fontWeight = "bold";
                        setTimeout(() => { content.style.backgroundColor = ""; }, 3000);
                    }
                }
            }, 300);
        }
    }
};

watch(tipoBusqueda, () => {
    if (treeRef.value) {
        treeRef.value.filter(filtroEjecutado.value);
    }
});

const activePathIds = ref([]);
const expandedTipoRegionKeys = ref([]);
let ultimoEventoExpandTime = 0;

const filtroEjecutado = ref('');

const aplicarFiltro = () => {
    filtroEjecutado.value = filterText.value;
    if (treeRef.value) {
        treeRef.value.filter(filtroEjecutado.value);
    }
};

const esRegionProtegida = (nombre) => {
    if (!nombre) return false;
    const n = nombre.trim().toUpperCase();
    return n === 'MÉXICO' || n === 'ND' || n === 'MEXICO/ND/ND';
};

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

watch(activePathIds, (newPath) => {
    const padres = newPath.slice(0, -1);
    padres.forEach(id => {
        if (!expandedTipoRegionKeys.value.includes(id)) {
            expandedTipoRegionKeys.value.push(id);
        }
    });
}, { immediate: true });


const seleccionarPrimeroPorDefault = () => {
    if (tiposRegionTreeData.value && tiposRegionTreeData.value.length > 0) {
        const primerNodo = tiposRegionTreeData.value[0];
        seleccionarTipoRegionBase(primerNodo);
    }
};

const getTipoRegionNodeClass = (data) => {
    let classes = [];
    if (activePathIds.value.includes(data.IdTipoRegion)) {
        classes.push('fila-activa-completa');
    }
    return classes.join(' ');
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

const props = defineProps({
    treeDataProp: { type: Array, required: true, default: () => [] },
    tiposDeRegionProp: { type: Array, required: true, default: () => [] },
    tiposDeRegionTreeProp: { type: Array, required: true, default: () => [] },
    modal: { type:Boolean, required:false, default:false }
});

/*Juan carlos Mora 11/06/2026
Se agregan las funciones para que al presionar el boton de salir cierre 
    el modal y no salga al menu principal*/

const emit = defineEmits(['cerrar']);

const cerrarDialogo = () => {
    emit('cerrar');
} 

/*Juan Carlos Mora Morquecho */

const botonNuevoDeshabilitado = computed(() => {
    if (!selectedTipoRegionNode.value) return true;
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
    if (!selectedTipoRegionNode.value || !selectedNode.value) return true;
    if (esRegionProtegida(selectedNode.value.NombreRegion)) return true;
    if (!filteredRegionsTree.value || filteredRegionsTree.value.length === 0) return true;
    if (filterText.value && filterText.value.trim() !== '') {
        const textoBusqueda = filterText.value.toLowerCase();
        const existeCoincidencia = (nodes) => {
            return nodes.some(node => {
                if (node.NombreRegion && node.NombreRegion.toLowerCase().includes(textoBusqueda)) return true;
                if (node.children && node.children.length > 0) return existeCoincidencia(node.children);
                return false;
            });
        };
        if (!existeCoincidencia(filteredRegionsTree.value)) return true;
    }
    return false;
});


const botonEliminarDeshabilitado = computed(() => {
    if (!selectedTipoRegionNode.value || !selectedNode.value) return true;
    if (esRegionProtegida(selectedNode.value.NombreRegion)) return true;
    if (!filteredRegionsTree.value || filteredRegionsTree.value.length === 0) return true;
    if (filterText.value && filterText.value.trim() !== '') {
        const textoBusqueda = filterText.value.toLowerCase();
        const existeCoincidencia = (nodes) => {
            return nodes.some(node => {
                if (node.NombreRegion && node.NombreRegion.toLowerCase().includes(textoBusqueda)) return true;
                if (node.children && node.children.length > 0) return existeCoincidencia(node.children);
                return false;
            });
        };
        if (!existeCoincidencia(filteredRegionsTree.value)) return true;
    }
    return false;
});


const buscadorDeshabilitado = computed(() => {
    if (!selectedTipoRegionNode.value) return true;
    const esNivelRaiz = selectedTipoRegionNode.value.Descripcion.toUpperCase() === 'PAÍS';
    if (!esNivelRaiz && !selectedNode.value) {
        return true;
    }
    return false;
});


const intentarAbrirModalInsertar = () => {
    if (!selectedTipoRegionNode.value) {
        mostrarNotificacion(
            "Aviso",
            "Debe seleccionar un Tipo de Región del panel izquierdo antes de continuar.",
            "warning"
        );
        return;
    }
    abrirModalParaInsertar();
};

const intentarAbrirModalEditar = () => {
    if (!selectedTipoRegionNode.value) {
        mostrarNotificacion("Aviso", "Debe seleccionar un Tipo de Región.", "warning");
        return;
    }
    if (!selectedNode.value) {
        mostrarNotificacion("Aviso", "Seleccione una región.", "warning");
        return;
    }
    if (esRegionProtegida(selectedNode.value.NombreRegion)) {
        mostrarNotificacion("Aviso", "Esta región es del sistema y no se puede modificar.", "warning");
        return;
    }
    abrirModalParaEditar();
};

const intentarAbrirModalEliminar = () => {
    if (!selectedTipoRegionNode.value) {
        mostrarNotificacion("Aviso", "Debe seleccionar un Tipo de Región.", "warning");
        return;
    }
    if (!selectedNode.value) {
        mostrarNotificacion("Aviso", "Seleccione una región.", "warning");
        return;
    }
    if (esRegionProtegida(selectedNode.value.NombreRegion)) {
        mostrarNotificacion("Aviso", "Esta región es del sistema y no se puede eliminar.", "warning");
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
const listaTiposDeRegion = ref([]);
const esModalVisible = ref(false);
const formModalRef = ref(null);
const nombreRegionInputRef = ref(null);
const modalMode = ref("");
const opcionNivel = ref("mismo");
const nodoEnModal = ref(null);
const formModal = ref({
    NombreRegion: "",
    IdTipoRegion: null,
    ClaveRegion: "",
    Abreviado: "",
    IdRegionAsc: 0
});
const esModalTipoRegionVisible = ref(false);
const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");

const isTipoRegionReadonly = computed(() => {
    return modalMode.value === 'editar';
});


const opcionesTipoRegionDisponibles = computed(() => {
    if (modalMode.value === 'editar') {
        return listaTiposDeRegion.value;
    }
    if (activePathIds.value.length === 0) return [];

    if (!selectedNode.value || opcionNivel.value === 'raiz') {
        const idRaizCamino = activePathIds.value[0];
        return listaTiposDeRegion.value.filter(tipo => tipo.IdTipoRegion === idRaizCamino);
    }

    if (opcionNivel.value === 'mismo') {
        const tipoRegionId = selectedNode.value.IdTipoRegion;
        return listaTiposDeRegion.value.filter(tipo => tipo.IdTipoRegion === tipoRegionId);
    }

    if (opcionNivel.value === 'inferior') {
        const tipoRegionActual = findNodeInTipoRegionTree(tiposRegionTreeData.value, selectedNode.value.IdTipoRegion);
        const hijosPosibles = tipoRegionActual?.children || [];
        const hijosFiltrados = hijosPosibles.filter(h => {
            return h.Descripcion.toUpperCase() === 'LOCALIDAD' || activePathIds.value.includes(h.IdTipoRegion);
        });
        return hijosFiltrados.length > 0 ? hijosFiltrados : (hijosPosibles.length > 0 ? [hijosPosibles[0]] : []);
    }
    return [];
});


const filterText = ref('');

watch(filterText, (nuevoValor) => {
    if (nuevoValor) {
        filterText.value = nuevoValor.toUpperCase();
    }
});

watch(() => props.treeDataProp, (newVal) => {
    localTreeData.value = JSON.parse(JSON.stringify(newVal));
    treeKey.value++;
    if (selectedTipoRegionNode.value) {
        nextTick(() => {
            if (filteredRegionsTree.value && filteredRegionsTree.value.length > 0 && !selectedNode.value) {
                handleNodeSelected(filteredRegionsTree.value[0]);
            }
        });
    }
}, { immediate: true, deep: true });


watch(() => props.tiposDeRegionTreeProp, (newVal) => {
    tiposRegionTreeData.value = JSON.parse(JSON.stringify(newVal));
    const existeSeleccion = selectedTipoRegionNode.value
        ? findNodeInTipoRegionTree(tiposRegionTreeData.value, selectedTipoRegionNode.value.IdTipoRegion)
        : null;
    if (tiposRegionTreeData.value.length > 0 && (!selectedTipoRegionNode.value || !existeSeleccion)) {
        seleccionarPrimeroPorDefault();
    }
}, { immediate: true, deep: true });

watch(() => props.tiposDeRegionProp, (newVal) => { listaTiposDeRegion.value = newVal; }, { immediate: true, deep: true });


const onOpcionNivelChange = (newVal) => {
    if (!selectedNode.value || newVal === 'raiz') {
        formModal.value.IdTipoRegion = activePathIds.value[0] || null;
        return;
    }
    if (newVal === 'mismo') {
        formModal.value.IdTipoRegion = selectedNode.value.IdTipoRegion;
    }
    else if (newVal === 'inferior') {
        const tipoRegionActual = findNodeInTipoRegionTree(tiposRegionTreeData.value, selectedNode.value.IdTipoRegion);
        const hijosMetadata = tipoRegionActual?.children || [];
        const hijoValido = hijosMetadata.length > 0 ? hijosMetadata[0] : null;
        formModal.value.IdTipoRegion = hijoValido ? hijoValido.IdTipoRegion : null;
    }
};


const filterNodeMethod = (value, data, node) => {
    if (!value) return true;
    const nombre = data.NombreRegion ? data.NombreRegion.toLowerCase() : '';
    const busqueda = value.toLowerCase();
    const idTipoTarget = selectedTipoRegionNode.value?.IdTipoRegion;
    if (data.IdTipoRegion === idTipoTarget && nombre.includes(busqueda)) {
        return true;
    }
    const checkHijos = (n) => {
        return n.childNodes.some(child => {
            const childNombre = child.data.NombreRegion.toLowerCase();
            const esNivelHijo = child.data.IdTipoRegion === idTipoTarget;
            if (esNivelHijo && childNombre.includes(busqueda)) return true;
            return checkHijos(child);
        });
    };

    return checkHijos(node);
};


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
    const targetId = data.IdTipoRegion;
    const tree = tiposRegionTreeRef.value;
    const ahora = Date.now();
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

const handleNodeSelected = (data) => {
    if (esModalVisible.value) return;
    selectedNode.value = data;
    nextTick(() => {
        treeRef.value?.setCurrentKey(data.IdRegion);
    });
};

const handleManageTiposRegion = () => { esModalTipoRegionVisible.value = true; };

const cerrarModalTipoRegion = () => {
    router.reload({
        only: ['tiposDeRegionTreeProp', 'tiposDeRegionProp', 'treeDataProp'],
        onSuccess: () => {
            esModalTipoRegionVisible.value = false;
            filterText.value = '';
            nextTick(() => {
                if (treeRef.value) {
                    treeRef.value.filter('');
                    const nodes = treeRef.value.store.nodesMap;
                    for (let key in nodes) {
                        nodes[key].expanded = false;
                    }
                }
                expandedTipoRegionKeys.value = [];
                if (tiposRegionTreeRef.value) {
                    const nodesLeft = tiposRegionTreeRef.value.store.nodesMap;
                    for (let key in nodesLeft) {
                        nodesLeft[key].expanded = false;
                    }
                }
                selectedNode.value = null;
                if (treeRef.value) treeRef.value.setCurrentKey(null);
            });
        }
    });
};


const modalTitle = computed(() => modalMode.value === "editar" ? "Modificar región" : "Ingresar nueva región");

const modalRules = {
    NombreRegion: [
        { required: true, message: "El nombre de la región es obligatorio.", trigger: "blur" },
        { max: 100, message: "Máximo 100 caracteres.", trigger: "blur" },
        {
            pattern: /^(?!.*  ).+$/,
            message: "No se permite ingresar más de un espacio seguido.",
            trigger: ["blur", "change"],
        },
    ],
    IdTipoRegion: [{ required: true, message: "El tipo de región es obligatorio.", trigger: "change" }],
    Abreviado: [
        { max: 10, message: "Máximo 10 caracteres.", trigger: "blur" },
        {
            pattern: /^(?!.*  ).+$/,
            message: "No se permite ingresar más de un espacio seguido.",
            trigger: ["blur", "change"],
        },
    ],
    ClaveRegion: [
        { max: 35, message: "Máximo 35 caracteres.", trigger: "blur" },
        {
            pattern: /^(?!.*  ).+$/,
            message: "No se permite ingresar más de un espacio seguido.",
            trigger: ["blur", "change"],
        },
    ],
};

const abrirModalParaInsertar = () => {
    modalMode.value = "insertar";
    const targetTypeId = selectedTipoRegionNode.value.IdTipoRegion;
    if (!filteredRegionsTree.value || filteredRegionsTree.value.length === 0) {
        opcionNivel.value = "raiz";
    } else if (selectedNode.value) {
        if (selectedNode.value.IdTipoRegion === targetTypeId) {
            opcionNivel.value = "mismo";
        } else {
            opcionNivel.value = "inferior";
        }
    } else {
        opcionNivel.value = "raiz";
    }
    formModal.value = {
        NombreRegion: "",
        IdTipoRegion: null,
        ClaveRegion: "",
        Abreviado: "",
        IdRegionAsc: 0
    };
    onOpcionNivelChange(opcionNivel.value);

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

    try {
        await formModalRef.value.validate();
    } catch (error) {
        return; 
    }

    const nombreNuevo = (formModal.value.NombreRegion || "").trim().toUpperCase();
    const modoActual = modalMode.value;
    const tipoRegionActual = formModal.value.IdTipoRegion;
    let idPadreFinal = 0;
    if (modoActual === "insertar") {
        if (opcionNivel.value === "mismo" && selectedNode.value) {
            idPadreFinal = selectedNode.value.IdRegionAsc || 0;
        } else if (opcionNivel.value === "inferior" && selectedNode.value) {
            idPadreFinal = selectedNode.value.IdRegion;
        } else {
            idPadreFinal = 0; 
        }
    } else {
        idPadreFinal = nodoEnModal.value.IdRegionAsc || 0;
    }

    let listaHermanos = [];
    if (idPadreFinal === 0) {
        listaHermanos = localTreeData.value; 
    } else {
        const nodoPadre = findNodeById(localTreeData.value, idPadreFinal);
        listaHermanos = nodoPadre?.children || [];
    }

    const duplicado = listaHermanos.find(nodo => {
        const mismoNombre = (nodo.NombreRegion || "").trim().toUpperCase() === nombreNuevo;
        const mismaRegion = nodo.IdTipoRegion === tipoRegionActual;
        const noEsElMismo = modoActual === 'editar' ? nodo.IdRegion !== nodoEnModal.value.IdRegion : true;

        return mismoNombre && mismaRegion && noEsElMismo;
    });

    if (duplicado) {
        nodoDuplicadoParaSeleccionar.value = duplicado;

        cerrarModalOperacion(); 

        const mensajeAviso = nombreNuevo === 'ND'
            ? `La región ingresada ya existe en este nivel.`
            : `La región ingresada ya existe en este nivel.`;
            

        mostrarNotificacion("Aviso", mensajeAviso, "warning");
        return;
    }

    const onSuccess = () => {
        cerrarModalOperacion();
        const titulo = modoActual === 'editar' ? "Modificación" : "Ingreso";
        const mensaje = modoActual === 'editar'
            ? "La región ha sido modificada con éxito."
            : "La región ha sido ingresada con éxito.";

        mostrarNotificacion(titulo, mensaje, "success");
        router.reload({ only: ['treeDataProp'] }); 
    };

    const onError = (errors) => {
        mostrarNotificacion("Error", Object.values(errors).flat().join("\n"), "error");
    };

    if (modoActual === "editar") {
        router.put(`/regiones/${nodoEnModal.value.IdRegion}`, formModal.value, {
            preserveState: true,
            onSuccess,
            onError
        });
    } else {
        const payload = {
            ...formModal.value,
            IdRegionAsc: idPadreFinal,
            opcionNivel: opcionNivel.value,
            idNodoReferencia: selectedNode.value ? selectedNode.value.IdRegion : null
        };
        router.post("/regiones", payload, {
            preserveState: true,
            onSuccess,
            onError
        });
    }
};


const handleEliminar = () => {
    if (!selectedNode.value) return ElMessage.warning("Por favor, seleccione una región para eliminar.");
    const nodoReal = findNodeById(localTreeData.value, selectedNode.value.IdRegion);

    if (nodoReal && nodoReal.children?.length > 0) {
        return mostrarNotificacion(
            "Aviso",
            "No es posible eliminar esta región porque tiene regiones que dependen de ella.",
            "warning"
        );
    }

    const nombre = selectedNode.value.NombreRegion;
    const mensaje = `¿Está seguro de eliminar la región seleccionada? Esta acción no se puede revertir.`;

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
    const idPadre = getAscendantId(nodeId, localTreeData.value);
    ElMessageBox.close();
    router.delete(`/regiones/${nodeId}`, {
        preserveScroll: true,
        onSuccess: () => {
            mostrarNotificacion("Eliminación", `La región ha sido eliminada correctamente.`, "success");
            router.reload({
                only: ['treeDataProp'],
                onSuccess: () => {
                    nextTick(() => {
                        if (idPadre) {
                            const nodoPadre = findNodeById(localTreeData.value, idPadre);
                            if (nodoPadre) {
                                selectedNode.value = nodoPadre;
                                treeRef.value?.setCurrentKey(idPadre);
                                const el = document.getElementById('region-node-' + idPadre);
                                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        } else {
                            if (filteredRegionsTree.value && filteredRegionsTree.value.length > 0) {
                                const primerNodo = filteredRegionsTree.value[0];
                                selectedNode.value = primerNodo;
                                treeRef.value?.setCurrentKey(primerNodo.IdRegion);
                                const el = document.getElementById('region-node-' + primerNodo.IdRegion);
                                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            } else {
                                selectedNode.value = null;
                            }
                        }
                    });
                }
            });
        },
        onError: (error) => mostrarNotificacion("Aviso", error.message || "Ocurrió un error.", "warning"),
    });
};


</script>
<template>
    <LayoutCuerpo :usar-app-layout="false" titulo-pag="Regiones" titulo-area="Catálogo de regiones geográficas">
        <div class="split-container">
            <el-card class="panel-card list-panel" shadow="never">
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
            </el-card>

            <el-card class="panel-card details-panel" shadow="never">
                <template #header>
                    <div class="header-container">

                        <div class="header-buscador">
                            <el-input v-model="filterText" placeholder="Escriba para buscar" clearable
                                :disabled="buscadorDeshabilitado" @keyup.enter="irAlNodoBuscado" />
                        </div>


                        <span class="details-header-title"></span>
                        <div class="right-header-content">
                            <div class="action-group">
                                <el-tooltip class="item" effect="dark" content="Ingresar">
                                    <el-button type="primary" circle @click="intentarAbrirModalInsertar"
                                        :disabled="botonNuevoDeshabilitado" title="Nuevo">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-box-arrow-in-down" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd"
                                                d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1h-2z" />
                                            <path fill-rule="evenodd"
                                                d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                                        </svg>
                                    </el-button>
                                </el-tooltip>

                                <el-tooltip class="item" effect="dark" content="Modificar">
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
                                </el-tooltip>

                                <el-tooltip class="item" effect="dark" content="Eliminar">
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
                                </el-tooltip>
                                <BotonSalir :accion="modal ? 'cerrar' : 'salida'" @salir="cerrarDialogo" />
                            </div>
                        </div>
                    </div>
                </template>

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

                    <el-form-item prop="NombreRegion" label="Nombre de la región:">
                        <el-input v-model="formModal.NombreRegion" class="input-mayusculas"
                            @input="formModal.NombreRegion = formModal.NombreRegion.toUpperCase()" clearable
                            maxlength="100" show-word-limit />
                    </el-form-item>

                    <el-form-item label="Abreviado:" prop="Abreviado">
                        <el-input v-model="formModal.Abreviado" class="input-mayusculas"
                            @input="formModal.Abreviado = formModal.Abreviado.toUpperCase()" clearable maxlength="10"
                            show-word-limit />
                    </el-form-item>

                    <el-form-item label="Clave:" prop="ClaveRegion">
                        <el-input v-model="formModal.ClaveRegion" class="input-mayusculas"
                            @input="formModal.ClaveRegion = formModal.ClaveRegion.toUpperCase()" clearable
                            maxlength="35" show-word-limit />
                    </el-form-item>
                </el-form>
            </div>
        </DialogGeneral>

        <DialogGeneral v-model="esModalTipoRegionVisible" :bot-cerrar="true" :press-esc="true" width="90%"
            @close="cerrarModalTipoRegion">

            <div class="dialog-body-container" style="padding: 10px;">
                <CuerpoTipoRegion :treeDataProp="tiposDeRegionTreeProp" :flatTreeDataProp="tiposDeRegionProp"
                    :isModal="true" @cerrar-modal="cerrarModalTipoRegion" />
            </div>
        </DialogGeneral>

        <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
            :mensaje="notificacionMensaje" :tipo="notificacionTipo" @close="handleCerrarNotificacion" />
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
    gap: 30px;

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

:deep(.fila-activa-completa > .el-tree-node__content) {
    background-color: #ddf6dd !important;
    border-radius: 4px;
    margin-bottom: 1px;
}

.nodo-tipo-region {
    font-size: 14px;
    padding: 2px 0;
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

:deep(.input-mayusculas .el-input__inner) {
    text-transform: uppercase;
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

</style>