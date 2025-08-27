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
import CambiarIconoButton from "@/Components/Biotica/CambiarIconoButton.vue";
import BotonSalir from '@/Components/Biotica/SalirButton.vue';

const treeRef = ref(null);
const ICONO_POR_DEFECTO = '/storage/images/RERJvyv0qvxOR9of8BRobZjiodN2DK4euvMWNYkZ.png';
const localTreeData = ref([]);
const selectedNode = ref(null);
const esModalVisible = ref(false);
const formModalRef = ref(null);
const modalMode = ref("");
const opcionNivel = ref("mismo");
const formModal = ref({ NombreCategoriaTaxonomica: "" });
const nodoEnModal = ref(null);

const expandedNodeIds = ref(new Set());

const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const esModalIconosVisible = ref(false);
const terminoBusquedaIcono = ref('');
const listaIconosEncontrados = ref([]);
const cargandoIconos = ref(false);
const notificacionDuracion = ref(5000);
let debounceTimer = null;


const props = defineProps({
  treeDataProp: { type: Array, required: true },
  flatTreeDataProp: { type: Array, required: true },
});

const handleNodeExpand = (data) => {
  expandedNodeIds.value.add(data.IdCategoriaTaxonomica);
};

const handleNodeCollapse = (data) => {
  expandedNodeIds.value.delete(data.IdCategoriaTaxonomica);
};

const handleNodeSelected = (data) => {
  if (esModalVisible.value) {
    ElMessage.info("Hay una operación en curso.");
    treeRef.value?.setCurrentKey(selectedNode.value?.IdCategoriaTaxonomica);
    return;
  }
  selectedNode.value = data;
};

const scrollToNode = (nodeId) => {
  nextTick(() => {
    const el = document.getElementById(`tree-node-${nodeId}`);
    if (el) el.scrollIntoView({ behavior: "smooth", block: "center" });
  });
};

const findNodeInTree = (nodes, nodeId) => {
  for (const node of nodes) {
    if (String(node.IdCategoriaTaxonomica) === String(nodeId)) return node;
    if (node.children) {
      const found = findNodeInTree(node.children, nodeId);
      if (found) return found;
    }
  }
  return null;
};

watch(() => props.treeDataProp, (newVal) => {
  localTreeData.value = newVal;
  nextTick(() => {
    if (!treeRef.value) return;
    expandedNodeIds.value.forEach(id => {
      const node = treeRef.value.getNode(id);
      if (node && !node.expanded) {
        node.expand();
      }
    });
  });
}, { immediate: true, deep: true });

const page = usePage();
watch(() => page.props.flash?.newNodeId, (newNodeId) => {
  if (newNodeId) {
    nextTick(() => {
      const newNodeData = props.flatTreeDataProp.find(n => n.IdCategoriaTaxonomica === newNodeId);
      if (newNodeData && newNodeData.IdAscendente) {
        expandedNodeIds.value.add(newNodeData.IdAscendente);
        const parentNode = treeRef.value.getNode(newNodeData.IdAscendente);
        if (parentNode) {
          parentNode.expand();
        }
      }
      treeRef.value?.setCurrentKey(newNodeId);
      selectedNode.value = newNodeData;
      scrollToNode(newNodeId);
    });
    router.page.props.flash.newNodeId = null;
  }
}, { deep: true });

onMounted(() => {
  if (props.treeDataProp.length > 0 && !page.props.flash?.newNodeId) {
    const firstNode = props.treeDataProp[0];
    selectedNode.value = firstNode;
    treeRef.value?.setCurrentKey(firstNode.IdCategoriaTaxonomica);
  }
});

const modalTitle = computed(() => modalMode.value === "editar" ? "Modificar la categoría taxonómica seleccionada" : "Ingresar una nueva categoría taxonómica");
const modalRules = { NombreCategoriaTaxonomica: [{ required: true, message: "El nombre es obligatorio.", trigger: "blur" }] };

const abrirModalParaInsertar = () => {
  modalMode.value = "insertar";
  formModal.value = { NombreCategoriaTaxonomica: "" };
  opcionNivel.value = selectedNode.value ? "mismo" : "raiz";
  esModalVisible.value = true;
  nextTick(() => formModalRef.value?.clearValidate());
};

const abrirModalParaEditar = () => {
  if (!selectedNode.value) return ElMessage.warning("Seleccione un nodo para editar.");
  if (isEditarDeshabilitado.value) {
    return mostrarNotificacion("Error", "Esta categoría no puede ser modificada.", "error");
  } else {
    modalMode.value = "editar";
    nodoEnModal.value = { ...selectedNode.value };
    formModal.value = { NombreCategoriaTaxonomica: selectedNode.value.NombreCategoriaTaxonomica };
    esModalVisible.value = true;
    nextTick(() => formModalRef.value?.clearValidate());
  }
};

const cerrarModalOperacion = () => {
  esModalVisible.value = false;
  nodoEnModal.value = null;
};



// =========================================================================
// ========================== INICIO DE LA MODIFICACIÓN ==========================
// =========================================================================

const guardarDesdeModal = async () => {
  if (!formModalRef.value) return;
  const isValid = await formModalRef.value.validate();
  if (!isValid) return;

  // Lógica de validación de duplicados
  const nombreNormalizado = formModal.value.NombreCategoriaTaxonomica.trim().toLowerCase();
  const esEdicion = modalMode.value === 'editar';

  const registroExistente = props.flatTreeDataProp.find(item => {
    const mismoNombre = item.NombreCategoriaTaxonomica.trim().toLowerCase() === nombreNormalizado;
    return esEdicion ? (mismoNombre && item.IdCategoriaTaxonomica !== nodoEnModal.value.IdCategoriaTaxonomica) : mismoNombre;
  });

  if (registroExistente) {
    mostrarNotificacionError(
      "Aviso",
      `Ya existe una categoría taxonómica con el nombre '${formModal.value.NombreCategoriaTaxonomica}'`,
      "error",
      0 // Duración 0 para que no se cierre sola
    );
    return;
  }

  const onSuccess = () => {
    cerrarModalOperacion();
    mostrarNotificacion("Ingreso", "La información ha sido ingresada correctamente.", "success");
  };
  const onError = (e) => mostrarNotificacion("Error", Object.values(e).flat().join("\n"), "error");

  if (modalMode.value === "insertar") {
    let nodoReferenciaActual = null;
    if (treeRef.value) {
      const currentNodeKey = treeRef.value.getCurrentKey();
      if (currentNodeKey) {
        nodoReferenciaActual = props.flatTreeDataProp.find(n => n.IdCategoriaTaxonomica === currentNodeKey);
      }
    }
    const nodoParaCalcular = nodoReferenciaActual || selectedNode.value;

    if (!nodoParaCalcular && opcionNivel.value !== 'raiz') {
      return ElMessage.error("No se pudo determinar un nodo de referencia para la inserción.");
    }
    
    const resultado = calcularNiveles_ADAPTADO_AL_CAOS(nodoParaCalcular, opcionNivel.value, props.flatTreeDataProp);
    if (!resultado) return;
    
    const datos = {
      NombreCategoriaTaxonomica: formModal.value.NombreCategoriaTaxonomica.trim(),
      ...resultado.niveles,
      IdAscendente: resultado.nuevoIdAscendente
    };

    if (nodoParaCalcular && opcionNivel.value === 'inferior') {
      expandedNodeIds.value.add(nodoParaCalcular.IdCategoriaTaxonomica);
    }

    router.post("/categorias-taxonomicas", datos, {
      preserveScroll: true,
      onSuccess,
      onError
    });
  } else {
    const datos = {
      NombreCategoriaTaxonomica: formModal.value.NombreCategoriaTaxonomica.trim()
    };
    const url = `/categorias-taxonomicas/${nodoEnModal.value.IdCategoriaTaxonomica}`;
    router.put(url, datos, {
      preserveScroll: true,
      onSuccess,
      onError
    });
  }
};

// =========================================================================
// ============================ FIN DE LA MODIFICACIÓN ===========================
// =========================================================================



const MAX_NIVELES = 12;

const calcularNiveles_ADAPTADO_AL_CAOS = (nodoReferencia, opcion, todosLosNodos) => {
    const nuevosNiveles = {};
    for (let i = 1; i <= MAX_NIVELES; i++) {
        nuevosNiveles[`IdNivel${i}`] = 0;
    }

    if (opcion === "raiz" || !nodoReferencia) {
        let maxRaiz = 0;
        todosLosNodos.filter(n => !n.IdAscendente || n.IdAscendente === 0)
                     .forEach(n => { maxRaiz = Math.max(maxRaiz, n.IdNivel1 || 0); });
        nuevosNiveles.IdNivel1 = maxRaiz + 1;
        return { niveles: nuevosNiveles, nuevoIdAscendente: null };
    }

    if (opcion === 'inferior') {
        const padre = nodoReferencia;
        const nuevoIdAscendente = padre.IdCategoriaTaxonomica;
        
        let profundidadPadre = 0;
        let ancestroActual = padre;
        const ancestroMap = new Map(todosLosNodos.map(n => [n.IdCategoriaTaxonomica, n]));
        while(ancestroActual) {
            profundidadPadre++;
            ancestroActual = ancestroMap.get(ancestroActual.IdAscendente);
        }
        const nivelDelNuevoHijo = profundidadPadre + 1;

        if (nivelDelNuevoHijo > MAX_NIVELES) {
             mostrarNotificacion("Error", "Profundidad máxima excedida.", "error");
             return null;
        }

        for (let i = 1; i <= profundidadPadre; i++) {
            nuevosNiveles[`IdNivel${i}`] = padre[`IdNivel${i}`];
        }
        
        const hermanos = todosLosNodos.filter(n => n.IdAscendente === nuevoIdAscendente);
        let maxNivelHijo = 0;
        hermanos.forEach(h => {
            maxNivelHijo = Math.max(maxNivelHijo, h[`IdNivel${nivelDelNuevoHijo}`] || 0);
        });
        
        nuevosNiveles[`IdNivel${nivelDelNuevoHijo}`] = maxNivelHijo + 1;
        return { niveles: nuevosNiveles, nuevoIdAscendente: nuevoIdAscendente };
    }

    if (opcion === 'mismo') {
        const nuevoIdAscendente = nodoReferencia.IdAscendente;
        if (!nuevoIdAscendente) {
            return calcularNiveles_ADAPTADO_AL_CAOS(null, 'raiz', todosLosNodos);
        }
        
        const padre = todosLosNodos.find(n => n.IdCategoriaTaxonomica === nuevoIdAscendente);
        if (!padre) {
            mostrarNotificacion("Error", "No se pudo encontrar el nodo padre para crear un hermano.", "error");
            return null;
        }
        
        return calcularNiveles_ADAPTADO_AL_CAOS(padre, 'inferior', todosLosNodos);
    }
    return null;
};

const handleEliminar = () => {
  if (!selectedNode.value) return ElMessage.warning("Por favor, seleccione un nodo para eliminar.");
  if (selectedNode.value.children && selectedNode.value.children.length > 0) {
    return mostrarNotificacion("Error", "No es posible eliminar categorías taxonómicas precargadas por omisión en Biótica.", "error");
  }
  const nombre = selectedNode.value.NombreCategoriaTaxonomica;
  const mensaje = `¿Está seguro de eliminar la categoría "${nombre}"? Esta acción no se puede revertir.`;
  const proceedWithDeletion = () => {
    router.delete(`/categorias-taxonomicas/${selectedNode.value.IdCategoriaTaxonomica}`, {
      preserveScroll: true,
      onSuccess: () => {
        mostrarNotificacion("¡Eliminación Exitosa!", `El elemento "${nombre}" ha sido eliminado.`, "success");
        const parent = findNodeInTree(localTreeData.value, selectedNode.value.IdAscendente);
        selectedNode.value = parent || (localTreeData.value.length > 0 ? localTreeData.value[0] : null);
        if (selectedNode.value) treeRef.value?.setCurrentKey(selectedNode.value.IdCategoriaTaxonomica);
      },
      onError: (e) => mostrarNotificacion("Error al Eliminar", e.message || "Ocurrió un error.", "error"),
    });
    ElMessageBox.close();
  };

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
        h(BotonAceptar, { texto: "Sí, Eliminar", onClick: proceedWithDeletion }),
      ]),
    ]),
  }).catch(() => { });
};

const iconosSugeridos = [
  'mdi:leaf', 'mdi:tree', 'mdi:flower', 'mdi:forest', 'mdi:pine-tree', 'mdi:sprout', 'mdi:seed', 'mdi:grass',
  'mdi:paw', 'mdi:fish', 'mdi:bug', 'mdi:bee', 'mdi:bird', 'mdi:dog', 'mdi:cat', 'mdi:cow', 'mdi:pig',
  'mdi:rabbit', 'mdi:snake', 'mdi:turtle', 'mdi:spider', 'mdi:ladybug', 'mdi:butterfly', 'mdi:snail',
  'mdi:folder', 'mdi:folder-open', 'mdi:folder-multiple', 'mdi:folder-account', 'mdi:folder-image',
  'mdi:file-document', 'mdi:file-pdf-box', 'mdi:file-word-box', 'mdi:file-excel-box', 'mdi:file-image',
  'mdi:file-music', 'mdi:file-video', 'mdi:file-cloud', 'mdi:file-chart',
  'mdi:account', 'mdi:account-group', 'mdi:account-circle', 'mdi:account-plus', 'mdi:account-remove',
  'mdi:home', 'mdi:home-variant', 'mdi:pencil', 'mdi:trash-can', 'mdi:delete-forever', 'mdi:cog', 'mdi:cog-outline',
  'mdi:check', 'mdi:check-bold', 'mdi:check-circle', 'mdi:close', 'mdi:close-thick',
  'mdi:plus', 'mdi:minus', 'mdi:plus-box', 'mdi:minus-box', 'mdi:magnify', 'mdi:zoom-in', 'mdi:zoom-out',
  'mdi:eye', 'mdi:eye-off', 'mdi:lock', 'mdi:lock-open-variant', 'mdi:key-variant', 'mdi:content-copy',
  'mdi:upload', 'mdi:download', 'mdi:share-variant', 'mdi:link-variant', 'mdi:filter-variant',
  'mdi:sort', 'mdi:refresh', 'mdi:undo', 'mdi:redo', 'mdi:history', 'mdi:dots-vertical', 'mdi:menu',
  'mdi:arrow-up', 'mdi:arrow-down', 'mdi:arrow-left', 'mdi:arrow-right', 'mdi:chevron-up', 'mdi:chevron-down',
  'mdi:chevron-left', 'mdi:chevron-right', 'mdi:arrow-expand-all', 'mdi:arrow-collapse-all',
  'mdi:earth', 'mdi:map-marker', 'mdi:map', 'mdi:compass', 'mdi:navigation-variant', 'mdi:flag',
  'mdi:email', 'mdi:phone', 'mdi:message-text', 'mdi:chat', 'mdi:at', 'mdi:bell', 'mdi:bell-ring',
  'mdi:laptop', 'mdi:cellphone', 'mdi:tablet', 'mdi:desktop-classic', 'mdi:printer', 'mdi:camera',
  'mdi:lightbulb', 'mdi:trophy', 'mdi:star', 'mdi:heart', 'mdi:thumb-up', 'mdi:thumb-down',
  'mdi:car', 'mdi:bus', 'mdi:train', 'mdi:airplane', 'mdi:rocket-launch', 'mdi:hammer-wrench',
  'mdi:circle', 'mdi:square', 'mdi:triangle', 'mdi:hexagon', 'mdi:rhombus', 'mdi:octagon', 'mdi:star-four-points',
  'mdi:numeric-1-box', 'mdi:numeric-2-box', 'mdi:numeric-3-box', 'mdi:alpha-a-box', 'mdi:alpha-b-box', 'mdi:alpha-s-box',
  'mdi:beaker', 'mdi:flask', 'mdi:dna', 'mdi:atom', 'mdi:chart-bar', 'mdi:chart-line', 'mdi:chart-pie',
  'mdi:calculator', 'mdi:database', 'mdi:server', 'mdi:cloud', 'mdi:code-tags',
  'mdi:weather-sunny', 'mdi:weather-night', 'mdi:weather-rainy', 'mdi:weather-cloudy', 'mdi:fire', 'mdi:water',
  'mdi:currency-usd', 'mdi:credit-card', 'mdi:cart', 'mdi:calendar', 'mdi:clock', 'mdi:tag',
  'heroicons:adjustments-horizontal-solid', 'heroicons:archive-box-arrow-down-solid', 'heroicons:backward-solid', 'heroicons:forward-solid',
  'heroicons:bars-3-solid', 'heroicons:bookmark-slash-solid', 'heroicons:clipboard-document-check-solid', 'heroicons:command-line-solid',
  'heroicons:cpu-chip-solid', 'heroicons:document-duplicate-solid', 'heroicons:finger-print-solid', 'heroicons:gift-solid',
  'heroicons:hand-raised-solid', 'heroicons:inbox-arrow-down-solid', 'heroicons:lifebuoy-solid',
  'heroicons:receipt-refund-solid', 'heroicons:scale-solid', 'heroicons:server-stack-solid', 'heroicons:shield-exclamation-solid',
  'heroicons:stop-circle-solid', 'heroicons:swatch-solid', 'heroicons:ticket-solid',
  'heroicons:trophy-solid', 'heroicons:viewfinder-circle-solid', 'heroicons:wrench-screwdriver-solid',
  'tabler:2fa', 'tabler:3d-cube-sphere', 'tabler:abc', 'tabler:access-point', 'tabler:activity', 'tabler:adjustments-alt',
  'tabler:alarm', 'tabler:alert-triangle', 'tabler:alien', 'tabler:align-center', 'tabler:ambulance', 'tabler:anchor',
  'tabler:angle', 'tabler:antenna-bars-5', 'tabler:armchair', 'tabler:arrow-autofit-content', 'tabler:assembly', 'tabler:award',
  'tabler:axis-x', 'tabler:backspace', 'tabler:badge', 'tabler:ball-football', 'tabler:barcode',
  'tabler:basket', 'tabler:battery-charging', 'tabler:bed', 'tabler:bike', 'tabler:binary-tree', 'tabler:biohazard',
  'tabler:blockquote', 'tabler:bluetooth', 'tabler:bolt', 'tabler:bone', 'tabler:book', 'tabler:bookmark',
  'tabler:border-all', 'tabler:box', 'tabler:brand-apple', 'tabler:brand-windows', 'tabler:brand-android', 'tabler:brand-github',
  'tabler:brand-gitlab', 'tabler:brand-google', 'tabler:brand-meta', 'tabler:brand-tailwind', 'tabler:brand-vue', 'tabler:brand-react',
  'tabler:briefcase', 'tabler:brightness-up', 'tabler:browser', 'tabler:building-bank', 'tabler:building-bridge', 'tabler:building-hospital',
  'tabler:building-store', 'tabler:bulb', 'tabler:calculator', 'tabler:calendar-event', 'tabler:camera-plus', 'tabler:capture',
  'tabler:cash', 'tabler:cast', 'tabler:ce', 'tabler:certificate', 'tabler:charging-pile', 'tabler:chart-area-line',
  'tabler:chart-bar', 'tabler:chart-infographic', 'tabler:chart-pie', 'tabler:checkbox', 'tabler:checkup-list', 'tabler:chef-hat',
  'tabler:circle-dashed', 'tabler:clipboard-text', 'tabler:clock', 'tabler:cloud-fog', 'tabler:cloud-storm', 'tabler:code',
  'tabler:color-picker', 'tabler:columns', 'tabler:command', 'tabler:compass', 'tabler:components', 'tabler:cone',
  'tabler:container', 'tabler:cookie', 'tabler:copy', 'tabler:copyright', 'tabler:crane', 'tabler:credit-card',
  'tabler:crop', 'tabler:crown', 'tabler:cup', 'tabler:currency-dollar', 'tabler:currency-euro', 'tabler:currency-pound',
  'tabler:cut', 'tabler:dashboard', 'tabler:database', 'tabler:device-desktop', 'tabler:device-laptop', 'tabler:device-mobile',
  'tabler:device-tablet', 'tabler:device-watch', 'tabler:diamond', 'tabler:dice', 'tabler:dimensions', 'tabler:direction-horizontal',
  'tabler:directions', 'tabler:disabled', 'tabler:disc', 'tabler:discount', 'tabler:divide', 'tabler:dna',
  'tabler:door', 'tabler:dots-circle-horizontal', 'tabler:download', 'tabler:droplet', 'tabler:eraser', 'tabler:exposure',
  'tabler:external-link', 'tabler:face-id', 'tabler:fall', 'tabler:feather', 'tabler:file-invoice', 'tabler:file-text',
  'tabler:filter', 'tabler:fingerprint', 'tabler:fire-hydrant', 'tabler:firetruck', 'tabler:first-aid-kit', 'tabler:flag',
  'tabler:flame', 'tabler:flask', 'tabler:flip-horizontal', 'tabler:float-center', 'tabler:focus', 'tabler:forbid',
  'tabler:forklift', 'tabler:forms', 'tabler:frame', 'tabler:friends', 'tabler:gas-station', 'tabler:gauge',
  'tabler:ghost', 'tabler:git-branch', 'tabler:git-compare', 'tabler:git-merge', 'tabler:git-pull-request',
  'tabler:glass-full', 'tabler:globe', 'tabler:gps', 'tabler:grid-dots', 'tabler:hand-stop', 'tabler:hanger',
  'tabler:hash', 'tabler:heading', 'tabler:headset', 'tabler:helicopter', 'tabler:highlight', 'tabler:ice-cream',
  'tabler:id', 'tabler:inbox', 'tabler:info-circle', 'tabler:input-search', 'tabler:ironing',
  'tabler:key', 'tabler:keyboard', 'tabler:language', 'tabler:layers-difference', 'tabler:layout-2', 'tabler:leaf',
  'tabler:lego', 'tabler:letter-case', 'tabler:license', 'tabler:lifebuoy', 'tabler:line-height', 'tabler:link',
  'tabler:list-check', 'tabler:location', 'tabler:lock', 'tabler:login', 'tabler:logout', 'tabler:magnet',
  'tabler:mail', 'tabler:manual-gearbox', 'tabler:map-pin', 'tabler:markdown', 'tabler:mask', 'tabler:math',
  'tabler:maximize', 'tabler:medal', 'tabler:medical-cross', 'tabler:message-circle', 'tabler:microphone',
  'tabler:microscope', 'tabler:military-rank', 'tabler:minimize', 'tabler:mood-happy', 'tabler:mood-sad', 'tabler:mood-neutral',
  'tabler:moon', 'tabler:mountain', 'tabler:movie', 'tabler:music', 'tabler:new-section', 'tabler:news',
  'tabler:note', 'tabler:notebook', 'tabler:notification', 'tabler:overline', 'tabler:package', 'tabler:paint',
  'tabler:palette', 'tabler:paper-bag', 'tabler:parachute', 'tabler:parentheses', 'tabler:parking', 'tabler:paw',
  'tabler:peace', 'tabler:pepper', 'tabler:percentage', 'tabler:phone-call', 'tabler:photo',
  'tabler:picture-in-picture', 'tabler:pill', 'tabler:pin', 'tabler:plane', 'tabler:planet', 'tabler:plug',
  'tabler:polaroid', 'tabler:polygon', 'tabler:pool', 'tabler:power', 'tabler:presentation', 'tabler:printer',
  'tabler:prompt', 'tabler:propeller', 'tabler:puzzle', 'tabler:pyramid', 'tabler:qrcode', 'tabler:radio',
  'tabler:radioactive', 'tabler:record-mail', 'tabler:registered', 'tabler:relation-one-to-one', 'tabler:repeat',
  'tabler:replace', 'tabler:road', 'tabler:rocket', 'tabler:rotate-clockwise', 'tabler:route',
  'tabler:ruler', 'tabler:run', 'tabler:sailboat', 'tabler:salt', 'tabler:satellite', 'tabler:sausage',
  'tabler:scale', 'tabler:scan', 'tabler:school', 'tabler:scissors', 'tabler:scooter', 'tabler:screen-share',
  'tabler:search', 'tabler:section', 'tabler:seeding', 'tabler:select', 'tabler:send', 'tabler:separator',
  'tabler:server', 'tabler:settings', 'tabler:share', 'tabler:shield', 'tabler:ship', 'tabler:shopping-cart',
  'carbon:add-alt', 'carbon:analytics', 'carbon:api', 'carbon:app-switcher', 'carbon:attachment', 'carbon:batch-job',
  'carbon:blockchain', 'carbon:branch', 'carbon:building', 'carbon:bullhorn', 'carbon:calculator-check', 'carbon:calendar-tools',
  'carbon:camera-action', 'carbon:catalog', 'carbon:certificate', 'carbon:chart-area', 'carbon:chart-bubble', 'carbon:chart-line',
  'carbon:checkbox-checked', 'carbon:chip', 'carbon:clean', 'carbon:cloud-app', 'carbon:cloud-satellite',
  'carbon:cognitive', 'carbon:collapse-all', 'carbon:connect', 'carbon:container-software', 'carbon:continue',
  'carbon:data-base', 'carbon:data-share', 'carbon:debug', 'carbon:deploy', 'carbon:deployment-policy', 'carbon:diagram',
  'carbon:document', 'carbon:edge-node', 'carbon:edit-off', 'carbon:education', 'carbon:enterprise', 'carbon:events',
  'carbon:explore', 'carbon:export', 'carbon:face-activated', 'carbon:finance', 'carbon:fingerprint-recognition',
  'carbon:flash-filled', 'carbon:flow', 'carbon:fork', 'carbon:function', 'carbon:game-console', 'carbon:generate-pdf',
  'carbon:globe', 'carbon:group', 'carbon:hashtag', 'carbon:health-cross', 'carbon:idea', 'carbon:image-search',
  'carbon:import-export', 'carbon:infrastructure-classic', 'carbon:inventory-management', 'carbon:iot-platform', 'carbon:launch',
  'carbon:license', 'carbon:light', 'carbon:location', 'carbon:loop', 'carbon:machine-learning', 'carbon:manage-protection',
  'carbon:mobile-check', 'carbon:model', 'carbon:network-4', 'carbon:notebook-reference', 'carbon:notification-new',
  'carbon:open-panel-left', 'carbon:operations-field', 'carbon:password', 'carbon:pedestrian', 'carbon:person-favorite',
  'carbon:play-outline', 'carbon:portfolio', 'carbon:power', 'carbon:presentation-file', 'carbon:purchase', 'carbon:query',
  'carbon:receipt', 'carbon:recommend', 'carbon:report', 'carbon:request-quote', 'carbon:reset',
  'carbon:review', 'carbon:save-image', 'carbon:scan', 'carbon:script', 'carbon:send-alt', 'carbon:service-desk',
  'carbon:session-border-control', 'carbon:settings-adjust', 'carbon:shopping-catalog', 'carbon:skill-level',
  'carbon:spell-check', 'carbon:split-screen', 'carbon:stamp', 'carbon:task', 'carbon:terminal', 'carbon:text-font',
  'carbon:thumbs-up', 'carbon:timer', 'carbon:tool-kit', 'carbon:traffic-cone', 'carbon:upgrade', 'carbon:user-avatar',
  'carbon:user-profile', 'carbon:video-chat', 'carbon:view-mode-2', 'carbon:virtual-machine', 'carbon:voice-activate',
  'carbon:volume-up', 'carbon:vpn', 'carbon:watson', 'carbon:wifi', 'carbon:workspace',
];

const abrirModalIconos = () => {
  if (!selectedNode.value) return ElMessage.warning("Seleccione un nodo.");
  if (isCambiarIconoDeshabilitado.value) {
    return mostrarNotificacion("Error", "El ícono de esta categoría no puede ser modificado.", "error");
  } else {
    esModalIconosVisible.value = true;
    listaIconosEncontrados.value = iconosSugeridos;
    terminoBusquedaIcono.value = '';
  }
};


const cerrarModalIconos = () => { esModalIconosVisible.value = false; };



const seleccionarIcono = async (iconName) => {
  try {
    const response = await fetch(`https://api.iconify.design/${iconName}.svg`);
    if (!response.ok) throw new Error('No se pudo obtener el SVG del ícono.');
    const svgContent = await response.text();

    const nodeId = selectedNode.value.IdCategoriaTaxonomica;

    router.put(`/categorias-taxonomicas/${nodeId}/update-icon`, { RutaIcono: svgContent }, {
      preserveScroll: true,
      onSuccess: () => {
        mostrarNotificacion("Éxito", "Ícono actualizado.", "success");
        cerrarModalIconos();
      },
      onError: (e) => mostrarNotificacion("Error", Object.values(e).flat().join("\n"), "error"),
    });
  } catch (error) {
    mostrarNotificacion("Error", "No se pudo procesar el ícono.", "error");
  }
};

const onInputBusquedaIcono = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(async () => {
    let term = terminoBusquedaIcono.value.trim().toLowerCase();
    if (term.length < 3) {
      if (term === '') listaIconosEncontrados.value = iconosSugeridos;
      return;
    }
    cargandoIconos.value = true;
    try {
      const res = await fetch(`https://api.iconify.design/search?query=${term}&limit=48`);
      const data = await res.json();
      listaIconosEncontrados.value = data.icons || [];
    } catch (error) { console.error("Error buscando iconos:", error); }
    finally { cargandoIconos.value = false; }
  }, 500);
};

const mostrarNotificacion = (titulo, mensaje, tipo) => { notificacionTitulo.value = titulo; notificacionMensaje.value = mensaje; notificacionTipo.value = tipo; notificacionVisible.value = true; };


const mostrarNotificacionError = (titulo, mensaje, tipo = "info", duracion = 5000) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = 0;
    notificacionVisible.value = true;
};

const isAccionDependienteDeNodoDeshabilitada = computed(() => !selectedNode.value || esModalVisible.value);


const isEditarDeshabilitado = computed(() => {
  if (!selectedNode.value || esModalVisible.value) {
    return true;
  }

  if (selectedNode.value.IdCategoriaTaxonomica < 132) {
    return true;
  }

  return false;
});

const isCambiarIconoDeshabilitado = computed(() => {
  if (!selectedNode.value || esModalVisible.value) {
    return true;
  }

  if (selectedNode.value.IdCategoriaTaxonomica < 132 && selectedNode.value.RutaIcono === ICONO_POR_DEFECTO) {
    return false;
  }

  if (selectedNode.value.RutaIcono !== ICONO_POR_DEFECTO) {
    return true;
  }

  return false;
});
</script>



<template>
  <AppLayout title="Categorías Taxonómicas">
    <LayoutCuerpo :usar-app-layout="false" titulo-pag="Categorías Taxonómicas"
      titulo-area="Catálogo de categorías taxonómicas">
      <el-card class="box-card tree-card">
        <template #header>
          <div class="header-container">
            <div class="left-header-content"></div>
            <div class="right-header-content">
              <div class="action-group">
                <NuevoButton @crear="abrirModalParaInsertar" toolPosicion="bottom" :disabled="esModalVisible" />
                <EditarButton @editar="abrirModalParaEditar" toolPosicion="bottom"
                  :disabled="isAccionDependienteDeNodoDeshabilitada" />
                <EliminarButton @eliminar="handleEliminar" toolPosicion="bottom"
                  :disabled="isAccionDependienteDeNodoDeshabilitada" />
                <CambiarIconoButton @cambiar-icono="abrirModalIconos" toolPosicion="bottom"
                  :disabled="isAccionDependienteDeNodoDeshabilitada" />
                <BotonSalir toolPosicion="bottom" />
              </div>
            </div>
          </div>
        </template>

        <el-tree v-if="localTreeData && localTreeData.length" ref="treeRef" :data="localTreeData"
          :props="{ children: 'children', label: 'NombreCategoriaTaxonomica' }" node-key="IdCategoriaTaxonomica"
          :current-node-key="selectedNode?.IdCategoria" highlight-current :expand-on-click-node="true"
          @node-expand="handleNodeExpand" @node-collapse="handleNodeCollapse" @node-click="handleNodeSelected"
          class="custom-element-tree">
          <template #default="{ data }">
            <span :id="`tree-node-${data.IdCategoriaTaxonomica}`" class="custom-tree-node-content">

              <img v-if="!data.RutaIcono" src="/storage/images/REvyORsYggrsOFexbUteuMmMhVzDTfKaZzDkAffD.png"
                class="node-icon-wrapper" />

              <template v-else>
                <span v-if="data.RutaIcono.startsWith('<svg')" v-html="data.RutaIcono" class="node-icon-wrapper">
                </span>
                <img v-else :src="data.RutaIcono" class="node-icon-wrapper" />
              </template>

              <span>{{ data.NombreCategoriaTaxonomica }}</span>

            </span>
          </template>
        </el-tree>

        <div v-else class="no-data-message">
          No hay datos de categorías taxonómicas para mostrar.
        </div>
      </el-card>
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

            <div v-if="modalMode === 'insertar' && selectedNode" class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Posición:</label>
              <el-radio-group v-model="opcionNivel">
                <el-radio value="mismo">Mismo nivel</el-radio>
                <el-radio value="inferior">Nivel inferior</el-radio>
              </el-radio-group>
            </div>

            <el-form-item prop="NombreCategoriaTaxonomica" label="Nombre de la Categoría:">
              <el-input v-model="formModal.NombreCategoriaTaxonomica" placeholder="Ingrese el nombre" clearable
                maxlength="255" show-word-limit />
            </el-form-item>


          </el-form>

        </div>
      </DialogGeneral>

      <DialogGeneral v-model="esModalIconosVisible" :bot-cerrar="true" :press-esc="true" @close="cerrarModalIconos">
        <div class="dialog-header">
          <h3>Seleccione el ícono para la categoría "{{ selectedNode?.NombreCategoriaTaxonomica }}"</h3>
        </div>
        <div class="dialog-body-container">
          <el-input v-model="terminoBusquedaIcono" placeholder="Buscar ícono (ej. 'hoja', 'animal')"
            @input="onInputBusquedaIcono" clearable />
          <h4 class="icon-section-title">{{ terminoBusquedaIcono.trim() === '' ? 'Íconos Sugeridos' : 'Resultados de la  búsqueda' }}</h4>
          <div v-loading="cargandoIconos" class="icon-grid mt-4">
            <div v-for="iconName in listaIconosEncontrados" :key="iconName" class="icon-item"
              @click="seleccionarIcono(iconName)">
              <img :src="`https://api.iconify.design/${iconName}.svg?color=currentColor`" />
            </div>
          </div>
          <p v-if="!cargandoIconos && terminoBusquedaIcono.length >= 3 && listaIconosEncontrados.length === 0"
            class="text-center text-gray-500 mt-4">
            No se encontraron resultados.
          </p>
        </div>
      </DialogGeneral>

      <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
        :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
        @close="notificacionVisible = false" />
    </Teleport>
  </AppLayout>
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
  height: 726px;
  max-width: 1600px;
  max-height: 726px;
  display: flex;
  flex-direction: column;
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
  margin-top: 24px;
  gap: 25px;
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

.node-icon-wrapper {
  width: 20px;
  height: 20px;
  margin-right: 5px;
}

.icon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
  gap: 10px;
  max-height: 400px;
  overflow-y: auto;
  border: 1px solid #eee;
  padding: 10px;
  border-radius: 4px;
}

.icon-item {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 60px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  cursor: pointer;
  font-size: 28px;
  transition: all 0.2s ease;
}

.icon-item:hover {
  background-color: #f5f7fa;
  color: #409eff;
  transform: scale(1.1);
}

.icon-section-title {
  margin-top: 20px;
  margin-bottom: 10px;
  font-size: 0.9em;
  font-weight: 600;
  color: #909399;
  text-align: left;
}
</style>