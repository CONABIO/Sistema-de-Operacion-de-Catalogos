<script setup>
import { ref, onMounted, triggerRef, h } from 'vue';
import { InfoFilled, MessageBox, Setting, HelpFilled, Grid, View } from '@element-plus/icons-vue';
import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
import FormNombre from '@/Pages/Socat/NombreTaxonomico/FormNombre.vue'; // Asegúrate de que la ruta sea correcta
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
import NotificacionExitoErrorModal from"@/Components/Biotica/NotificacionExitoErrorModal.vue";
import filtroGrupos from '@/Components/Biotica/Icons/Conectado.vue';
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import { showConfirmMessage } from '@/Composables/mensajeConfirm';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrableImg.vue";

const { permisos } = usePermisos();

const page = usePage();
const authUser = page.props.auth.user || [];


//Definición de variables
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
  { prop: 'TipoRelacion', label: 'Tipo Relación', minWidth: '120', sortable: true, 
            align: 'left', tipo:'imagenTexto', filtrable: true },
  { prop: 'Nombrecompleto', label: 'Nombre Completo', minWidth: '250', sortable: true, 
            align: 'left', tipo:'imagenTexto', filtrable: true },
  { prop: 'Biblio', label: 'Ref.', minWidth: '55', sortable: false, align: 'center', 
            tipo:'imagenTexto', filtrable: false }
]);

const opcionesFiltroNomenclatura = ref([
  { label: 'TipoRelación', value: 'TipoRelacion' },
  { label: 'NombreCompleto', value: 'Nombrecompleto' }
]);

const totalRegRef = ref(0);

const columnasDefRef= ref([
  { prop: 'Cita', label: 'Cita completa', minWidth: '250', sortable: true,
           align: 'left', tipo:'textarea', filtrable: true},
 ]);

const opcionesFiltroRef = ref([
  { label: 'Cita', value: 'Cita' }
]);

//variables del paginado
const totalItems = ref(0);
const currentPage = ref(1);
const itemsPerPage = ref(150);

const tree = ref(null);
const selectedNode = ref([]);
const menuPosition = ref({ x: 0, y: 0 });
const isMenuVisible = ref(false);

const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);

const dialogFormVisibleAlta = ref(false); // Para controlar la visibilidad del modal
const taxonAct = ref([]); // Agrega esta línea
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


//Declaración de Funciones
const filtro_Catalogos = () => {
  //Funcion para abrir el modal que muestra los catalogos taxonómicos
  dialogFormVisibleCat.value = true;
};

//Funcion para validae la visbilidad de los objetos 
const hasPermisos = (etiqueta, modulo) => {

  const permiso = permisos.find(item => item.NombreModulo === etiqueta);

  return permiso[modulo];
};

// Funcion para abrir el modal de FormNombre
const openDialog = (nodeData) => {
  console.log(nodeData);

  emit('reset-form'); // Emite el evento "reset-form"
  dialogFormVisibleAlta.value = true;
};

const emit = defineEmits(['reset-form']); //Definimos el Emite
//Reseteamos FormNombre
const resetFormNombre = () => {
  emit('reset-form'); // Emitimos el evento de vuelta al componente
}

//Funcion para cerrar el modal de FormNombre
const closeDialog = () => {
  dialogFormVisibleAlta.value = false;
  dialogFormVisibleRel.value = false;
};

const cerrarDialog = (valor) => {
  dialogFormVisibleCat.value = valor; // Cambia la visibilidad del diálogo
};

//Funcion para recibir los datos de los grupos y catalogos selccionados
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

//Funcion para los cambios recibidos
const recibeTaxMod = async (res) => {

  const index = data.value.findIndex(nombre => nombre.id === taxonAct.value.id);

  for (const node of data.value) {
    if (node.id === res.id) {
      Object.assign(node, res);
      return true;  // Nodo encontrado y actualizado
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
      return true; // Nodo encontrado y actualizado en los hijos
    }
    if (child.children && child.children.length > 0) {
      const found = await updateChildNode(child.children, res);
      if (found) return true;
    }
  }
  return false; // Nodo no encontrado en los hijos
};

//Función para recibir los nuevos taxones 
const recibeTaxNuevo = async (res) => {

  const index = data.value.findIndex(nombre => nombre.id === taxonAct.value.id);

  if (index !== -1) {
    if (!data.value[index].children) {
      data.value[index].children = [];
    }
    data.value[index].children.push(res);
  }
};

//Función para recibir los taxones que dan de baja
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

//Funcion que se encarga de ejecutar acciones una vez que se crea el elemento
onMounted(() => {
  dialogFormVisibleCat.value = true;
  console.log(authUser);
});

//Funcion para el envio de mensajes en pantalla
const open = (mensaje) => {
  ElMessageBox.alert(mensaje, 'Nombres taxonómicos', {
    confirmButtonText: 'OK',
  });
}

//Esta funcion se dispara una vez que se selecciona una categia taxonomica
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

      //De forma asincrona se ejecutan las funciones de carga de datos por medio de axios
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

//Función para hacer la busqueda de los valores colocados en el input de busqueda
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

//Funcion que se ejecuta para la expancion de un nodo
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

const proceder = ()=>
{
  console.log("Pase el movimeinto");
}

const cancelar = ()=>
{
  console.log("Evite el movimeinto");
}
//Función para mover un taxón y reasignarlo a otro 
const mover = async (node) => {

  if (taxMov.value.length === 0) {
    try {

     const result = await showConfirmMessage({
        title: 'Atención',
        message: '¿Está seguro de mover este taxón?',
        icon: '!'
      });

      if(!result)
      {
        return ;
      }

      node.data.customClass = 'highlight-node';
      taxMov.value = node;

      // Forzar actualización del árbol
      triggerRef(data); // Esto actualizará la vista reactivamente

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

          //Lógica para validación de estatus
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

      if(!result)
      {
        return ;
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

    //De forma asincrona se ejecutan las funciones de carga de datos por medio de axios
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
    // El usuario canceló la operación
    console.log(error);
    console.log('Operación cancelada por el usuario');
  }

}

const handleNodeRightClick = (event, data, node) => {

  event.preventDefault(); // Prevenir el menú contextual del navegador

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

// Manejador de acciones del menú
const handleMenuClick = (action) => {
  isMenuVisible.value = false;
  // Aquí puedes agregar lógica específica para cada acción
  console.log('Acción seleccionada:', action);
};

// Cerrar menú
const closeMenu = () => {
  isMenuVisible.value = false;
  document.removeEventListener('click', closeMenu);
  document.removeEventListener('keydown', handleEscKey);
};

// Manejador de tecla Escape
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
  /*currentData.value = response.data.data || [];
  totalItems.value = response.data.total || response.data.totalItems || 0;*/
};

  const abre_Relaciones = () => {
      dialogFormVisibleRel.value = true;
  }
</script>

<template>
  <CuerpoGen :tituloPag="'Nombre_Taxón'" :tituloArea="'Catálogo de nombres taxonómicos'">
      <!--el-container style="min-height: 100vh; display: flex; flex-direction: column; border: 1px solid #eee"-->
      <el-container style="height: 100vh; display: flex;">
        <el-header>
          <div>
            <el-row :gutter="16" style="display: flex; flex-wrap: wrap;">
              <!-- Primera columna -->
              <el-col :xs="24" :sm="12" :md="7" style="display: flex; flex-direction: column;">
                <span>Ir a:</span>
                <el-input clearable placeholder="" v-model="filterText" @change="filterNode"  
                          style="height: 28px;" size="small">
                </el-input>
              </el-col>

              <!-- Segunda columna -->
              <el-col :xs="24" :sm="12" :md="5" style="display: flex; flex-direction: column;">
                <span class="block">Nivel taxonómico</span>
                <el-cascader :options="categoriasTax" 
                             clearable 
                             filterable 
                             v-model="categ"
                             placeholder="Nivel taxonómico" 
                             @change="handleChange">
                  <template #default="{ data }">
                    <span style="display: inline-flex; align-items: center;">
                      <img :src="data.RutaIcono" alt="" 
                            style="width: 16px; height: 16px; margin-right: 6px;" />
                      <span>{{ data.label }}</span>
                    </span>
                  </template>           
                </el-cascader>
              </el-col>

              <!--el-row :gutter='2' justify="center" style="display: flex; border: 1px solid green"-->
              <el-col :span="12">
                <el-row :gutter="10" style="display: flex; align-items: flex-start;">
                  
                  <!-- Catálogo(s) -->
                  <el-col :span="11">
                    <div style="display: flex; flex-direction: column;">
                      <span class="demo-input-label" style="margin-bottom: 4px;">
                        Catálogo(s)
                      </span>
                      <el-input
                        type="textarea"
                        :rows="2"
                        placeholder="Catálogos"
                        v-model="catalogos"
                        :disabled="true"
                      />
                    </div>
                  </el-col>

                  <!-- Grupo SCAT -->
                  <el-col :span="11">
                    <div style="display: flex; flex-direction: column;">
                      <span class="demo-input-label" style="margin-bottom: 4px;">
                        Grupo SCAT
                      </span>
                      <el-input
                        type="textarea"
                        :rows="2"
                        placeholder="Grupo SCAT"
                        v-model="grupos"
                        :disabled="true"
                      />
                    </div>
                  </el-col>

                  <!-- Botón alineado con las etiquetas -->
                  <el-col :span="2" style="display: flex; align-items: flex-start;">
                    <el-tooltip
                      effect="dark"
                      content="Selección Catálogo de Grupos taxonómicos"
                      placement="bottom"
                    >
                      <el-button
                        @click="filtro_Catalogos()"
                        type="primary"
                        circle
                        style="margin-top: 4px;" 
                      >
                        <el-icon>
                          <filtroGrupos />
                        </el-icon>
                      </el-button>
                    </el-tooltip>
                  </el-col>
                </el-row>
              </el-col>
            </el-row>
          </div>
          <br>
          <div style="flex-grow: 1;">
            <el-container style="height: 100%;">
              <el-aside width="600px" style="background-color: rgb(238, 241, 246); height: 100%; overflow: auto;">
                <div class="tree-container">
                  <el-scrollbar height="550px">
                  <el-tree 
                    class="filter-tree" 
                    style="overflow: auto;" 
                    :data="data" 
                    node-key="id"
                    @node-click="expande" 
                    :expand-on-click-node="true" 
                    :filter-node-method="filterNode" 
                    :allow-drag="() => true"
                    :draggable="false" 
                    empty-text='' 
                    ref="tree" 
                    :highlight-current="true" 
                    :current-node-key="selectedNodeKey"
                    :props="defaultProps"
                    @node-contextmenu="handleNodeRightClick">
                    <template #default="{ node }">
                      <div class="tree-node-wrapper" >
                        <Logo class="tree-node-logo" :rutaCategoria="node.data.completo.categoria.RutaIcono" />
                        <el-tooltip content="Información">
                          <el-icon @click.prevent="openDialog(node.data)">
                            <!-- Agregamos el @click.stop y el openDialog -->
                            <InfoFilled />
                          </el-icon>
                        </el-tooltip>
                        <div v-if="hasPermisos('MnuNomCientifico', 'Cambios')">
                          <el-tooltip class="item" effect="dark" content="Mover" placement="bottom">
                            <span :style="{ color: node.color }" :id="`node-${node.id}`">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
                                  class="bi bi-diagram-3-fill" viewBox="0 0 16 16" @click="mover(node)">
                                <path fill-rule="evenodd" 
                                  d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5zM0 11.5A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                              </svg>
                              <ModalConfirmacion ref="modalConfirmar" />
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
                  <el-menu
                        ref = "contextMenu"
                        class="context-menu"
                        :style="{top: menuPosition.y + 'px', left: menuPosition + 'px'}"
                        v-if="isMenuVisible">
                    <el-menu-item
                      class="item">
                      <span  class="text-lg">  
                        <el-icon class="icono">
                          <HelpFilled />
                        </el-icon>
                      </span>
                      <span class="hidden sm:inline mr-2 text-base" 
                            @click="abre_Relaciones()">
                        Relaciones taxonómicas
                      </span> 
                    </el-menu-item>
                    <el-menu-item
                      class="item">
                      <el-icon><Grid /></el-icon>
                      <span>Catálogos asociados</span>
                    </el-menu-item>
                    <el-menu-item
                      class="item">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bar-chart-steps" viewBox="0 0 16 16">
                        <path d="M.5 0a.5.5 0 0 1 .5.5v15a.5.5 0 0 1-1 0V.5A.5.5 0 0 1 .5 0M2 1.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-4a.5.5 0 0 1-.5-.5zm2 4a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm2 4a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-6a.5.5 0 0 1-.5-.5zm2 4a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5z"/>
                      </svg> 
                      <span>Ascendentes</span>
                    </el-menu-item>
                  </el-menu>
                </div>
              </el-aside>
              <el-container style="height: 500px; border: 1px;">
                <el-header style="text-align: left; font-size: 12px; height: 35px;">
                  <div>
                    <br/>
                    <span class="demo-input-label" style="display: flex; align-items: center; font-size: 15px; font-weight: bold;">
                      <img v-if="taxonAct?.completo?.categoria?.RutaIcono" :src = "taxonAct?.completo?.categoria?.RutaIcono" style="width: 25px; height: 25px">
                      <span style="margin-left: 8px;">
                        {{ taxonAct?.completo?.NombreCompleto }} {{ taxonAct?.completo?.NombreAutoridad }}
                      </span>
                    </span>
                  </div>
                </el-header>
                <el-main width="500px" style="height: 100%; display: flex; flex-direction: column;">
                  <div style="flex-shrink: 0; height: 35px;">
                    <span class="demo-input-label" style=" font-weight: bold;">Relaciones nomenclaturales</span>
                  </div>
                  <div style=" flex-direction: column; overflow-y: auto; flex-grow: 1; height: 1400px;">
                    <TablaFiltrable class="flex-grow" 
                                    :container-class="'main-section'" 
                                    :columnas="columnasDefinidas"
                                    v-model:datos="tablaNomenclatura" 
                                    v-model:total-items="totalRegNom" 
                                    :opciones-filtro="opcionesFiltroNomenclatura">
                    </TablaFiltrable>
                  </div>
                  <br>
                  <div style="flex-shrink: 0; height: 35px;">
                    <span class="demo-input-label" style=" font-weight: bold;">Referencias asocidas</span>
                  </div>
                  <div style=" flex-direction: column; overflow-y: auto; flex-grow: 1; height: 1400px;">
                    <TablaFiltrable class="flex-grow" 
                                    :container-class="'main-section'" 
                                    :columnas="columnasDefRef"
                                    v-model:datos="tablaReferencias" 
                                    v-model:total-items="totalRegRef" 
                                    :opciones-filtro="opcionesFiltroRef">
                    </TablaFiltrable>
                  </div>
                </el-main>                  
              </el-container>
            </el-container>
          </div>
          <br>
          <div class="main-section">
            <div v-if="totalItems > 0" class="pagination-container-wrapper">
              <el-pagination :current-page="currentPage" :page-size="itemsPerPage" :total="totalItems"
                @current-change="handlePageChange" layout="prev, pager, next, total" background
                class="main-pagination-style">
              </el-pagination>
            </div>
          </div>

        </el-header>
      </el-container>
    
  </CuerpoGen>
  <DialogForm v-model="dialogFormVisibleCat" :botCerrar="false" :pressEsc="false" :width="'35%'">
    <FiltroGrupos :grupos="gruposTax" @cerrar="cerrarDialog" @regresaGrupos="recibeGrupos" />
  </DialogForm>

  <DialogForm v-model="dialogFormVisibleAlta" 
              @close = "closeDialog" 
              @reset-form = "resetFormNombre" 
              :botCerrar = "true" 
              :pressEsc = "true">
    <FormNombre :taxonAct = "taxonAct" 
                :paginaActual = "1" 
                :categoria = "catego.value" 
                :catalogos = "idsGrupos.value" 
                @cerrar = "closeDialog"
                @regresaTaxMod = "recibeTaxMod"
                @resultadoAlta = "recibeTaxNuevo"
                @resultadoBaja = "recibeTaxBaja"/>
  </DialogForm>

  <DialogForm v-model="dialogFormVisibleRel" :botCerrar="true" :pressEsc="true" :width="'83%'">
    <!--FiltroGrupos :grupos="gruposTax" @cerrar="cerrarDialog" @regresaGrupos="recibeGrupos" /-->
    <DialogRelaciones :taxonAct = "taxonAct" 
                      :gruposTax = "gruposTax"
                      :categoriasTax = "categoriasTax"
                      :catalogPadre = "catalogos"
                      :gruposPadre = "grupos"
                      :idsGruposPadre = "idsGrupos"
                       @cerrar = "closeDialog">
    </DialogRelaciones>
  </DialogForm>

    <Teleport to="body">
      <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
        :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
        @close="cerrarNotificacion" />
    </Teleport>
</template>

<style scoped>
.el-aside {
  display: flex;
  flex-direction: column;
  height: 100%; /* Asegura que ocupe todo el alto disponible */
}

/* Contenedor del árbol con scroll */
.tree-container {
  flex: 1; /* Ocupa todo el espacio disponible */
  overflow: auto; /* Habilita scroll si es necesario */
  min-height: 0; /* Necesario para que funcione flex + overflow en algunos navegadores */
}

/* Ajustes para el árbol */
.el-tree {
  min-width: fit-content; /* Asegura que el árbol no sea más pequeño que su contenido */
  width: 100%; /* Ocupa todo el ancho disponible */
}

/* Opcional: Ajusta el ancho de los nodos para evitar desbordamiento horizontal */
.custom-tree-node {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

/*------------------------------------------------------------------------------6&&&&&&&&&&&&&&&&&&&&&&&&&&&&*/
.scrollbar-demo-item {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 50px;
  margin: 10px;
  text-align: center;
  border-radius: 4px;
  background: var(--el-color-primary-light-9);
  color: var(--el-color-primary);
}


.icono {
  margin-right: 8px;
  /* Ajusta el espacio */
}
/*
.custom-tree-node {
  align-items: center;
  justify-content: space-between;
  font-size: 14px;
  padding-right: 8px;
  width: 50%;
  overflow: auto;
}*/

.main-content-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
  padding: 25px;
  display: flex;
  flex-direction: column;
  height: 705px; 
}

.filters-section {
  margin-bottom: 20px;
  flex-shrink: 0; 
}

.main-view-section {
  flex-grow: 1; 
  min-height: 0; 
}


:deep(.el-tree-node.is-current > .el-tree-node__content){
  background-color: rgb(203, 233, 200) !important;
  color: #0d6efd !important;
}

:deep(.highlight-node){
  color: #a52f2f !important;
}

.tree-node-wrapper {
  display: flex;
  align-items: center; 
  gap: 8px;
  white-space: nowrap;
  font-size: 14px;
}

.tree-node-logo {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.context-menu {
  position: absolute;
  z-index: 9999;
  background-color: white;
  border: 1px solid #dcdfe6;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  padding: 5px 0;
  border-radius: 4px;
  min-width: 180px;
}
.el-menu-item {
    height: 36px;
    line-height: 36px;
}
.w-full { width: 100%; }
.mb-4 { margin-bottom: 1rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-6 { margin-bottom: 1.5rem; }
.p-2 { padding: 0.5rem; }
.p-4 { padding: 1rem; }
.h-full { height: 100%; }
.border { border: 1px solid #dcdfe6; }
.rounded-lg { border-radius: 8px; }
.bg-gray-50 { background-color: #f9fafb; }
.font-semibold { font-weight: 600; }
.cursor-pointer { cursor: pointer; }
</style>