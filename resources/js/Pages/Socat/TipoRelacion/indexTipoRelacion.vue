<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import NuevoButton from "@/Components/Biotica/NuevoButton.vue";
import EditarButton from "@/Components/Biotica/EditarButton.vue";
import EliminarButton from "@/Components/Biotica/EliminarButton.vue";
import GuardarButton from "@/Components/Biotica/GuardarButton.vue";
import DialogGeneral from "@/Components/Biotica/DialogGeneral.vue";
import ModalGenerico from "@/Components/Biotica/ModalGenerico.vue";
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from "@/Components/Biotica/BotonAceptar.vue";
import BotonCancelar from "@/Components/Biotica/BotonCancelar.vue";
import { ref, computed, watch, onMounted, nextTick, h } from "vue";
import { ElTree, ElMessage, ElMessageBox, ElInput, ElRadioGroup, ElRadio, ElForm, ElFormItem, ElSelect, ElOption, } from "element-plus";
import { router, usePage } from "@inertiajs/vue3";
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import CambiarIconoButton from "@/Components/Biotica/CambiarIconoButton.vue";
import BotonSalir from '@/Components/Biotica/SalirButton.vue';

const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);
const treeRef = ref(null);
const expandedNodeIds = ref(new Set());
const nodeIdToFocus = ref(null);
const nodeIdToSelectAfterInsert = ref(null);
const nodeDataForDeleteConfirmation = ref(null);
const nodeIdToScrollToAfterNotification = ref(null);
const localTreeData = ref([]);
const selectedNode = ref(null);
const esModalVisible = ref(false);
const formModalRef = ref(null);
const ICONO_POR_DEFECTO = '/storage/images/RERJvyv0qvxOR9of8BRobZjiodN2DK4euvMWNYkZ.png';


const esModalIconosVisible = ref(false);
const terminoBusquedaIcono = ref('');
const listaIconosEncontrados = ref([]);
const cargandoIconos = ref(false);
let debounceTimer = null;

const formModal = ref({
    Descripcion: "",
    Direccionalidad: null,
});
const modalMode = ref("");
const opcionNivel = ref("mismo");
const nodoEnModal = ref(null);

const props = defineProps({
    treeDataProp: { type: Array, required: true, default: () => [] },
    flatTreeDataProp: { type: Array, required: true, default: () => [] },
});




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


const traduccionesIconos = {
    'arbol': 'tree',
    'árbol': 'tree',
    'hoja': 'leaf',
    'animal': 'animal',
    'perro': 'dog',
    'gato': 'cat',
    'usuario': 'user',
    'persona': 'person',
    'casa': 'home',
    'hogar': 'home',
    'flecha': 'arrow',
    'engrane': 'gear',
    'configuracion': 'settings',
    'configuración': 'settings',
    'guardar': 'save',
    'eliminar': 'delete',
    'borrar': 'trash',
    'lapiz': 'pencil',
    'lápiz': 'pencil',
    'letra e': 'letter e',
    'editar': 'edit',
    'cruz': 'cross',
    'cerrar': 'close',
    'mas': 'plus',
    'más': 'plus',
    'agregar': 'add',
    'ojo': 'eye',
    'ver': 'view',
    'nube': 'cloud',
};


const seleccionarIcono = async (iconName) => {
    try {
        const response = await fetch(`https://api.iconify.design/${iconName}.svg`);
        if (!response.ok) {
            throw new Error('No se pudo obtener el SVG del ícono.');
        }
        const svgContent = await response.text();

        const nodeId = selectedNode.value.IdTipoRelacion;
        const datosUpdate = { RutaIcono: svgContent };

        router.put(`/tipos-relacion/${nodeId}/update-icon`, datosUpdate, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                mostrarNotificacion("¡Éxito!", "Ícono actualizado correctamente.", "success");
                cerrarModalIconos();
            },
            onError: (errors) => {
                const errorMessage = errors.message || "Ocurrió un error desconocido al intentar eliminar.";
                mostrarNotificacion("Acción no permitida", errorMessage, "error");
            },
            onFinish: () => {
                nodeDataForDeleteConfirmation.value = null;
            }
        });
    } catch (error) {
        console.error("Error al seleccionar el ícono:", error);
        mostrarNotificacion("Error", "No se pudo procesar el ícono seleccionado.", "error");
    }
};

const buscarIconos = async () => {
    let terminoDeBusqueda = terminoBusquedaIcono.value.toLowerCase().trim();

    // Si el campo de búsqueda está vacío, mostramos los sugeridos y terminamos.
    if (terminoDeBusqueda === '') {
        listaIconosEncontrados.value = iconosSugeridos;
        return;
    }

    // Si escribe menos de 3 letras, no hacemos nada (para no saturar)
    if (terminoDeBusqueda.length < 3) {
        return;
    }

    const traduccion = traduccionesIconos[terminoDeBusqueda];
    if (traduccion) {
        terminoDeBusqueda = traduccion;
    }

    cargandoIconos.value = true;
    try {
        const response = await fetch(`https://api.iconify.design/search?query=${terminoDeBusqueda}&limit=48`);
        const data = await response.json();
        listaIconosEncontrados.value = data.icons || [];
    } catch (error) {
        console.error("Error al buscar íconos:", error);
        mostrarNotificacion("Error de red", "No se pudieron buscar los íconos.", "error");
    } finally {
        cargandoIconos.value = false;
    }
};

const onInputBusquedaIcono = () => {
    clearTimeout(debounceTimer);

    if (terminoBusquedaIcono.value.trim() === '') {
        listaIconosEncontrados.value = iconosSugeridos;
        return;
    }

    debounceTimer = setTimeout(() => {
        buscarIconos();
    }, 500);
};


const abrirModalIconos = () => {
    if (!selectedNode.value) {
        return ElMessage.warning('Por favor, seleccione un nodo para asignarle un ícono.');
    }
    terminoBusquedaIcono.value = '';
    listaIconosEncontrados.value = iconosSugeridos;
    esModalIconosVisible.value = true;
};

const cerrarModalIconos = () => {
    esModalIconosVisible.value = false;
};

const page = usePage();

const expandedKeysArray = computed(() => Array.from(expandedNodeIds.value));

const handleNodeExpand = (data, node) => {
    expandedNodeIds.value.add(data.IdTipoRelacion);
    handleNodeSelected(data, node);
};

const handleNodeCollapse = (data, node) => {
    expandedNodeIds.value.delete(data.IdTipoRelacion);
    handleNodeSelected(data, node);
};

const scrollToNode = (nodeId) => {
    nextTick(() => {
        const element = document.getElementById(`tree-node-${nodeId}`);
        if (element) {
            element.scrollIntoView({ behavior: "smooth", block: "center" });
        } else {
            console.warn(`scrollToNode: Elemento con ID="tree-node-${nodeId}" no encontrado para desplazar.`);
        }
    });
};

const expandAncestors = (nodeIdToExpand) => {
    const node = treeRef.value?.getNode(nodeIdToExpand);
    if (node && node.parent) {
        let parentNode = node.parent;
        while (parentNode && parentNode.level > 0) {
            parentNode.expand();
            parentNode = parentNode.parent;
        }
    }
};

const findNodeInTree = (nodes, nodeIdToFind) => {
    if (!nodes || !Array.isArray(nodes)) return null;
    const targetIdStr = String(nodeIdToFind);
    for (const node of nodes) {
        if (String(node.IdTipoRelacion) === targetIdStr) return node;
        if (node.children && node.children.length > 0) {
            const found = findNodeInTree(node.children, targetIdStr);
            if (found) return found;
        }
    }
    return null;
};

const mostrarNotificacion = (titulo, mensaje, tipo = "info", duracion = 5000) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = duracion;
    notificacionVisible.value = true;
};

const mostrarNotificacionError = (titulo, mensaje, tipo = "error",) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = 0;
    notificacionVisible.value = true;
};

const cerrarNotificacion = () => {
    notificacionVisible.value = false;
    if (nodeIdToScrollToAfterNotification.value) {
        selectAndFocusNode(nodeIdToScrollToAfterNotification.value);
        nodeIdToScrollToAfterNotification.value = null;
    }
};

const deepCopy = (data) => JSON.parse(JSON.stringify(data));
const sortNodesAlphabetically = (nodes) => {
    if (!nodes || !Array.isArray(nodes) || nodes.length === 0) return;
    nodes.sort((a, b) => (a.Descripcion || "").localeCompare(b.Descripcion || "", undefined, { sensitivity: "base" }));
    nodes.forEach((node) => { if (node.children && node.children.length) sortNodesAlphabetically(node.children); });
};

const selectAndFocusNode = (nodeId, retries = 0) => {
    const MAX_RETRIES = 10;
    const RETRY_DELAY = 100;
    nextTick(() => {
        if (!treeRef.value) return;
        const node = treeRef.value.getNode(nodeId);
        if (node) {
            expandAncestors(nodeId);
            treeRef.value.setCurrentKey(nodeId);
            selectedNode.value = node.data;
            setTimeout(() => scrollToNode(nodeId), 200);
            nodeIdToSelectAfterInsert.value = null;
            nodeIdToFocus.value = null;
        } else if (retries < MAX_RETRIES) {
            setTimeout(() => selectAndFocusNode(nodeId, retries + 1), RETRY_DELAY);
        }
    });
};

watch(() => page.props.flash?.newNodeId, (newNodeId) => {
    if (newNodeId) {
        nodeIdToSelectAfterInsert.value = newNodeId;
    }
}, { immediate: true });

watch(() => props.treeDataProp, (newVal) => {
    const copiedData = deepCopy(newVal);
    sortNodesAlphabetically(copiedData);
    localTreeData.value = copiedData;

    let idToProcess = nodeIdToSelectAfterInsert.value || nodeIdToFocus.value;
    if (idToProcess) {
        selectAndFocusNode(String(idToProcess));
    }
}, { immediate: true, deep: true });

onMounted(() => {
    if (localTreeData.value && localTreeData.value.length > 0 && !nodeIdToSelectAfterInsert.value) {
        const firstNodeId = localTreeData.value[0].IdTipoRelacion;
        selectAndFocusNode(firstNodeId);
    }
});

const handleNodeSelected = (data, node) => {
    if (esModalVisible.value) {
        ElMessage.info("Hay una operación en curso. Por favor, guarde o cancele.");
        treeRef.value?.setCurrentKey(selectedNode.value?.IdTipoRelacion);
        return;
    }
    selectedNode.value = data;
};

const modalRules = {
    Descripcion: [{ required: true, message: "La descripción de la relación es un dato obligatorio, por lo que no puede quedar en blanco.", trigger: "blur" }],
    Direccionalidad: [{ required: true, message: "La direccionalidad es un dato obligatorio, por lo que no puede quedar en blanco.", trigger: "change" }],
};

const abrirModalParaInsertar = () => {
    modalMode.value = "insertar";
    formModal.value = { Descripcion: "", Direccionalidad: null };
    opcionNivel.value = selectedNode.value ? "mismo" : "raiz";
    esModalVisible.value = true;
    nextTick(() => formModalRef.value?.clearValidate());
};

const abrirModalParaEditar = () => {
    if (!selectedNode.value) {
        return ElMessage.warning("Por favor, seleccione un nodo para editar.");
    }

    if (selectedNode.value.IdTipoRelacion <= 8) {
        return mostrarNotificacion(
            "Acción no permitida",
            "La relación seleccionada no puede ser modificada.",
            "error"
        );
    }

    modalMode.value = "editar";
    nodoEnModal.value = { ...selectedNode.value };
    formModal.value = {
        Descripcion: selectedNode.value.Descripcion,
        Direccionalidad: selectedNode.value.Direccionalidad,
    };
    esModalVisible.value = true;
    nextTick(() => formModalRef.value?.clearValidate());
};

const cerrarModalOperacion = () => {
    esModalVisible.value = false;
    nodoEnModal.value = null;
    formModal.value = { Descripcion: "", Direccionalidad: null };
    modalMode.value = "";
    opcionNivel.value = "mismo";
};

const modalTitle = computed(() => modalMode.value === "editar" ? "Modificar el tipo de relación seleccionado" : "Ingresar un nuevo tipo de relación");

const guardarDesdeModal = async () => {
    if (!formModalRef.value) return;
    const isValid = await formModalRef.value.validate();
    if (!isValid) return;

    const proceedWithSave = () => {
        ElMessageBox.close();
        const onSuccessHandler = (page) => {
            cerrarModalOperacion();
            mostrarNotificacion("Ingreso", "La información ha sido ingresada correctamente.", "success");
        };
        const onErrorHandler = (errors) => {
            mostrarNotificacion("Error", Object.values(errors).flat().join("\n"), "error");
        };

        if (modalMode.value === "editar") {
            const datosUpdate = { Descripcion: formModal.value.Descripcion.trim(), Direccionalidad: formModal.value.Direccionalidad };
            const nodeId = nodoEnModal.value.IdTipoRelacion;
            nodeIdToFocus.value = nodeId;
            router.put(`/tipos-relacion/${nodeId}`, datosUpdate, {
                preserveState: true, preserveScroll: true,
                onSuccess: onSuccessHandler, onError: onErrorHandler,
            });
        } else if (modalMode.value === "insertar") {
            // Se calcula la posición del nuevo nodo
            const calculoNiveles = calcularNivelesParaNuevoNodo(selectedNode.value, opcionNivel.value, props.flatTreeDataProp);
            if (!calculoNiveles) return;

            // CONSTRUIMOS LOS DATOS PARA INSERTAR
            const datosInsert = {
                Descripcion: formModal.value.Descripcion.trim(),
                Direccionalidad: formModal.value.Direccionalidad,
                ...calculoNiveles.niveles,
                RutaIcono: ICONO_POR_DEFECTO // <--- SIEMPRE SE ASIGNA EL PUTO ICONO AQUÍ
            };

            // Se manda la petición POST
            router.post("/tipos-relacion", datosInsert, {
                preserveState: true, preserveScroll: true,
                onSuccess: (page) => {
                    onSuccessHandler(page);
                    const finalNewNodeId = page.props.flash?.newNodeId;
                    if (finalNewNodeId) {
                        nodeIdToScrollToAfterNotification.value = finalNewNodeId;
                    }
                },
                onError: onErrorHandler,
            });
        }
    };

    if (modalMode.value === 'insertar') {
        proceedWithSave();
    } else {
        const nombreTipoRelacion = formModal.value.Descripcion.trim();
        ElMessageBox({
            title: "Confirmar modificación", showConfirmButton: false, showCancelButton: false, customClass: "message-box-diseno-limpio",
            message: h('div', { class: 'custom-message-content' }, [
                h('div', { class: 'body-content' }, [
                    h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle', style: "background-color: #e6a23c;" }, '!')]),
                    h('div', { class: 'text-container' }, [h('p', null, `¿Estás seguro de que deseas guardar los cambios para "${nombreTipoRelacion}"?`)]),
                ]),
                h('div', { class: 'footer-buttons' }, [
                    h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                    h(BotonAceptar, { texto: "Sí, Guardar", onClick: proceedWithSave }),
                ]),
            ]),
        }).catch(() => { });
    }
};


const handleEliminar = () => {
    if (esModalVisible.value) return ElMessage.info("Cierre cualquier operación en curso.");
    if (!selectedNode.value) return ElMessage.warning("Por favor, seleccione un nodo para eliminar.");

    if (selectedNode.value.IdTipoRelacion <= 8) {
        return mostrarNotificacion(
            "Acción no permitida",
            "La relación seleccionada no puede ser eliminada.",
            "error"
        );
    }

    if (selectedNode.value.children && selectedNode.value.children.length > 0) {
        return mostrarNotificacion("Acción no permitida", "No es posible eliminar la relación seleccionada por tener otras relaciones que dependen de ella.", "error");
    }

    nodeDataForDeleteConfirmation.value = { ...selectedNode.value };
    const nombreTipoRelacion = nodeDataForDeleteConfirmation.value.Descripcion;
    const mensaje = `¿Está seguro de eliminar el tipo de relación "${nombreTipoRelacion}"? Esta acción no se puede revertir.`;

    ElMessageBox({
        title: "Confirmar eliminación",
        showConfirmButton: false,
        showCancelButton: false,
        customClass: "message-box-diseno-limpio",
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
    }).catch();
};

const proceedWithDeletion = async () => {
    try {
        ElMessageBox.close();
        if (!nodeDataForDeleteConfirmation.value) return;

        const { IdTipoRelacion, Descripcion } = nodeDataForDeleteConfirmation.value;

        const parentNode = findNodeInTree(localTreeData.value, nodeDataForDeleteConfirmation.value.IdAscendente);

        router.delete(`/tipos-relacion/${IdTipoRelacion}`, {
            preserveScroll: true,
            onSuccess: () => {
                mostrarNotificacion("¡Eliminación Exitosa!", `El elemento "${Descripcion}" ha sido eliminado.`, "success");
                if (parentNode) {
                    nodeIdToFocus.value = parentNode.IdTipoRelacion;
                } else {
                    selectedNode.value = null;
                }
            },
            onError: (error) => {
                mostrarNotificacionError('Aviso', `El tipo de relacion ${Descripcion} no se puede eliminar. Este tipo de relacion está en uso en otra parte del sistema.`, 'success');
            },
            onFinish: () => {
                nodeDataForDeleteConfirmation.value = null;
            }
        });

    } catch (apiError) {
        ElMessageBox.alert('Ocurrió un error al intentar eliminar el registro.', 'Error', { type: 'error' });
    }
};

const MAX_NIVELES = 5;
const calcularNivelesParaNuevoNodo = (nodoReferencia, opcion, todosLosNodos) => {
    const nuevosNiveles = {};
    for (let i = 1; i <= MAX_NIVELES; i++) {
        nuevosNiveles[`Nivel${i}`] = 0;
    }
    if (opcion === "raiz") {
        let maxNivel1Raiz = 0;
        todosLosNodos.forEach((nodo) => {
            const esRaiz = nodo.Nivel1 > 0 && nodo.Nivel2 === 0 && nodo.Nivel3 === 0 && nodo.Nivel4 === 0 && nodo.Nivel5 === 0;
            if (esRaiz) {
                maxNivel1Raiz = Math.max(maxNivel1Raiz, nodo.Nivel1);
            }
        });
        nuevosNiveles["Nivel1"] = maxNivel1Raiz + 1;
        return { niveles: nuevosNiveles };
    }
    if (!nodoReferencia) {
        mostrarNotificacion("Error", "Se necesita un nodo de referencia para esta operación.", "error");
        return null;
    }
    if (opcion === "inferior") {
        let profundidadPadre = 0;
        for (let i = 1; i <= MAX_NIVELES; i++) {
            if (nodoReferencia[`Nivel${i}`] > 0) {
                nuevosNiveles[`Nivel${i}`] = nodoReferencia[`Nivel${i}`];
                profundidadPadre = i;
            } else {
                break;
            }
        }
        const nivelParaSecuencia = profundidadPadre + 1;
        if (nivelParaSecuencia > MAX_NIVELES) {
            mostrarNotificacion("Error", "Profundidad máxima de niveles excedida.", "error");
            return null;
        }
        let maxValorSecuencia = 0;
        todosLosNodos.forEach((nodo) => {
            let esHijoDirecto = true;
            for (let i = 1; i <= profundidadPadre; i++) {
                if (nodo[`Nivel${i}`] !== nuevosNiveles[`Nivel${i}`]) {
                    esHijoDirecto = false;
                    break;
                }
            }
            if (esHijoDirecto) {
                for (let i = nivelParaSecuencia + 1; i <= MAX_NIVELES; i++) {
                    if (nodo[`Nivel${i}`] > 0) {
                        esHijoDirecto = false;
                        break;
                    }
                }
            }
            if (esHijoDirecto) {
                maxValorSecuencia = Math.max(maxValorSecuencia, nodo[`Nivel${nivelParaSecuencia}`] || 0);
            }
        });
        nuevosNiveles[`Nivel${nivelParaSecuencia}`] = maxValorSecuencia + 1;
        return { niveles: nuevosNiveles };
    }
    if (opcion === 'mismo') {
        let profundidadHermano = 0;
        for (let i = 1; i <= MAX_NIVELES; i++) {
            if (nodoReferencia[`Nivel${i}`] > 0) {
                profundidadHermano = i;
            } else {
                break;
            }
        }
        if (profundidadHermano === 1) {
            let maxNivel1Raiz = 0;
            todosLosNodos.forEach((nodo) => {
                const esRaiz = nodo.Nivel1 > 0 && nodo.Nivel2 === 0;
                if (esRaiz) maxNivel1Raiz = Math.max(maxNivel1Raiz, nodo.Nivel1);
            });
            nuevosNiveles["Nivel1"] = maxNivel1Raiz + 1;
            return { niveles: nuevosNiveles };
        }
        for (let i = 1; i < profundidadHermano; i++) {
            nuevosNiveles[`Nivel${i}`] = nodoReferencia[`Nivel${i}`];
        }
        let maxValorSecuencia = 0;
        todosLosNodos.forEach(nodo => {
            let esHermano = true;
            for (let i = 1; i < profundidadHermano; i++) {
                if (nodo[`Nivel${i}`] !== nuevosNiveles[`Nivel${i}`]) {
                    esHermano = false;
                    break;
                }
            }
            if (esHermano) {
                for (let i = profundidadHermano + 1; i <= MAX_NIVELES; i++) {
                    if (nodo[`Nivel${i}`] > 0) {
                        esHermano = false;
                        break;
                    }
                }
            }
            if (esHermano) {
                maxValorSecuencia = Math.max(maxValorSecuencia, nodo[`Nivel${profundidadHermano}`] || 0);
            }
        });
        nuevosNiveles[`Nivel${profundidadHermano}`] = maxValorSecuencia + 1;
        return { niveles: nuevosNiveles };
    }
    return null;
};

const isAccionDependienteDeNodoDeshabilitada = computed(() => !selectedNode.value || esModalVisible.value);

const cerrarDialogo = () => {
    emit('cerrar');
};

</script>

<template>
    <AppLayout title="Tipos de Relación">
        <LayoutCuerpo :usar-app-layout="false" titulo-pag="Tipos de Relación"
            titulo-area="Catálogo de tipos de relaciones taxonómicas">

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
                                <CambiarIconoButton @cambiar-icono="abrirModalIconos" toolPosicion="bottom"
                                    :disabled="isAccionDependienteDeNodoDeshabilitada" />
                                <BotonSalir />
                            </div>
                        </div>
                    </div>
                </template>

                <el-tree v-if="localTreeData && localTreeData.length" ref="treeRef" :data="localTreeData"
                    :props="{ children: 'children', label: 'Descripcion' }" node-key="IdTipoRelacion"
                    :current-node-key="selectedNode?.IdTipoRelacion" :highlight-current="true"
                    :expand-on-click-node="true" :default-expanded-keys="expandedKeysArray"
                    @node-expand="handleNodeExpand" @node-collapse="handleNodeCollapse" @node-click="handleNodeSelected"
                    class="custom-element-tree">
                    <template #default="{ node, data }">
                        <span :id="`tree-node-${data.IdTipoRelacion}`" class="custom-tree-node-content">

                            <img v-if="!data.RutaIcono"
                                src="/storage/images/RERJvyv0qvxOR9of8BRobZjiodN2DK4euvMWNYkZ.png"
                                class="node-icon-wrapper static-icon" alt="ícono por defecto" />

                            <template v-else>
                                <span v-if="data.RutaIcono.startsWith('<svg')" v-html="data.RutaIcono"
                                    class="node-icon-wrapper"></span>
                                <img v-else :src="data.RutaIcono" class="node-icon-wrapper static-icon" alt="ícono">
                            </template>

                            <span>{{ node.label }}</span>
                        </span>
                    </template>
                </el-tree>
                <div v-else class="no-data-message">
                    No hay datos de tipos de relación para mostrar.
                </div>
            </el-card>

        </LayoutCuerpo>

        <Teleport to="body">
            <DialogGeneral v-model="esModalVisible" :bot-cerrar="true" :press-esc="true" @close="cerrarModalOperacion">
                <div class="dialog-header">
                    <h3>{{ modalTitle }}</h3>
                </div>
                <div class="content-wrapper-custom">
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

                            <el-form-item prop="Descripcion" label="Descripción del tipo de relación:">
                                <el-input v-model="formModal.Descripcion" placeholder="Ingrese la descripción" clearable
                                    maxlength="255" show-word-limit />
                            </el-form-item>

                            <el-form-item prop="Direccionalidad" label="Direccionalidad:">
                                <el-select v-model="formModal.Direccionalidad" placeholder="Seleccione una opción"
                                    style="width: 100%;">
                                    <el-option label="1 - Unidireccional" :value="1" />
                                    <el-option label="2 - Reciproca" :value="2" />
                                    <el-option label="3 - No aplica" :value="3" />
                                </el-select>
                            </el-form-item>


                        </el-form>
                    </div>
                </div>
            </DialogGeneral>

            <DialogGeneral v-model="esModalIconosVisible" :bot-cerrar="true" @close="cerrarModalIconos">
                <div class="dialog-header">
                    <h3>Seleccionar Ícono para "{{ selectedNode?.Descripcion }}"</h3>
                </div>
                <div class="dialog-body-container">
                    <el-input v-model="terminoBusquedaIcono" placeholder="Buscar ícono (ej. 'hoja', 'animal', 'flecha')"
                        @input="onInputBusquedaIcono" clearable />
                    <h4 class="icon-section-title">
                        {{ terminoBusquedaIcono.trim() === '' ? 'Íconos Sugeridos' : 'Resultados de la Búsqueda' }}
                    </h4>

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
                @close="cerrarNotificacion" />
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
    background-color: #f5f5f5;
    padding: 20px 24px;
    border-bottom: 1px solid #e4e7ed;
    text-align: left;
    border-radius: 10px;
    margin-bottom: 10px;
}

.content-wrapper-custom {
    background-color: #ffffff;
    padding: 24px;
    border-radius: 10px;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.08);
    /* La sombra que faltaba */
    max-height: 65vh;
    overflow-y: auto;
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

.node-icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2em;
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

.static-icon {
    width: 1.2em;
    height: 1.2em;
    object-fit: contain;
}
</style>