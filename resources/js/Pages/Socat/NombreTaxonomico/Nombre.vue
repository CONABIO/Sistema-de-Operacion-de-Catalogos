<script setup>
import { ref, onMounted, triggerRef, h, computed, onUnmounted } from 'vue';
import { InfoFilled, MessageBox, Setting, HelpFilled, Grid, View } from '@element-plus/icons-vue';
import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
import FormNombre from '@/Pages/Socat/NombreTaxonomico/FormNombre.vue';
import FiltroGrupos from '@/Pages/Socat/NombreTaxonomico/FiltroGrupoTax.vue';
import DialogRelaciones from '@/Pages/Socat/Relaciones/RelacionesTaxonomicas.vue';
import CuerpoGen from '@/Components/Biotica/LayoutCuerpo.vue';
import EditarButton from '@/Components/Biotica/EditarButton.vue';
import { ElMessageBox } from 'element-plus';
import { ElLoading } from 'element-plus';
import AutorTaxon from '../Autores/AutorTaxon.vue';
import Logo from '@/Components/Biotica/LogoCategoria.vue';
import { usePage } from '@inertiajs/vue3';
import usePermisos from '@/composables/usePermisos';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import filtroGrupos from '@/Components/Biotica/Icons/Conectado.vue';
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import { showConfirmMessage } from '@/Composables/mensajeConfirm';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrableImg.vue";

const { permisos } = usePermisos();

const page = usePage();
const authUser = page.props.auth.user || [];


const props = defineProps({
  gruposTax: {
    type: Object,
    required: true,
  },
  categoriasTax: {
    type: Object,
    required: true,
  }
});

const totalRegNom = ref(0);

const columnasDefinidas = ref([
  {
    prop: 'TipoRelacion', label: 'Tipo Relación', minWidth: '120', sortable: true,
    align: 'left', tipo: 'imagenTexto', filtrable: true
  },
  {
    prop: 'Nombrecompleto', label: 'Nombre Completo', minWidth: '250', sortable: true,
    align: 'left', tipo: 'imagenTexto', filtrable: true
  },
  {
    prop: 'Biblio', label: 'Ref.', minWidth: '55', sortable: false, align: 'center',
    tipo: 'imagenTexto', filtrable: false
  }
]);

const opcionesFiltroNomenclatura = ref([
  { label: 'TipoRelación', value: 'TipoRelacion' },
  { label: 'NombreCompleto', value: 'Nombrecompleto' }
]);

const opcionesPaginadoTabla = ref({ 'page-sizes': [2, 5, 10], 'page-size': 2 });

const totalRegRef = ref(0);

const columnasDefRef = ref([
  {
    prop: 'Cita', label: 'Cita completa', minWidth: '250', sortable: true,
    align: 'left', tipo: 'textarea', filtrable: true
  },
]);

const opcionesFiltroRef = ref([
  { label: 'Cita', value: 'Cita' }
]);

const totalItems = ref(0);
const currentPage = ref(1);
const itemsPerPage = ref(150);

const tree = ref(null);
const ascendantsTree = ref(null);

const treeDataAscendentes = ref([]);

const selectedNode = ref([]);
const menuPosition = ref({ x: 0, y: 0 });
const isMenuVisible = ref(false);

const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);

const dialogFormVisibleAlta = ref(false);
const dialogFormVisibleAscendentes = ref(false);
const taxonAct = ref([]);
const data = ref([]);
const categ = ref(null);
const catalogos = ref('');
const grupos = ref('');
const idsGrupos = ref('');
const mostrar = ref(false);
const dialogFormVisibleCat = ref(false);
const dialogFormVisibleRel = ref(false);
const dialogFormVisibleRelTax = ref(false);
const totalReg = ref(0);
const paginas = ref('');
const taxAct = ref([]);
const taxMov = ref([]);
const defaultProps = {
  children: 'children',
  label: 'label'
};

const selectedNodeKey = ref(null);
const filterText = ref('');
const catego = ref('');
const tablaNomenclatura = ref([]);
const tablaReferencias = ref([]);

const currentPageNomenclatura = ref(1);
const pageSizeNomenclatura = ref(2);


const scrollbarHeight = ref('450px');
const dialogWidth = ref('35%');

const updateLayout = () => {
  if (window.innerHeight < 820) {
    scrollbarHeight.value = '800px';
  } else {
    scrollbarHeight.value = '450px';
  }

  if (window.innerWidth <= 1440) {
    dialogWidth.value = '55%';
  } else {
    dialogWidth.value = '40%';
  }
};

onMounted(() => {
  dialogFormVisibleCat.value = true;
  console.log(authUser);

  updateLayout();
  window.addEventListener('resize', updateLayout);
});

onUnmounted(() => {
  window.removeEventListener('resize', updateLayout);
});


const datosPaginadosNomenclatura = computed(() => {
  if (!tablaNomenclatura.value || tablaNomenclatura.value.length === 0) {
    return [];
  }

  const inicio = (currentPageNomenclatura.value - 1) * pageSizeNomenclatura.value;
  const fin = inicio + pageSizeNomenclatura.value;

  return tablaNomenclatura.value.slice(inicio, fin);
});


const opcionesPaginadoTablas = { 'page-size': 2, 'page-sizes': [2, 5, 10, 20] };


const filtro_Catalogos = () => {
  dialogFormVisibleCat.value = true;
};

const hasPermisos = (etiqueta, modulo) => {

  const permiso = permisos.find(item => item.NombreModulo === etiqueta);

  return permiso[modulo];
};

const openDialog = (nodeData) => {
  console.log(nodeData);
  emit('reset-form');
  taxonAct.value = nodeData;
  dialogFormVisibleAlta.value = true; 
};

const emit = defineEmits(['reset-form']);
const resetFormNombre = () => {
  emit('reset-form');
}

const closeDialog = () => {
  dialogFormVisibleAlta.value = false;
  dialogFormVisibleRel.value = false;
};

const cerrarDialog = (valor) => {
  dialogFormVisibleCat.value = valor;
};

const recibeGrupos = async (data) => {
  catalogos.value = data['catalogos'];
  grupos.value = data['grupos'];
  idsGrupos.value = data['ids'];

  if (categ.value === '') {
    open("Se debe seleccionar una categoría taxonómica.");
  } else {
    let categ = [];
    categ.push(catego.value)
    handleChange(categ);
  }
};

const recibeTaxMod = async (res) => {

  const index = data.value.findIndex(nombre => nombre.id === taxonAct.value.id);

  for (const node of data.value) {
    if (node.id === res.id) {
      Object.assign(node, res);
      return true;
    }

    if (node.children && node.children.length > 0) {
      const found = await updateChildNode(node.children, res);
      if (found) break;
    }
  }
};

const updateChildNode = async (children, res) => {
  for (const child of children) {
    if (child.id === res.id) {
      Object.assign(child, res);
      return true;
    }
    if (child.children && child.children.length > 0) {
      const found = await updateChildNode(child.children, res);
      if (found) return true;
    }
  }
  return false;
};

const recibeTaxNuevo = async (res) => {

  const index = data.value.findIndex(nombre => nombre.id === taxonAct.value.id);

  if (index !== -1) {
    if (!data.value[index].children) {
      data.value[index].children = [];
    }
    data.value[index].children.push(res);
  }
};

const recibeTaxBaja = async (res) => {

  const index = data.value.findIndex(nombre => nombre.id === taxonAct.value.id);

  let found = false;

  for (let i = 0; i < data.value.length; i++) {
    if (String(data.value[i].id) === String(res.Id)) {
      children.splice(i, 1);
      return true;
    }

    if (data.value[i].children && data.value[i].children.length > 0) {

      found = deleteChildNode(data.value[i].children, res);
      if (found) break;
    }
  }
}

const deleteChildNode = async (children, res) => {
  for (let i = 0; i < children.length; i++) {
    if (String(children[i].id) === String(res.Id)) {
      children.splice(i, 1);
      return true;
    }
  }
  return false;
};

onMounted(() => {
  dialogFormVisibleCat.value = true;
  console.log(authUser);
});

const open = (mensaje) => {
  ElMessageBox.alert(mensaje, 'Nombres taxonómicos', {
    confirmButtonText: 'OK',
  });
}

const handleChange = async (value) => {

  if (value != undefined) {
    filterText.value = "";
    mostrar.value = false;
    catego.value = value[0];

    if (idsGrupos.value != '') {
      const params = {
        categ: value[0],
        catalog: idsGrupos.value
      };

      const loading = ElLoading.service({
        lock: true,
        text: "Loading",
        spinner: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="none" d="M0 0h200v200H0z"></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M70 95.5V112m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5L92 57.3M33.6 91 48 82.7m0-25.5L33.6 49m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;-120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M130 155.5V172m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5-14.3 8.3M93.6 151l14.3-8.3m0-25.4L93.6 109m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>`,
        backgroud: 'rgba(255,255,255,0.85)',
      });

      const response = await axios.get('/cargar-nomArb', { params });

      if (response.status === 200) {

        data.value = response.data[0];
        totalItems.value = response.data[1].total;
        paginas.value = response.data[1].last_page;
      }
      else {
        console.log("Se presentó un error en la recuperación de los datos");
      }
      loading.close();
    }
  }
}

const filterNode = async (value) => {
  mostrar.value = false;

  const loading = ElLoading.service({
    lock: true,
    text: "Loading",
    spinner: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="none" d="M0 0h200v200H0z"></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M70 95.5V112m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5L92 57.3M33.6 91 48 82.7m0-25.5L33.6 49m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;-120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M130 155.5V172m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5-14.3 8.3M93.6 151l14.3-8.3m0-25.4L93.6 109m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>`,
    backgroud: 'rgba(255,255,255,0.85)',
  });

  if (value != '' && idsGrupos.value != '' && categ.value != null) {
    const params = {
      categ: categ.value[0],
      catalog: idsGrupos.value,
      taxon: value
    };

    const response = await axios.get('/cargar-nomArb',
      { params });

    if (response.status === 200) {
      data.value = response.data[0];
      totalReg.value = response.data[1].total;
      paginas.value = response.data[1].last_page;
      loading.close();
    }
  }

  loading.close();
}

const mostrarNotificacion = (
  titulo,
  mensaje,
  tipo = "warning",
  duracion = 5000,
  dangerouslyUseHTML = false
) => {
  console.log("Entre a mostrar la notificación");
  notificacionTitulo.value = titulo;
  notificacionMensaje.value = mensaje;
  notificacionTipo.value = tipo;
  notificacionDuracion.value = duracion;
  notificacionVisible.value = true;
};

const cerrarNotificacion = () => {
  notificacionVisible.value = false;
};

const expande = async (draggingNode, nodeData, nodeComponent) => {

  isMenuVisible.value = false;
  mostrar.value = true;
  taxonAct.value = draggingNode;

  if (draggingNode.children.length === 0) {
    const loading = ElLoading.service({
      lock: true,
      text: "Loading",
      spinner: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="none" d="M0 0h200v200H0z"></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M70 95.5V112m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5L92 57.3M33.6 91 48 82.7m0-25.5L33.6 49m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;-120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M130 155.5V172m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5-14.3 8.3M93.6 151l14.3-8.3m0-25.4L93.6 109m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>`,
      backgroud: 'rgba(255,255,255,0.85)',
    });

    const response = await axios.get(`/cargar-hijos-nomArb/${draggingNode.id}`);

    if (response.status === 200) {
      if (draggingNode.children.length === 0) {
        for (let i = 0; i < response.data[0].length; i++) {
          draggingNode.children.push(response.data[0][i]);
        }
      }

      const node = tree.value.getNode(draggingNode);
      node.expanded = true;

    }
    loading.close();
  }

  tablaNomenclatura.value = draggingNode.relaciones;
  totalRegNom.value = draggingNode.relaciones.length;
  tablaReferencias.value = draggingNode.referencias;
  totalRegRef.value = draggingNode.referencias.length
  selectedNodeKey.value = draggingNode.id;

}

const proceder = () => {
  console.log("Pase el movimeinto");
}

const cancelar = () => {
  console.log("Evite el movimeinto");
}

const mover = async (node) => {

  if (taxMov.value.length === 0) {
    try {

      const result = await showConfirmMessage({
        title: 'Atención',
        message: '¿Está seguro de mover este taxón?',
        icon: '!'
      });

      if (!result) {
        return;
      }

      node.data.customClass = 'highlight-node';
      taxMov.value = node;

      triggerRef(data);

      await mostrarNotificacion(
        "Aviso",
        "El taxón será reasignado.",
        "info",
        5000
      );

    } catch (error) {
      await ElMessageBox.alert("Acción cancelada", {
        confirmButtonText: "OK",
        type: 'info'
      });
    }
  } else {
    if (taxMov.value.data.completo.scat === undefined) {
      await mostrarNotificacion(
        "Error",
        `El taxón ${taxMov.value.data.completo.TaxonCompleto} no tiene un registro en la tabla scat 1`,
        "error",
        5000
      );
      return;
    } else if (typeof node.data.completo.scat === 'object' && Object.keys(node.data.completo.scat).length <= 0) {
      await mostrarNotificacion(
        "Error",
        `El taxón ${node.data.completo.TaxonCompleto} no tiene un registro en la tabla scat 2`,
        "error",
        5000
      );
      return;
    }
    if (taxMov.value.data.id === node.data.id) {
      taxMov.value = [];
      await mostrarNotificacion(
        "Aviso",
        "Se cancelará el movimiento del taxón",
        "info",
        5000
      );
      delete node.data.customClass;
      return;
    }
    if (taxMov.value.data.completo.scat.grupo_scat.GrupoSCAT === node.data.completo.scat.grupo_scat.GrupoSCAT) {
      if (node.data.completo.categoria.IdNivel1 === (taxMov.value.data.completo.categoria.IdNivel1 - 1)) {
        const params = {
          estatusInicio: taxMov.value.data.completo.Estatus,
          nomAct: taxMov.value.data.id
        };

        try {
          const response = await axios.get('/valCamEstatus', params);
          switch (taxMov.value.data.completo.Estatus) {
            case 1:
              switch (node.data.completo.Estatus) {
                case 1:
                  await moverTaxon(
                    taxMov.value.data.completo.IdNombre,
                    node.data.completo.IdNombre,
                    taxMov.value,
                    node
                  );
                  break;
                case 2:
                  if (response.data != 1) {
                    await mostrarNotificacion(
                      "Error",
                      `Está intentando mover un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,
                      "error",
                      5000
                    );
                  } else {
                    await moverTaxon(
                      taxonMov.value.data.completo.IdNombre,
                      node.data.completo.IdNombre,
                      taxMov.value,
                      node
                    );
                  }
                  break;
                default:
                  await mostrarNotificacion(
                    "Error",
                    `Está intentando mover un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,
                    "error",
                    5000
                  );
                  break;
              }
              break;
            case 2:
              switch (node.data.completo.Estatus) {
                case 2:
                  await moverTaxon(
                    taxMov.value.data.completo.IdNombre,
                    node.data.completo.IdNombre,
                    taxMov.value,
                    node
                  );
                  break;
                case 1:
                  if (response.data != 1) {
                    await mostrarNotificacion(
                      "Error",
                      `Está intentando mover a un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,
                      "error",
                      5000
                    );
                  } else {
                    await moverTaxon(
                      taxMov.value.data.completo.IdNombre,
                      node.data.completo.IdNombre,
                      taxMov,
                      node
                    );
                  }
                  break;
                default:
                  await mostrarNotificacion(
                    "Error",
                    `Está intentando mover un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,
                    "error",
                    5000
                  );
              }
              break;
            case 6:
              switch (node.data.completo.Estatus) {
                case 6:
                case -9:
                  await moverTaxon(
                    taxMov.data.completo.IdNombre,
                    node.data.completo.IdNombre,
                    taxMov.value,
                    node);
                  break;
                case 1:
                case 2:
                  if (response.data != 1) {
                    await mostrarNotificacion(
                      "Error",
                      `Está intentando mover un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,
                      "error",
                      5000
                    );
                  } else {
                    await moverTaxon(
                      taxMov.value.data.completo.IdNombre,
                      node.data.completo.IdNombre,
                      taxMov.value,
                      node);
                  }
              }
              break;
            case -9:
              switch (node.data.completo.Estatus) {
                case 6:
                  await moverTaxon(
                    taxMov.data.completo.IdNombre,
                    node.data.completo.IdNombre,
                    taxMov.value,
                    node);
                  break;
                case -9:
                  await mostrarNotificacion(
                    "Error",
                    `Está intentando mover un taxón con estatus ${taxMov.data.estatus} a un taxón con estatus ${node.data.estatus}`,
                    "error",
                    5000
                  );
                  break;
                case 1:
                case 2:
                  if (response.data != 1) {
                    await mostrarNotificacion(
                      "Error",
                      `Está intentando mover un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,
                      "error",
                      5000
                    );
                  } else {
                    await moverTaxon(
                      taxMov.value.data.completo.IdNombre,
                      node.data.completo.IdNombre,
                      taxMov.value,
                      node
                    );
                  }
                  break;
              }
              break;
          }
        } catch (error) {
          console.error('Error al validar estatus:', error);
          ElMessageBox.error('Error al validar el estatus del taxón');
        }
      } else {
        await mostrarNotificacion(
          "Error",
          `Está intentando mover el taxón a una categoria no permitida.`,
          "error",
          5000
        );
      }
    } else {
      await mostrarNotificacion(
        "Error",
        `El taxón al que quiere asignar pertenece a un grupo taxonómico diferente`,
        "error",
        5000
      );
    }
  }
}

const manejarEliminarRel = (item) => {
  
  const procederConEliminacion = async () => {

    try {
      ElMessageBox.close();

      const response = await axios.delete('/elimina-RelacionesTax', { data: {relCompleta: item.TipoRelacion.relCompleta, 
                                                                              taxAct: props.taxonAct.id}});
      
      tablaNomenclatura.value = response.data;

      mostrarNotificacion('Eliminación Exitosa', `La relación de: ${item.TipoRelacion.texto} fue eliminado correctamente.`, 'success');
    } catch (apiError) {
      mostrarNotificacionError('Aviso', `La relación de: ${item.TipoRelacion.texto} no se puede eliminar.`, 'success');
    }
  };
  const cancelarEliminacion = () => {
    ElMessageBox.close();
  };

  const contBiblio = item?.Biblio && item.Biblio.contBiblio ? item.Biblio.contBiblio : 0;

  const mensaje = ` La relación de: ${item.TipoRelacion.texto}, que quiere eliminar tiene ${contBiblio} referencia(s) asociadas(s). ¿Realmente desea realizarlo?. Esta acción no se puede revertir`;
  
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
};

const moverTaxon = async (taxMover, taxRecb, nodoMov, nodoRecb) => {

  if (nodoMov.data.completo.padre.IdNombre === nodoRecb.data.completo.IdNombre) {
    await mostrarNotificacion(
      "Error",
      'El taxón al que quiere asignar es el mismo del que parte selccione uno diferente',
      "error",
      5000
    );
    return;
  }

  try {
    const result = await showConfirmMessage({
      title: 'Atención',
      message: `¿Está seguro de mover el(la) ${nodoMov.data.completo.NombreCategoriaTaxonomica} 
                  ${nodoMov.data.completo.NombreCompleto} y que sea asignado a el(la) 
                  ${nodoRecb.data.completo.NombreCategoriaTaxonomica} ${nodoRecb.data.completo.NombreCompleto}?`,
      icon: '!'
    });

    if (!result) {
      return;
    }

    const requestData = {
      taxonMover: taxMover,
      taxonRecibir: taxRecb,
      categorias: catego.value,
      grupos: idsGrupos.value,
      alias: authUser.Alias
    };

    const loading = ElLoading.service({
      lock: true,
      text: "Loading",
      spinner: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="none" d="M0 0h200v200H0z"></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M70 95.5V112m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5L92 57.3M33.6 91 48 82.7m0-25.5L33.6 49m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;-120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M130 155.5V172m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5-14.3 8.3M93.6 151l14.3-8.3m0-25.4L93.6 109m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>`,
      backgroud: 'rgba(255,255,255,0.85)',
    });

    const response = await axios.put('/mueveTaxones', requestData);

    if (response.status === 200) {
      await mostrarNotificacion(
        "Aviso",
        'El taxón sea reasignado exitosamente',
        "error",
        5000
      );

      const params = {
        categ: catego.value,
        catalog: idsGrupos.value
      }

      const resp = await axios.get('/cargar-nomArb', { params: params });

      console.log('Esta es respuesta despues del get: ', resp);

      data.value = resp.data[0];
      totalReg.value = resp.data[1].total;
      paginas.value = resp.data[1].last_page;

    }
    else {
      await mostrarNotificacion(
        "Aviso",
        'El taxón que intenta mover no se pudo reasignar',
        "Error",
        5000
      );
    }

    loading.close();


  } catch (error) {
    console.log(error);
    console.log('Operación cancelada por el usuario');
  }

}

const handleNodeRightClick = (event, data, node) => {

  event.preventDefault(); 

  tree.value.setCurrentKey(data.id);

  expande(data, node)

  taxAct.value = data;
  selectedNode.value = data;
  menuPosition.value = {
    x: event.clientX,
    y: event.clientY
  };
  isMenuVisible.value = true;

  document.addEventListener('click', closeMenu);
  document.addEventListener('keydown', handleEscKey);
};

const handleMenuClick = (action) => {
  isMenuVisible.value = false;
  console.log('Acción seleccionada:', action);
};

const closeMenu = () => {
  isMenuVisible.value = false;
  document.removeEventListener('click', closeMenu);
  document.removeEventListener('keydown', handleEscKey);
};

const handleEscKey = (event) => {
  if (event.key === 'Escape' || event.key === 'Esc') {
    closeMenu();
  }
};

const handlePageChange = (page) => {
  console.log("Este es el valor de page:", page);
  currentPage.value = page;
  fetchFilteredData();
};

const fetchFilteredData = async () => {
  const params = {
    categ: catego.value,
    catalog: idsGrupos.value,
    page: currentPage.value
  };
  const loading = ElLoading.service({
    lock: true,
    text: "Loading",
    spinner: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="none" d="M0 0h200v200H0z"></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M70 95.5V112m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5L92 57.3M33.6 91 48 82.7m0-25.5L33.6 49m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;-120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M130 155.5V172m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5-14.3 8.3M93.6 151l14.3-8.3m0-25.4L93.6 109m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>`,
    backgroud: 'rgba(255,255,255,0.85)',
  });

  const response = await axios.get("/cargar-nomArb", { params });
  if (response.status === 200) {
    data.value = response.data[0];
  }

  loading.close();
};

const abre_Relaciones = () => {
  dialogFormVisibleRel.value = true;
}


const showAscendants = async () => {
  if (!taxonAct.value?.completo?.Ascendentes) {
    ElMessageBox.alert('No hay información de ascendentes para el taxón seleccionado.', 'Aviso', { confirmButtonText: 'OK' });
    return;
  }
  const ascendantsString = taxonAct.value.completo.Ascendentes;
  const ascendantIds = ascendantsString.split(',').map(id => id.trim()).filter(Boolean);
  if (ascendantIds.length === 0) {
    ElMessageBox.alert('No se encontraron IDs de ascendentes válidos.', 'Aviso', { confirmButtonText: 'OK' });
    return;
  }

  const loading = ElLoading.service({
    lock: true,
    text: 'Cargando ascendentes...',
    background: 'rgba(0, 0, 0, 0.7)',
  });

  try {
    const response = await axios.post('/cargar-ascendentes', { ids: ascendantIds });
    if (response.status === 200 && Array.isArray(response.data)) {
      const ascendantTaxa = response.data;
      let nestedTree = [];
      if (ascendantTaxa.length > 0) {
        nestedTree.push(ascendantTaxa[0]);
        let currentNode = nestedTree[0];
        for (let i = 1; i < ascendantTaxa.length; i++) {
          const nextNode = ascendantTaxa[i];
          currentNode.children = [nextNode];
          currentNode = nextNode; 
        }
      }

      treeDataAscendentes.value = nestedTree; 
      dialogFormVisibleAscendentes.value = true; 
    } else {
      ElMessageBox.alert('La respuesta del servidor no fue válida.', 'Error', { confirmButtonText: 'OK' });
    }
  } catch (error) {
    console.error("Error al cargar los ascendentes:", error);
    ElMessageBox.alert('Ocurrió un error al cargar los ascendentes.', 'Error de Conexión', { confirmButtonText: 'OK' });
  } finally {
    loading.close(); 
  }
};



const closeAscendantsDialog = () => {
  dialogFormVisibleAscendentes.value = false;
};

</script>




<template>
  <CuerpoGen :tituloPag="'Nombre_Taxón'" :tituloArea="'Catálogo de nombres taxonómicos'">
    <el-container class="main-layout-container-fixed">
      <el-header class="main-header-override">
        <div>
          <el-row :gutter="16">
            <el-col :xs="24" :sm="12" :md="7" class="form-item-col">
              <span>Ir a:</span>
              <el-input clearable placeholder="" v-model="filterText" @change="filterNode" style="height: 28px;"
                size="small">
              </el-input>
            </el-col>

            <el-col :xs="24" :sm="12" :md="5" class="form-item-col">
              <span class="block">Nivel taxonómico</span>
              <el-cascader :options="categoriasTax" clearable filterable v-model="categ" placeholder="Nivel taxonómico"
                @change="handleChange" popper-class="z-index-fix">

                <template #default="{ data }">
                  <span style="display: inline-flex; align-items: center;">
                    <img :src="data.RutaIcono" alt="" style="width: 16px; height: 16px; margin-right: 6px;" />
                    <span>{{ data.label }}</span>
                  </span>
                </template>

              </el-cascader>
            </el-col>

            <el-col :xs="24" :md="12" class="form-item-col">
              <el-row :gutter="10">
                <el-col :xs="24" :sm="11">
                  <div style="display: flex; flex-direction: column;">
                    <span class="demo-input-label" style="margin-bottom: 4px;">
                      Catálogo(s)
                    </span>
                    <el-input type="textarea" :rows="2" placeholder="Catálogos" v-model="catalogos" :disabled="true" />
                  </div>
                </el-col>

                <el-col :xs="24" :sm="11">
                  <div style="display: flex; flex-direction: column;">
                    <span class="demo-input-label" style="margin-bottom: 4px;">
                      Grupo SCAT
                    </span>
                    <el-input type="textarea" placeholder="Grupo SCAT" v-model="grupos" :disabled="true" />
                  </div>
                </el-col>

                <el-col :xs="24" :sm="2">
                  <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
                    <el-tooltip effect="dark" content="Selección Catálogo de Grupos taxonómicos" placement="bottom">
                      <el-button @click="filtro_Catalogos()" type="primary" circle>
                        <el-icon>
                          <filtroGrupos />
                        </el-icon>
                      </el-button>
                    </el-tooltip>
                  </div>
                </el-col>
              </el-row>
            </el-col>

          </el-row>
        </div>

        <div class="content-wrapper">
          <el-container class="main-content-container">
            <el-aside width="600px" class="aside-tree">
              <div class="tree-container">
                <el-scrollbar height="470px">
                  <el-tree class="filter-tree" :data="data" node-key="id" @node-click="expande"
                    :expand-on-click-node="true" :filter-node-method="filterNode" :draggable="false"
                    empty-text='Sin datos que mostrar' ref="tree" :highlight-current="true"
                    :current-node-key="selectedNodeKey" :props="defaultProps" @node-contextmenu="handleNodeRightClick">
                    <template #default="{ node }">
                      <div class="tree-node-wrapper">
                        <Logo class="tree-node-logo" :rutaCategoria="node.data.completo.categoria.RutaIcono" />
                        <el-tooltip content="Información">
                          <el-icon @click.prevent="openDialog(node.data)">
                            <InfoFilled />
                          </el-icon>
                        </el-tooltip>
                        <div v-if="hasPermisos('MnuNomCientifico', 'Cambios')">
                          <el-tooltip class="item" effect="dark" content="Mover" placement="bottom">
                            <span :style="{ color: node.color }" :id="`node-${node.id}`">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-diagram-3-fill" viewBox="0 0 16 16" @click="mover(node)">
                                <path fill-rule="evenodd"
                                  d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5zM0 11.5A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z" />
                              </svg>
                            </span>
                          </el-tooltip>
                        </div>
                        <span class="tree-node-label"
                          :class="{ 'highlight-node': node.data.customClass === 'highlight-node' }">
                          {{ node.label }}
                        </span>
                      </div>
                    </template>
                  </el-tree>
                </el-scrollbar>
              </div>
              <div>
                <el-menu ref="contextMenu" class="context-menu"
                  :style="{ top: menuPosition.y + 'px', left: menuPosition.x + 'px' }" v-if="isMenuVisible">
                  <el-menu-item class="item" @click="abre_Relaciones()">
                    <el-icon>
                      <HelpFilled />
                    </el-icon>
                    <span>Relaciones taxonómicas</span>
                  </el-menu-item>
                  <el-menu-item class="item">
                    <el-icon>
                      <Grid />
                    </el-icon>
                    <span>Catálogos asociados</span>
                  </el-menu-item>
                  <el-menu-item class="item" @click="showAscendants">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                      class="bi bi-bar-chart-steps" viewBox="0 0 16 16">
                      <path
                        d="M.5 0a.5.5 0 0 1 .5.5v15a.5.5 0 0 1-1 0V.5A.5.5 0 0 1 .5 0M2 1.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-4a.5.5 0 0 1-.5-.5zm2 4a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm2 4a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-6a.5.5 0 0 1-.5-.5zm2 4a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5z" />
                    </svg>
                    <span>Ascendentes</span>
                  </el-menu-item>
                </el-menu>
              </div>
            </el-aside>
            <el-container class="details-container" style="height: 470px;">
              <el-scrollbar :height="scrollbarHeight">
                <el-header class="details-header">
                  <div class="details-title" style="margin-bottom: 20px;">
                    <img v-if="taxonAct?.completo?.categoria?.RutaIcono" :src="taxonAct?.completo?.categoria?.RutaIcono"
                      class="details-title-icon">
                    <span class="details-title-text">
                      {{ taxonAct?.completo?.NombreCompleto }} {{ taxonAct?.completo?.NombreAutoridad }}
                    </span>
                  </div>
                </el-header>
                <el-main class="details-main" style="margin-top: -40px;">
                  <div class="table-section">
                    <span class="demo-input-label" style=" font-weight: bold;">Relaciones nomenclaturales</span>
                    <TablaFiltrable :columnas="columnasDefinidas" :datos="datosPaginadosNomenclatura"
                      :opciones-filtro="opcionesFiltroNomenclatura"
                      @eliminar-item = "manejarEliminarRel">
                    </TablaFiltrable>

                    <div v-if="totalRegNom > pageSizeNomenclatura"
                      style="display: flex; justify-content: center; padding-top: 10px;">
                      <el-pagination small background layout="prev, pager, next, total" :total="totalRegNom"
                        :page-size="pageSizeNomenclatura" v-model:current-page="currentPageNomenclatura" />
                    </div>
                  </div>

                  <br>
                  <div class="table-section">
                    <span class="demo-input-label" style=" font-weight: bold;">Referencias asocidas</span>
                    <TablaFiltrable :columnas="columnasDefRef" v-model:datos="tablaReferencias"
                      v-model:total-items="totalRegRef" :opciones-filtro="opcionesFiltroRef" :items-por-pagina="2">
                    </TablaFiltrable>
                  </div>
                </el-main>
              </el-scrollbar>
            </el-container>
          </el-container>
        </div>

        <el-footer class="main-pagination-footer">
          <div class="pagination-footer">
            <div v-if="totalItems > 0" class="pagination-container-wrapper">
              <el-pagination :current-page="currentPage" :page-size="itemsPerPage" :total="totalItems"
                @current-change="handlePageChange" layout="prev, pager, next, total" background
                class="main-pagination-style">
              </el-pagination>
            </div>
          </div>
        </el-footer>
      </el-header>
    </el-container>

    <DialogForm v-model="dialogFormVisibleAscendentes" @close="closeAscendantsDialog" :botCerrar="true" :pressEsc="true"
      custom-class="dialog-ascendentes-diseno"> 
      <div class="dialog-header-custom">
        <h3>Ascendentes del taxón</h3>
      </div>
      <div class="content-wrapper-custom">
        <el-tree class="filter-tree" :data="treeDataAscendentes" node-key="id" @node-click="expande"
          :expand-on-click-node="true" :filter-node-method="filterNode" :draggable="false"
          empty-text='Sin datos que mostrar' ref="ascendantsTree" :highlight-current="true"
          :current-node-key="selectedNodeKey" :props="defaultProps" @node-contextmenu="handleNodeRightClick"
          default-expand-all>
          <template #default="{ node }">
            <div class="tree-node-wrapper">
              <Logo class="tree-node-logo" :rutaCategoria="node.data.completo.categoria.RutaIcono" />
              <span class="tree-node-label">
                {{ node.label }}
              </span>
            </div>
          </template>
        </el-tree>
      </div>
    </DialogForm>

    <DialogForm v-model="dialogFormVisibleCat" :botCerrar="false" :pressEsc="false" custom-class="responsive-dialog"
      :width="dialogWidth">
      <FiltroGrupos :grupos="gruposTax" @cerrar="cerrarDialog" @regresaGrupos="recibeGrupos" />
    </DialogForm>

    <DialogForm v-model="dialogFormVisibleAlta" @close="closeDialog" 
                @reset-form="resetFormNombre" :botCerrar="true"
                :pressEsc="true" custom-class="responsive-dialog">
      <FormNombre :taxonAct="taxonAct" :paginaActual="1" :categoria="catego.value" 
                  :catalogos="idsGrupos.value" :active-tab.sync="activeTab" 
        @cerrar="closeDialog" @regresaTaxMod="recibeTaxMod" @resultadoAlta="recibeTaxNuevo"
        @resultadoBaja="recibeTaxBaja"/>
    </DialogForm>

    <DialogForm v-model="dialogFormVisibleRel" :botCerrar="true" :pressEsc="true" :width="'83%'"
      custom-class="responsive-dialog relations-dialog">
      <DialogRelaciones :taxonAct="taxonAct" :gruposTax="gruposTax" :categoriasTax="categoriasTax"
        :catalogPadre="catalogos" :gruposPadre="grupos" :idsGruposPadre="idsGrupos" @cerrar="closeDialog">
      </DialogRelaciones>
    </DialogForm>

    <Teleport to="body">
      <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
        :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
        @close="cerrarNotificacion" />
    </Teleport>
  </CuerpoGen>
</template>

<style scoped>
:deep(.z-index-fix) {
  z-index: 3000 !important;
}

.tree-container {
  flex: 1;
  overflow: auto;
  min-height: 0;
}

.el-tree {
  min-width: fit-content;
  width: 100%;
  padding-bottom: 25px;
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

.context-menu {
  position: absolute;
  z-index: 1000;
  background-color: white;
  border: 1px solid #dcdfe6;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  padding: 5px 0;
  border-radius: 4px;
  min-width: 180px;
}

:deep(.el-tree-node.is-current > .el-tree-node__content) {
  background-color: rgb(203, 233, 200) !important;
  color: #0d6efd !important;
}

:deep(.highlight-node) {
  color: #a52f2f !important;
}

.form-item-col {
  margin-bottom: 10px;
}

.icono {
  margin-right: 8px;
}

.pagination-footer {
  padding-top: 15px;
  flex-shrink: 0;
}

.main-header-override {
  height: 100% !important;
  padding: 16px;
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.main-layout-container {
  display: flex;
  flex-direction: column;
  max-height: 100px;
  height: 20%;
}

.content-wrapper {
  flex-grow: 1;
  margin-top: 16px;
  overflow: hidden;
  min-height: 0;
}


@media (min-width: 992px) {
  .content-wrapper {
    max-height: 500px;
  }

  .main-content-container {
    flex-direction: row;
  }

  .aside-tree {
    width: 600px !important;
    background-color: rgb(238, 241, 246);
    height: 470px;
    overflow: auto;
    display: flex;
    flex-direction: column;
    border: 1px solid #e4e7ed;
    border-radius: 4px;
  }

  .details-container {
    flex-grow: 1;
    padding-left: 20px;
    display: flex;
    flex-direction: column;
    height: 720px;
  }

  .el-scrollbar {
    height: 100%;
  }
}

.table-section {
  overflow-x: auto;
}

@media (max-width: 991px) {
  .main-header-override {
    height: 100% !important;
    overflow-y: auto !important;
    padding-bottom: 20px;
  }

  .content-wrapper {
    flex-grow: 0;
    min-height: auto;
  }

  .main-content-container {
    height: 100%;
  }

  .aside-tree {
    width: 100% !important;
    background-color: rgb(238, 241, 246);
    margin-bottom: 20px;
    border: 1px solid #e4e7ed;
    border-radius: 4px;
    height: auto;
  }

  .details-container {
    width: 100%;
    padding-left: 0;
    height: auto;
  }

  .details-main {
    overflow-y: visible;
  }

  .aside-tree .el-scrollbar {
    height: 45vh !important;
  }

  .details-container .el-scrollbar {
    height: auto !important;
  }

  .details-header,
  .details-main {
    padding: 0 !important;
    text-align: left;
  }

  :deep(.responsive-dialog .el-dialog) {
    width: 95% !important;
  }

  :deep(.responsive-dialog .el-dialog__body) {
    max-height: 70vh;
    overflow-y: auto;
  }

  :deep(.relations-dialog .el-dialog__body .form-item-col) {
    display: none !important;
  }

  :deep(.context-menu) {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%);
    width: 80%;
    max-width: 280px;
  }
}

.context-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.4);
  z-index: 999;
}

.context-menu .el-menu-item {
  display: flex !important;
  align-items: center !important;
  gap: 10px;
}

.context-menu .el-menu-item span {
  white-space: nowrap;
}

.details-header {
  height: auto !important;
  padding-top: 10px;
  padding-bottom: 10px;
}

.details-title {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 15px;
  font-weight: bold;
}

.details-title-icon {
  width: 25px;
  height: 25px;
  flex-shrink: 0;
}

.details-title-text {
  word-break: break-word;
  line-height: 1.4;
}

@media (max-width: 1440px) and (min-width: 992px) {
  .aside-tree {
    width: 480px !important;
  }

  .content-wrapper {
    max-height: 400px;
  }

}

@media (max-width: 1280px) and (min-width: 992px) {
  .aside-tree {
    width: 520px !important;
  }

  .content-wrapper {
    max-height: 350px;
  }

}

:deep(.dialog-ascendentes-diseno .el-dialog__body) {
  padding: 0 !important;
}

:deep(.dialog-ascendentes-diseno .el-dialog__header) {
  display: none;
}

.dialog-header-custom {
  background-color: #f1f7ff;
  padding: 20px 24px;
  border-bottom: 1px solid #e4e7ed;
  text-align: left;
}

.dialog-header-custom h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #303133;
}

.dialog-header-custom {
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
    max-height: 65vh;
    overflow-y: auto;
}
</style>