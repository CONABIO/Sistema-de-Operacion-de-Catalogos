<script setup>
import { ref, onMounted, triggerRef} from 'vue';
import { InfoFilled, MessageBox, Setting, HelpFilled, Grid } from '@element-plus/icons-vue';
import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
import FormNombre from '@/Pages/Socat/NombreTaxonomico/FormNombre.vue'; 
import FiltroGrupos from '@/Pages/Socat/NombreTaxonomico/FiltroGrupoTax.vue';
import CuerpoGen from '@/Components/Biotica/LayoutCuerpo.vue';
import { ElMessageBox } from 'element-plus';
import { ElLoading } from 'element-plus';
import AutorTaxon from '../Autores/AutorTaxon.vue';
import Logo from '@/Components/Biotica/LogoCategoria.vue';
import { usePage } from '@inertiajs/vue3';
import usePermisos from '@/composables/usePermisos';

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

const selectedNode = ref([]);
const menuPosition = ref({ x: 0, y: 0 });
const isMenuVisible = ref(false);

const dialogFormVisibleAlta = ref(false); 
const taxonAct = ref([]); 
const data = ref([]);
const categ = ref(null);
const catalogos = ref('');
const grupos = ref('');
const idsGrupos = ref('');
const mostrar = ref(false);
const dialogFormVisibleCat = ref(false);
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
const columnasNom = ref([
  {
    prop: "TipoRelacion",
    label: "Tipo",
    minWidth: 80,
    sortable: true,
    hidden: false,
    align: 'left',
    fixed: true
  },
  {
    prop: "NombreCompleto",
    label: "Nombre",
    minWidth: 80,
    sortable: true,
    hidden: false,
    align: 'left',
    fixed: true
  },
  {
    prop: "estatus",
    label: "Estatus",
    minWidth: 80,
    sortable: true,
    hidden: false,
    align: 'left',
    fixed: true
  }
]);
const tablaReferencias = ref([]);
const columnasRef = ref([
  {
    prop: "Autor",
    label: "Autor",
    minWidth: 80,
    sortable: true,
    hidden: false,
    align: 'left',
    fixed: true
  },
  {
    prop: "Anio",
    label: "Año",
    minWidth: 80,
    sortable: true,
    hidden: false,
    align: 'left',
    fixed: true
  },
  {
    prop: "Titulo",
    label: "Tipo de publicación",
    minWidth: 80,
    sortable: true,
    hidden: false,
    align: 'left',
    fixed: true
  },
  {
    prop: "Cita",
    label: "Cita completa",
    minWidth: 80,
    sortable: true,
    hidden: false,
    align: 'left',
    fixed: true
  }
]);

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
const resetFormNombre = () =>{
 emit('reset-form'); // Emitimos el evento de vuelta al componente
}

//Funcion para cerrar el modal de FormNombre
const closeDialog = () => {
  dialogFormVisibleAlta.value = false;
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
  }else{
    let categ = [];
    categ.push(catego.value)  
    handleChange(categ); 
  }
};

//Funcion para los cambios recibidos
const recibeTaxMod = async (res) => {

  const index = data.value.findIndex(nombre => nombre.id === taxonAct.value.id);
  
  for(const node of data.value){
    if(node.id === res.id){
       Object.assign(node, res);
       return true;  // Nodo encontrado y actualizado
    }
    
    if(node.children && node.children.length > 0 )
    {
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
  
  if(index !== -1){
    if(!data.value[index].children){
      data.value[index].children = [];
    }
    data.value[index].children.push(res);
  }
};

//Función para recibir los taxones que dan de baja
const recibeTaxBaja = async (res) => {
 
  const index = data.value.findIndex(nombre => nombre.id === taxonAct.value.id);

  let found = false;
  
  for(let i=0; i < data.value.length; i++){
    if(String(data.value[i].id) === String(res.Id)){
      children.splice(i,1);
      return true;
    }

    if(data.value[i].children && data.value[i].children.length > 0){

      found = deleteChildNode(data.value[i].children, res);
      if(found) break;
    }
  }
}

const deleteChildNode = async (children, res) => {
  for(let i=0; i < children.length; i++){
    if(String(children[i].id) === String(res.Id))
    {
      children.splice(i,1);
      return true;
    }
  }
  return  false;
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
  
  if(value != undefined)
  {
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
        totalReg.value = response.data[1].total;
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
const filterNode = async (value, data) => {
  mostrar.value = false;

  const loading = ElLoading.service({
    lock: true,
    text: "Loading",
    spinner: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="none" d="M0 0h200v200H0z"></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M70 95.5V112m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5L92 57.3M33.6 91 48 82.7m0-25.5L33.6 49m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;-120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M130 155.5V172m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5-14.3 8.3M93.6 151l14.3-8.3m0-25.4L93.6 109m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>`,
    backgroud: 'rgba(255,255,255,0.85)',
  });

  if (value != '' && idsGrupos.value != '' && categ.value[0] != '') {
    const params = {
      categ: categ.value[0],
      catalog: idsGrupos.value,
      taxon: value
    };

    const response = await axios.get('/cargar-nomArb',
      { params });
      
    if (response.status === 200) {
      data.value = response.data[0];
      totalReg = response.data[1].total;
      paginas = response.data[1].last_page;
      loading.close();
    }
  } else if (idsGrupos.value != '') {
    const params = {
      categ: categ.value[0],
      catalog: idsGrupos.value
    };

    const response = await axios.get('/cargar-nomArb',
      { params });
      
    if (response.status === 200) {
      data.value = response.data[0];
      totalReg = response.data[1].total;
      paginas = response.data[1].last_page;
      loading.close();
    }
  }
  loading.close();
}

//Funcion que se ejecuta para la expancion de un nodo
const expande = async (draggingNode) => {
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
      tablaNomenclatura.value = response.data[1];
      tablaReferencias.value = response.data[2];
      if (draggingNode.children.length === 0) {
        for (let i = 0; i < response.data[0].length; i++) {
          draggingNode.children.push(response.data[0][i]);
        }
      }
    }
    loading.close();
  }
  selectedNodeKey.value = draggingNode.id;
}

//Función para cambiar el color de la columna referencias bibliograficas
const classChecker =({ row, column, rowIndex, columnIndex}) =>{
  if(column.property === 'Biblio'){

    const val = row[column.property];
    if( val > 0 ){
      return 'greenClass';
    } else {
      return 'redClass';
    }
  }
}

//Función para mover un taxón y reasignarlo a otro 
const mover = async (node) => {
  if(taxMov.value.length === 0)
  {
    try {
      await ElMessageBox.confirm("¿Está seguro de mover este taxón?",
                                "Alert",
          {
            confirmButtonText: "OK",
            cancelButtonText: 'Cancel',
            type: 'warning'
          }
      );
    
      node.data.customClass = 'highlight-node';
      taxMov.value = node;

      // Forzar actualización del árbol
      triggerRef(data); // Esto actualizará la vista reactivamente

      await ElMessageBox.alert("El taxón será reasignado", 
      {
        confirmButtonText: "OK",
        type: "success"
      });
    } catch (error) {
      await ElMessageBox.alert("Acción cancelada",{
        confirmButtonText: "OK",
        type: 'info'
      });
    }
  }else{
    if(taxMov.value.data.completo.scat === undefined){
      await ElMessageBox.alert(`El taxón ${taxMov.value.data.completo.TaxonCompleto} no tiene un registro en la tabla scat 1`,
        {
          title: "Error",
          confirmButtonText: "OK",
          type: "error"
        });
        return;
    } else if (typeof node.data.completo.scat === 'object' && Object.keys(node.data.completo.scat).length <= 0){
      await ElMessageBox.alert(`El taxón ${node.data.completo.TaxonCompleto} no tiene un registro en la tabla scat 2`,
        {
          title: "Error",
          confirmButtonText:"OK",
          type: "error"
        }
      );
      return;
    }
    if(taxMov.value.data.id === node.data.id){
      taxMov.value= [];
      await ElMessageBox.alert("Se cancelará el movimiento del taxón", 
        {
          title: "SOCAT",
          confirmButtonText: "OK",
          type:"success"
        });
        delete node.data.customClass;
        return;
    }
    if(taxMov.value.data.completo.scat.grupo_scat.GrupoSCAT === node.data.completo.scat.grupo_scat.GrupoSCAT)
    {
      if(node.data.completo.categoria.IdNivel1 === (taxMov.value.data.completo.categoria.IdNivel1 -1 )){
        const params = {
            estatusInicio: taxMov.value.data.completo.Estatus,
            nomAct: taxMov.value.data.id
          };

        try{
          const response = await axios.get('/valCamEstatus', params);

          //Lógica para validación de estatus
          switch(taxMov.value.data.completo.Estatus){
            case 1:
              switch(node.data.completo.Estatus)
              {
                case 1:
                    await moverTaxon(
                      taxMov.value.data.completo.IdNombre,
                      node.data.completo.IdNombre,
                      taxMov.value,
                      node
                    );
                  break;
                case 2:
                  if(response.data != 1)
                  {
                    await ElMessageBox.alert(`Está intentando mover un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,{
                      confirmButtonText: "OK",
                      type: "error"
                    });
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
                  await ElMessageBox.alert(`Está intentando mover un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,{
                    confirmButtonText: 'OK', 
                    type: 'error'}
                  );
                break;
              }
            break;
            case 2:
              switch(node.data.completo.Estatus)
              {
                case 2:
                  await moverTaxon(
                    taxMov.value.data.completo.IdNombre,
                    node.data.completo.IdNombre,
                    taxMov.value,
                    node
                  );
                break;
                case 1:
                  if(response.data != 1)
                  {
                    await ElMessageBox.alert(`Está intentando mover a un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,{
                      confirmButtonText: 'OK',
                      type: 'error'
                    });
                  }else{
                    await moverTaxon(
                      taxMov.value.data.completo.IdNombre,
                      node.data.completo.IdNombre,
                      taxMov,
                      node
                    );
                  }
                break;
                default:
                  await ElMessageBox.alert(`Está intentando mover un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,{
                    confirmButtonText: 'OK',
                    type: 'error'
                  });
              }
            break;
            case 6:
              switch(node.data.completo.Estatus)
              {
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
                  if(response.data != 1)
                  {
                    await ElMessageBox.alert(`Está intentando mover un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`,{
                      confirmButtonText: 'OK',
                      type: 'error'
                    });
                  }else{
                    await moverTaxon(
                      taxMov.value.data.completo.IdNombre,
                      node.data.completo.IdNombre,
                      taxMov.value,
                      node);
                  }
              }
            break;
            case -9:
              switch(node.data.completo.Estatus)
              {
                case 6:
                  await moverTaxon(
                    taxMov.data.completo.IdNombre,
                    node.data.completo.IdNombre,
                    taxMov.value,
                    node);
                break;
                case -9: 
                  await ElMessageBox.alert(`Está intentando mover un taxón con estatus ${taxMov.data.estatus} a un taxón con estatus ${node.data.estatus}`,{
                    confirmButtonText: 'OK',
                    type: 'error'
                  });
                break;
                case 1:
                case 2:
                  if(response.data != 1)
                  {
                    await ElMessageBox.alert(`Está intentando mover un taxón con estatus ${taxMov.value.data.estatus} a un taxón con estatus ${node.data.estatus}`, {
                      confirmButtonText: 'OK',
                      type: 'error'
                    });
                  }else{
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
        } catch(error){
          console.error('Error al validar estatus:', error);
          ElMessageBox.error('Error al validar el estatus del taxón');
        }
      }else{
        await ElMessageBox.alert(`Está intentando mover el taxón a una categoria no permitida`, {
          confirmButtonText: 'OK',
          type: 'error'
        });
      }
    }else{
      await ElMessageBox.alert(`El taxón al que quiere asignar pertenece a un grupo taxonómico diferente`,{
        confirmButtonText: 'OK',
        type: 'error'
      });
    }
  }
}

const moverTaxon = async (taxMover, taxRecb, nodoMov, nodoRecb) => {

  if(nodoMov.data.completo.padre.IdNombre === nodoRecb.data.completo.IdNombre)
  {
    await ElMessageBox.alert('El taxón al que quiere asignar es el mismo del que parte selccione uno diferente',{
      confirmButtonText: 'OK',
      type: 'error'
    });
    return;
  }
 
  try {

    await ElMessageBox.confirm(`¿Está seguro de mover el(la) ${nodoMov.data.completo.NombreCategoriaTaxonomica} 
            ${nodoMov.data.completo.NombreCompleto} y que sea asignado a el(la) 
            ${nodoRecb.data.completo.NombreCategoriaTaxonomica} ${nodoRecb.data.completo.NombreCompleto}?`,
            'Alerta',{
              confirmButtonText: 'OK',
              cancelButtonText: 'Cancel',
              type: 'warning'
            }
          );

    const requestData  = {
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
      await ElMessageBox.alert('El taxón sea reasignado exitosamente',{
        confirmButtonText: 'OK',
        type: 'success'
      });

      const params ={
        categ: catego.value,
        catalog: idsGrupos.value
      }
      
      const resp = await axios.get('/cargar-nomArb', {params: params});
      
      console.log('Esta es respuesta despues del get: ', resp);

      data.value = resp.data[0];
      totalReg.value = resp.data[1].total;
      paginas.value = resp.data[1].last_page;

    }
    else {
      await ElMessageBox.alert('El taxón que intenta mover no se pudo reasignar',{
        confirmButtonText: 'OK',
        type: 'error'
      });
    }

    loading.close();
  

  } catch (error) {
    // El usuario canceló la operación
    console.log(error);
    console.log('Operación cancelada por el usuario');
  }
  
}

const handleNodeRightClick = (event, data) => {
   taxAct.value = data;
  event.preventDefault(); // Prevenir el menú contextual del navegador
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
</script>

<template>
  <CuerpoGen :tituloPag="'Nombre_Taxón'" :tituloArea="'Catálogo de nombres taxonómicos'">
    <div class="main-content-card">
      
      <div class="filters-section">
        <el-row :gutter="20" class="mb-4">
          <el-col :span="24">
            <el-tooltip content="Selección Catálogo de Grupos taxonómicos" placement="top">
              <el-button @click="filtro_Catalogos()" type="primary" :icon="Setting" round>
                Grupos taxonómicos
              </el-button>
            </el-tooltip>
          </el-col>
        </el-row>

        <el-collapse class="mb-4">
          <el-collapse-item title="Grupos taxonómicos seleccionados" name="1">
              <el-row :gutter='20'>
                <el-col :xs="24" :sm="12">
                  <span class="font-semibold">Catálogo(s)</span>
                  <el-input type="textarea" autosize placeholder="Catálogos" v-model="catalogos" :disabled="true" />
                </el-col>
                <el-col :xs="24" :sm="12">
                  <span class="font-semibold">Grupo SCAT</span>
                  <el-input type="textarea" :rows="2" placeholder="Grupo SCAT" v-model="grupos" :disabled="true" />
                </el-col>
              </el-row>
          </el-collapse-item>
        </el-collapse>

        <el-row :gutter="20">
          <el-col :xs="24" :md="12">
            <span class="font-semibold">Ir a:</span>
            <el-input clearable placeholder="Buscar taxón..." v-model="filterText" @change="filterNode" />
          </el-col>
          <el-col :xs="24" :md="12">
            <span class="font-semibold">Nivel taxonómico</span>
            <el-cascader :options="categoriasTax" clearable filterable v-model="categ"
              placeholder="Seleccionar nivel taxonómico" @change="handleChange" class="w-full" />
          </el-col>
        </el-row>
      </div>

      <div class="main-view-section">
        <el-container class="h-full border rounded-lg">
          
          <el-aside width="750px" class="bg-gray-50 p-2">
              <el-tree 
                class="filter-tree" 
                :data="data" 
                node-key="id"
                @node-click="expande" 
                :expand-on-click-node="true" 
                :draggable="hasPermisos('MnuNomCientifico', 'Cambios')" 
                empty-text='No hay datos para mostrar' 
                ref="tree" 
                :highlight-current="true" 
                :current-node-key="selectedNodeKey"
                :props="defaultProps"
                @node-contextmenu="handleNodeRightClick"
              >
                <template #default="{ node }">
                  <div class="tree-node-wrapper">
                    <Logo class="tree-node-logo" :rutaCategoria="node.data.completo.categoria.RutaIcono" />
                    <el-tooltip content="Información">
                      <el-icon @click.stop="openDialog(node.data)"><InfoFilled /></el-icon>
                    </el-tooltip>
                    <el-tooltip v-if="hasPermisos('MnuNomCientifico', 'Cambios')" content="Mover" placement="right">
                      <span @click.stop="mover(node)" class="cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-diagram-3-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5zM0 11.5A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/></svg>
                      </span>
                    </el-tooltip>
                    <span class="tree-node-label" :class="{ 'highlight-node': node.data.customClass === 'highlight-node' }">
                      {{ node.label }}
                    </span>
                  </div>
                </template>
              </el-tree>
          </el-aside>
          
          <el-container v-if="mostrar && taxonAct" class="p-4">
            <el-main>
                <div class="mb-6">
                    <p><span class="font-semibold">Categoría:</span> {{ taxonAct.completo.categoria.NombreCategoriaTaxonomica }}</p>
                    <p><span class="font-semibold">Nombre:</span> {{ taxonAct.completo.NombreCompleto }} {{ taxonAct.completo.NombreAutoridad }}</p>
                    <p><span class="font-semibold">Taxón:</span> {{ taxonAct.completo.Nombre }}</p>
                </div>
                
                <div class="mb-6">
                    <h3 class="font-semibold mb-2">Relaciones nomenclaturales</h3>
                    <el-table :data="tablaNomenclatura" border height="200" style="width: 100%;">
                      <el-table-column v-for="col in columnasNom" :key="col.prop" v-bind="col" />
                      <el-table-column align="center" width="100" prop="Biblio" label="Referencia">
                        <template #default><span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book-half" viewBox="0 0 16 16"><path d="M8.5 2.687c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/></svg></span></template>
                      </el-table-column>
                      <template #empty><div>No hay datos para mostrar</div></template>
                    </el-table>
                </div>

                <div>
                    <h3 class="font-semibold mb-2">Referencias asociadas</h3>
                    <el-table :data="tablaReferencias" border height="250" style="width: 100%;">
                      <el-table-column v-for="col in columnasRef" :key="col.prop" v-bind="col" />
                      <template #empty><div>No hay referencias asociadas</div></template>
                    </el-table>
                </div>
            </el-main>                  
          </el-container>
        </el-container>
      </div>

        <el-menu v-if="isMenuVisible" class="context-menu" :style="{ top: menuPosition.y + 'px', left: menuPosition.x + 'px' }">
            <el-menu-item @click="handleMenuClick('relaciones')"><el-icon><HelpFilled /></el-icon>Relaciones</el-menu-item>
            <el-menu-item @click="handleMenuClick('catalogos')"><el-icon><Grid /></el-icon>Catálogos asociados</el-menu-item>
        </el-menu>

    </div>
  </CuerpoGen>
  
  <DialogForm v-model="dialogFormVisibleCat" :botCerrar="false" :pressEsc="false">
    <FiltroGrupos :grupos="gruposTax" @cerrar="cerrarDialog" @regresaGrupos="recibeGrupos" />
  </DialogForm>

  <DialogForm v-model="dialogFormVisibleAlta" @close="closeDialog" @reset-form="resetFormNombre" :botCerrar="true" :pressEsc="true">
    <FormNombre :taxonAct="taxonAct" :paginaActual="1" :categoria="catego.value" :catalogos="idsGrupos.value" @cerrar="closeDialog" @regresaTaxMod="recibeTaxMod" @resultadoAlta="recibeTaxNuevo" @resultadoBaja="recibeTaxBaja"/>
  </DialogForm>
</template>

<style scoped>
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

:deep(.el-table .greenClass) { background: rgb(90, 177, 90); }
:deep(.el-table .redClass) { background: rgb(226, 119, 119); }

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