<template>
    <div>
        <el-card class="box-card">
            <div class="common-layout">
                <el-container style="height: 72vh;">
                    <el-header class="header">
                        <div class="header-content">
                            <h1 class="titulo">Asociación de catalogos</h1>
                        </div>
                    </el-header>
                    <el-main style="padding: 15px; background: #fff; overflow: hidden;">
                        <el-row :gutter="21">
                            <el-col :span="18">
                                <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                    {{ props.taxonAct.label }} 
                                </span>
                            </el-col>
                            <el-col :span="5" >
                                <div style="display: flex; gap: 5px; justify-content: flex-end;">                                
                                    <BotonCaract @click="abrirCaract"
                                                    style="flex-shrink: 0; min-width: max-content;"/>
                                                            
                                    <BotonRegiones @click="abrirReg"
                                                    style="flex-shrink: 0; min-width: max-content;"/>
                                                            
                                    <BotonNomComun @click="abrirNomCom"
                                                    style="flex-shrink: 0; min-width: max-content;"/>
                                                            
                                    <BotonSalir accion="cerrar" @salir="closeDialog"
                                                    style="flex-shrink: 0; min-width: max-content;"/>
                                </div>
                            </el-col>
                        </el-row>
                        <el-tabs type="card" v-model="tabInicial">
                            <el-tab-pane label="Características" name="Caracteristicas">
                                <el-container>
                                    <el-aside width="710px">
                                        <div class="table-wrapper">
                                            <TablaFiltrable 
                                                :columnas = "columnasDefinidasCaract" 
                                                :datos = "tablaCaracteristicas"
                                                :opciones-filtro = "opcionesFiltroCaract"
                                                :totalItems = "totalRegCaract"
                                                :itemsPerPage = 9
                                                :mostrarBiblio = "false"
                                                :mostrarAcci = "false"
                                                :alturaTabla = 330
                                                :highlight-current-row = "true"
                                                :mostrarNuevo = "false"
                                                :mostrarEditar = "true"
                                                :mostrarBorrar = "true"
                                                :mostrarSalir = "false"
                                                @row-click="clickCaract"/>
                                        </div>
                                    </el-aside>
                                    <el-aside width="30px"/>
                                    <el-aside width="710px">
                                        <div class="table-wrapper">
                                            <TablaFiltrable 
                                                :columnas = "columnasDefinidasRegCaract" 
                                                :datos = "tablaCaractReg"
                                                :opciones-filtro = "opcionesFiltroRegCaract"
                                                :totalItems = "totalRegionCaract"
                                                :itemsPerPage = 9
                                                :mostrarBiblio = "true"
                                                :mostrarAcci = "false"
                                                :alturaTabla = 380
                                                :highlight-current-row = "true"
                                                :mostrarNuevo = "false"
                                                :mostrarEditar = "true"
                                                :mostrarBorrar = "true"
                                                :mostrarSalir = "false"
                                                @row-click="clickRegCaract">                                                
                                            </TablaFiltrable> 
                                        </div>
                                    </el-aside>           
                                </el-container>
                            </el-tab-pane>
                            <el-tab-pane label="Nombre(s) común(es)" name="NomComun">
                                <el-container>
                                    <el-aside width="710px">
                                        <div class="table-wrapper">
                                            <TablaFiltrable 
                                                :columnas = "columnasDefinidas" 
                                                :datos = "tablaNomComun"
                                                :opciones-filtro = "opcionesFiltroNomComun"
                                                :totalItems = "totalRegNomComun"
                                                :itemsPerPage = 9
                                                :mostrarBiblio = "false"
                                                :mostrarAcci = "false"
                                                :alturaTabla = 330
                                                :highlight-current-row = "true"
                                                :mostrarNuevo = "false"
                                                :mostrarEditar = "true"
                                                :mostrarBorrar = "true"
                                                :mostrarSalir = "false"
                                                @row-click="clickNomCom"/>
                                        </div>
                                    </el-aside>
                                    <el-aside width="30px"/>
                                    <el-aside width="710px">
                                        <div class="table-wrapper">
                                            <TablaFiltrable 
                                                :columnas = "columnasDefinidasRegNomCom" 
                                                :datos = "tablaNomComunReg"
                                                :opciones-filtro = "opcionesFiltroRegNomComun"
                                                :totalItems = "totalRegionNomComun"
                                                :itemsPerPage = 9
                                                :mostrarBiblio = "true"
                                                :mostrarAcci = "false"
                                                :alturaTabla = 380
                                                :highlight-current-row = "true"
                                                :mostrarNuevo = "false"
                                                :mostrarEditar = "true"
                                                :mostrarBorrar = "true"
                                                :mostrarSalir = "false"
                                                @row-click="clickRegNomCom">                                                
                                            </TablaFiltrable> 
                                        </div>
                                    </el-aside>                                     
                                </el-container>
                            </el-tab-pane>
                            <el-tab-pane label="Regiones" name="Region">
                                Regiones
                            </el-tab-pane>
                        </el-tabs>
                    </el-main>  
                    <el-footer height="60px">
                        Observaciones {{ etiquetaObs }}
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                            <el-input
                                v-model="observaciones"
                                style="width:97%"
                                :rows="2"
                                type="textarea"
                                :disabled="habObservaciones"
                                placeholder="Observaciones"
                            />
                            <GuardarButton :habilitar = "habObservaciones" @click="Guardar"
                                            style="flex-shrink: 0; min-width: max-content;"/>
                        </div>
                    </el-footer>                              
                </el-container>
            </div>
        </el-card>
        <DialogForm v-model="dialogFormVisibleCaract" :botCerrar="true" :pressEsc="false" :width="'83%'">
            <CuerpoCaracteristicas :modal="true" @cerrar="cerrarCarac"
                :treeDataProp="treeDataProp"
                :flatTreeDataProp="flatTreeDataProp"
            />
        </DialogForm>
        <DialogForm v-model="dialogFormVisibleReg" :botCerrar="true" :pressEsc="false" :width="'83%'">
            <CuerpoRegion :modal="true" @cerrar="cerrarReg"
                :treeDataProp="treeRegionDataProp"
                :tiposDeRegionProp="tiposDeRegionProp"
                :tiposDeRegionTreeProp="tiposDeRegionTreeProp"
            />
        </DialogForm>
        <DialogForm v-model="dialogFormVisibleNomCom" :botCerrar="true" :pressEsc="false" :width="'83%'">
            <CuerpoNombreCom :modal="true" @cerrar="cerrarNomCom"/>
        </DialogForm>
    </div>
</template> 
<script setup>
    import BotonSalir from '@/Components/Biotica/SalirButton.vue';
    import BotonCaract from '@/Components/Biotica/BtnCaracteristicas.vue';
    import BotonRegiones from '@/Components/Biotica/BtnRegiones.vue';
    import BotonNomComun from '@/Components/Biotica/BtnNomComunes.vue';
    import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
    import CuerpoCaracteristicas from '@/Pages/Socat/Caracteristicas/CuerpoCaracteristicas.vue';
    import CuerpoRegion from '@/Pages/Socat/Regiones/CuerpoRegion.vue';
    import CuerpoNombreCom from '@/Pages/Socat/Nombres/CuerpoNombreComun.vue';
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
    import { onMounted, ref, watch } from 'vue';
    import GuardarButton from '@/Components/Biotica/GuardarButton.vue';

    const props = defineProps({
        taxonAct: {
            type: Object
        },
    });

    const tabInicial = ref("NomComun");

    const localTreeData = ref([]);
    const localTreeDataTaxon = ref([]);
    const flatTreeDataProp = ref([]);
    const treeDataProp = ref([]);
    const dialogFormVisibleCaract = ref(false);
    const dialogFormVisibleReg = ref(false);
    const dialogFormVisibleNomCom = ref(false);

    /*Declaracion de propiedades para regiones*/
    const treeRegionDataProp = ref([]);
    const tiposDeRegionProp = ref([]);
    const tiposDeRegionTreeProp = ref([]);
    const etiquetaObs = ref('');
    const observaciones = ref('');
    const habObservaciones = ref(true);

    const tablaNomComun = ref([]);
    const tablaNomComunReg = ref([]);
    const tablaCaracteristicas = ref([]);
    const tablaCaractReg = ref([]); 

    const totalRegCaract = ref(0);
    const totalRegionCaract = ref(0);
    
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

    const closeDialog = () => {
        emit('cerrar');
    };

    //Estas son las columnas a mostrar Nombres Comunes 
    const columnasDefinidas = ref([
        {
            prop: 'NombreComun', label: 'Nombre comun', minWidth: '120',
            align: 'left', tipo: 'texto', filtrable: true
        },
        {
            prop: 'Lengua', label: 'Lengua', minWidth: '250',
            align: 'left', tipo: 'texto', filtrable: true
        }
    ]);

    //Estas son las columnas a mostrar regiones de Nombres Comunes 
    const columnasDefinidasRegNomCom = ref([
        {
            prop: 'Region', label: 'Región', minWidth: '120',
            align: 'left', tipo: 'Texto', filtrable: true
        },
        {
            prop: 'Biblio', label: '', minWidth: '55', align: 'left',
            tipo: 'imagenTexto', filtrable: false
        }
    ]);

    const opcionesFiltroNomComun = ref([
        { label: 'Nombre comun', value: 'NombreComun' },
        { label: 'Lengua', value: 'Lengua' }
    ]);

    const opcionesFiltroRegNomComun = ref([
        { label: 'Region', value: 'Region' },
    ]);

    const opcionesFiltroRegCaract = ref([
        { label: 'Region', value: 'Region' },
        { label: 'Tipo Distribución', value: 'TipDistribucion' },
    ]);

    //Estas son las columnas a mostrar Caracteristicas 
    const columnasDefinidasCaract = ref([
        {
            prop: 'Caracteristica', label: 'Caracteristica', minWidth: '100',
            align: 'left', tipo: 'texto', filtrable: true
        },
        {
            prop: 'BiblioCaract', label: '', minWidth: '35', align: 'left',
            tipo: 'imagenTexto', filtrable: false
        }
    ]);

    const opcionesFiltroCaract = ref([
        { label: 'Caracteristica', value: 'Caracteristica' }
    ]);

     const columnasDefinidasRegCaract = ref([
        {
            prop: 'Region', label: 'Región', minWidth: '120',
            align: 'left', tipo: 'Texto', filtrable: true
        },
        {
            prop: 'TipDistribucion', label: 'Tipo Distribucion', minWidth: '120',
            align: 'left', tipo: 'Texto', filtrable: true
        },
        {
            prop: 'Biblio', label: '', minWidth: '55', align: 'left',
            tipo: 'imagenTexto', filtrable: false
        }
    ]);

    const totalRegNomComun = ref(0);
    const totalRegionNomComun = ref(0);

    watch(
        () => props.taxonAct,
        async (nuevoValor, valorAnterior) => {
            const respNomCom = await axios.get(`/cargar-nomcomun-taxon/${props.taxonAct.id}`);

            if(respNomCom.status === 200)
            {                
                tablaNomComun.value = respNomCom.data;
                totalRegNomComun.value = respNomCom.data.length;
            }

            const listCaract = await axios.get(`/cargaCaracTaxon/${props.taxonAct.id}`);

            if(listCaract.status === 200)
            {
                console.log("Estas son las caracteristicas: ", listCaract);
                tablaCaracteristicas.value = listCaract.data;
                totalRegCaract.value = listCaract.data.length;
            }
        }
    );

    const abrirCaract = async () => {
        const respCarac = await axios.get('/cargar-caracteristicas');

        if(respCarac.status === 200){

            flatTreeDataProp.value = respCarac.data.flatTreeDataProp;
            treeDataProp.value = respCarac.data.treeDataProp;
        }

        dialogFormVisibleCaract.value = true;
    }

    const cerrarCarac = () => {
        dialogFormVisibleCaract.value = false;
    }

    const abrirReg = async () => {
        const respRegiones = await axios.get('/carga-regiones');

        if(respRegiones.status === 200){
            treeRegionDataProp.value = respRegiones.data.treeData;
            tiposDeRegionProp.value = respRegiones.data.todosLosTiposDeRegion;
            tiposDeRegionTreeProp.value = respRegiones.data.tiposDeRegionTree;
        }

        dialogFormVisibleReg.value = true;
    }

    const clickNomCom = (row) =>{
        observaciones.value = "";

        tablaNomComunReg.value = row.Regiones;
        
        if(row.Regiones != undefined)
        {
            totalRegionNomComun.value = row.Regiones.length;
            observaciones.value = row.Observaciones;
        }
        
        etiquetaObs.value = 'nombre común'
    } 

    const clickCaract = (row) => {
        observaciones.value = "";

        tablaCaractReg.value = row.Regiones

        if(row.Regiones != undefined){
            totalRegionCaract.value = row.Regiones.length;
        }

        etiquetaObs.value = 'caracteristicas'
    }

    const clickRegCaract = (row) => {
        observaciones.value = "";

        etiquetaObs.value = 'relacion con caracteristicas';
        observaciones.value = row.Observaciones;
    }

    const clickRegNomCom = (row) => {
        observaciones.value = "";
        
        etiquetaObs.value = 'relación con región';
        observaciones.value = row.ObservacionesReg;
    }

    const cerrarReg = () => {
        dialogFormVisibleReg.value = false;
    }

    const abrirNomCom = () => {
        dialogFormVisibleNomCom.value = true;
    }

    const cerrarNomCom= () => {
        dialogFormVisibleNomCom.value = false;
    }

    const Guardar = () => {
        console.log("Esta es la funcion de guardar");
    }

    onMounted( async () => {
        const respCarac = await axios.get('/cargar-caracteristicas');
        
        if(respCarac.status === 200)
        {
            flatTreeDataProp.value = respCarac.data.flatTreeDataProp;
            treeDataProp.value = respCarac.data.treeDataProp;

            const copiedData = deepCopy(respCarac.data.treeDataProp);
            sortNodesAlphabetically(copiedData);
            localTreeData.value = copiedData;
        }
        
        //Aqui se va a verificar los nombre comunes 

        const respNomCom = await axios.get(`/cargar-nomcomun-taxon/${props.taxonAct.id}`);
        
        if(respNomCom.status === 200)
        {
            tablaNomComun.value = respNomCom.data;
            totalRegNomComun.value = respNomCom.data.length;
        }

        const listCaract = await axios.get(`/cargaCaracTaxon/${props.taxonAct.id}`);

        if(listCaract.status === 200)
        {
            console.log("Estas son las caracteristicas: ", listCaract);
            tablaCaracteristicas.value = listCaract.data;
            totalRegCaract.value = listCaract.data.length;
        }
        
    });

</script>

<style scoped>
    .tree-card {
        width: 100%;
        max-width: 1600px;
        max-height: 590px;
        display: flex;
        flex-direction: column;
    }
    .table-wrapper :deep(.el-table__body tr.current-row > td) {
      background-color: #ddf6dd !important;
      color: #0d6efd !important;
      font-weight: bold;
    }
</style>
