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
                                                :mostrarNuevo = "true"
                                                :mostrarEditar = "true"
                                                :mostrarBorrar = "true"
                                                :mostrarSalir = "false"
                                                @row-click="clickCaract"
                                                @nuevo-item="nuevoRelCaract"/>
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
                                                :valoresOpcion = "tiposDistribucion"
                                                :habOpciones ="habOpciones"
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
                                                :mostrarNuevo = "true"
                                                :mostrarEditar = "true"
                                                :mostrarBorrar = "true"
                                                :mostrarSalir = "false"
                                                @row-click="clickNomCom"
                                                @nuevo-item="nuevoRelNomComun"/>
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
                            <el-tab-pane label="Regiones2" name="Region3">
                                <div style="height: 500px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                    <el-splitter lazy>
                                        <el-splitter-panel min="50">
                                            <div class="demo-panel panel-nombre">
                                                <el-container style="width:100%; height:100%;">
                                                    <el-header height="40px" style=" display:flex; justify-content:center; align-items:center;">
                                                        <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                                           Regiones asociadas al Taxón 
                                                        </span>
                                                    </el-header>
                                                    
                                                        <TablaFiltrable 
                                                            :columnas = "colDefRegionNombre" 
                                                            :datos = "regionesNombre"
                                                            :opciones-filtro = "opcionesFiltroRegCaract"
                                                            :totalItems = "totalRegionesNom"
                                                            :valoresOpcion = "tiposDistribucion"
                                                            :habOpciones ="habOpciones"
                                                            :itemsPerPage = 4
                                                            :mostrarBiblio = "true"
                                                            :mostrarAcci = "false"
                                                            :alturaTabla = 264
                                                            :highlight-current-row = "true"
                                                            :mostrarNuevo = "false"
                                                            :mostrarRegion = "true"
                                                            :mostrarGuardar = "true"
                                                            :mostrarEditar = "true"
                                                            :mostrarBorrar = "true"
                                                            :mostrarSalir = "false">                                                
                                                        </TablaFiltrable>
                                                    
                                                </el-container>
                                            </div>
                                        </el-splitter-panel>
                                        <el-splitter-panel min="50">
                                            <div class="demo-panel panel-carac">
                                                <el-container>
                                                    <el-header height="40px" style=" display:flex; justify-content:center; align-items:center;">
                                                        <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                                           Regiones asociadas al Taxón-Caracteristica
                                                        </span>
                                                    </el-header>
                                                    <TablaFiltrable 
                                                        :columnas = "colDefRegionCaract" 
                                                        :datos = "regionesCaract"
                                                        :opciones-filtro = "opcionesFiltroRegCaract"
                                                        :totalItems = "totalRegionesCaract"
                                                        :valoresOpcion = "tiposDistribucion"
                                                        :habOpciones ="habOpciones"
                                                        :itemsPerPage = 4
                                                        :mostrarBiblio = "true"
                                                        :mostrarAcci = "false"
                                                        :alturaTabla = 288
                                                        :highlight-current-row = "true"
                                                        :mostrarNuevo = "false"
                                                        :mostrarGuardar = "true"
                                                        :mostrarEditar = "true"
                                                        :mostrarBorrar = "false"
                                                        :mostrarSalir = "false">                                                
                                                    </TablaFiltrable>    
                                                </el-container>
                                            </div>
                                        </el-splitter-panel>
                                        <el-splitter-panel min="50">
                                            <div class="demo-panel panel-nomcomun">
                                                <el-container>
                                                    <el-header height="40px" style=" display:flex; justify-content:center; align-items:center;">
                                                        <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                                           Regiones asociadas al Taxón-Nombre Comun
                                                        </span>
                                                    </el-header>                                                    
                                                    <TablaFiltrable 
                                                        :columnas = "colDefRegionNomCom" 
                                                        :datos = "regionesNomCom"
                                                        :opciones-filtro = "opcionesFiltroRegCaract"
                                                        :totalItems = "totalRegionesNomCom"
                                                        :itemsPerPage = 5
                                                        :mostrarBiblio = "true"
                                                        :mostrarAcci = "false"
                                                        :alturaTabla = 304
                                                        :highlight-current-row = "true"
                                                        :mostrarNuevo = "false"
                                                        :mostrarEditar = "false"
                                                        :mostrarBorrar = "false"
                                                        :mostrarSalir = "false">                                                
                                                    </TablaFiltrable>
                                                </el-container>
                                            </div>
                                        </el-splitter-panel>
                                    </el-splitter>
                                </div>
                            </el-tab-pane>
                            <el-tab-pane label="Regiones3" name="Region4">
                                <div style="height: 500px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                    <el-splitter lazy>
                                        <el-splitter-panel collapsible min="50">
                                            <div class="demo-tree panel-nombre">
                                                <el-tree
                                                    class="tree-full"
                                                    :data="todasRegiones"
                                                    :props="defaultProps">                                                    
                                                    <template #default="{ node, data }">
                                                        <Logo class="tree-node-logo" :rutaCategoria="data.Biblio.url" />
                                                        <span :class="claseOrigen(data)">
                                                            {{ data.Region }}
                                                            <template v-if="data.TipoDistribucion">
                                                                ({{ data.TipoDistribucion.descripcion }})
                                                            </template>
                                                        </span>
                                                    </template>
                                                </el-tree>                                               
                                            </div>
                                        </el-splitter-panel>
                                        <el-splitter-panel collapsible min="50">
                                            <div class="demo-panel panel-carac">
                                                Aqui puedo mostar el detalle 
                                            </div>
                                        </el-splitter-panel>
                                    </el-splitter>
                                </div>
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

        <DialogForm v-model="dialogFormVisibleRelNomCom" :botCerrar="true" :pressEsc="false" :width="'90%'">
            <RelNomComun :modal="true" :taxonActual = props.taxonAct @cerrar="cerrarRelNomCom"/>
        </DialogForm>

        <DialogForm v-model="dialogFormVisibleRelCaract" :botCerrar="true" :pressEsc="false" :width="'90%'">
            <RelCaract :modal="true" :taxonActual = props.taxonAct @cerrar="cerrarRelCaract"/>
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
    import RelNomComun from '@/Pages/Socat/RelCatalogosAsociados/RelacionNomComun.vue';
    import RelCaract from '@/Pages/Socat/RelCatalogosAsociados/RelacionCaracteristicas.vue';
    import CuerpoNombreCom from '@/Pages/Socat/Nombres/CuerpoNombreComun.vue';
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
    import { onMounted, ref, watch } from 'vue';
    import GuardarButton from '@/Components/Biotica/GuardarButton.vue';
    import Logo from '@/Components/Biotica/LogoCategoria.vue';

    const props = defineProps({
        taxonAct: {
            type: Object
        },
    });

    const defaultProps = {
        children: 'children',
        label: 'Region',
    }


    const claseOrigen = (data) => {
        console.log("Esto es claseOrigen: ", data.origen);
        switch (data.origen) {

            case 'nombre':
                return 'origen-rojo';

            case 'caracteristica':
                return 'origen-verde';

            case 'nomComun':
                return 'origen-amarillo';
        }
    }




    const tabInicial = ref("NomComun");

    const localTreeData = ref([]);
    const localTreeDataTaxon = ref([]);
    const flatTreeDataProp = ref([]);
    const treeDataProp = ref([]);
    const dialogFormVisibleCaract = ref(false);
    const dialogFormVisibleReg = ref(false);
    const dialogFormVisibleNomCom = ref(false);

    const dialogFormVisibleRelNomCom = ref(false);
    const dialogFormVisibleRelCaract = ref(false);

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

    const tiposDistribucion = ref([]);
    const habOpciones = ref(true);

    const regionesNombre = ref([]);
    const regionesCaract = ref([]);
    const regionesNomCom = ref([]);

    const todasRegiones = ref([]);
    const totalRegiones = ref(0);

    const totalRegionesNom = ref(0);
    const totalRegionesCaract = ref(0);
    const totalRegionesNomCom = ref(0);
    
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
            align: 'left', tipo: 'lista', filtrable: true
        },
        {
            prop: 'Biblio', label: '', minWidth: '55', align: 'left',
            tipo: 'imagenTexto', filtrable: false
        },
    ]);

    const colDefRegionNombre = ref([
        {
            prop: 'Region', label: 'Región', minWidth: '120',
            align: 'left', tipo: 'Texto', filtrable: true
        },
        {
            prop: 'TipoDistribucion', label: 'Tipo Distribucion', minWidth: '120',
            align: 'left', tipo: 'lista', filtrable: true
        },
        {
            prop: 'Biblio', label: '', minWidth: '55', align: 'left',
            tipo: 'imagenTexto', filtrable: false
        },
    ]);

    const colDefRegionCaract = ref([
        {
            prop: 'Region', label: 'Región', minWidth: '120',
            align: 'left', tipo: 'Texto', filtrable: true
        },
        {
            prop: 'TipoDistribucion', label: 'Tipo Distribucion', minWidth: '120',
            align: 'left', tipo: 'lista', filtrable: true
        },
        {
            prop: 'Biblio', label: '', minWidth: '55', align: 'left',
            tipo: 'imagenTexto', filtrable: false
        },
    ]);

    const colDefRegionNomCom = ref([
        {
            prop: 'Region', label: 'Región', minWidth: '120',
            align: 'left', tipo: 'Texto', filtrable: true
        },
        {
            prop: 'Biblio', label: '', minWidth: '55', align: 'left',
            tipo: 'imagenTexto', filtrable: false
        },
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

            const regionTaxon = await axios.get(`/cargaRegionesTaxon/${props.taxonAct.id}`);

            if(regionTaxon.status === 200)
            {
                regionesNombre.value = regionTaxon.data.regPorNombre;
                totalRegionesNom.value = regionTaxon.data.regPorNombre.length;
                console.log("Regiones Nombre: ", regionesNombre.value);
                regionesCaract.value = regionTaxon.data.regPorCaract;
                totalRegionesNom.value = regionTaxon.data.regPorCaract.length;
                console.log("Regiones Caracteristicas: ", regionesCaract.value);
                regionesNomCom.value = regionTaxon.data.regPorNomCom;
                totalRegionesNom.value = regionTaxon.data.regPorCaract.length;
                console.log("Regiones nombre comun: ", regionesNomCom.value);
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

    const cerrarRelNomCom = () => {
        dialogFormVisibleRelNomCom.value = false;
    }

    const cerrarRelCaract = () => {
        dialogFormVisibleRelCaract.value = false;
    }
    

    const nuevoRelNomComun = () => {
        dialogFormVisibleRelNomCom.value = true;
    }

    const nuevoRelCaract = () => {
        dialogFormVisibleRelCaract.value = true;
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

        const tiposDistrib = await axios.get('/carga-tipos-distribucion');

        if(tiposDistrib.status === 200)
        {
            tiposDistribucion.value = tiposDistrib.data;
        }

        const regionTaxon = await axios.get(`/cargaRegionesTaxon/${props.taxonAct.id}`);

        if(regionTaxon.status === 200)
        {
            
            regionesNombre.value = regionTaxon.data.regPorNombre;
            totalRegionesNom.value = regionTaxon.data.regPorNombre.length;
            
            regionesCaract.value = regionTaxon.data.regPorCaract;
            totalRegionesCaract.value = regionTaxon.data.regPorCaract.length;
            
            regionesNomCom.value = regionTaxon.data.regPorNomCom;
            totalRegionesNomCom.value = regionTaxon.data.regPorCaract.length;
            
            todasRegiones.value = regionTaxon.data.todas;
            totalRegiones.value = regionTaxon.data.todas.length;

            console.log("Estas son todas las regiones: ", todasRegiones.value);
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

    /* Cabecera de la tabla */
    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1px 16px;
      background: #d9e1eb;
      border-bottom: 1px solid #f5ebeb;
    }

    .table-title {
      font-size: 14px;
      font-weight: 600;
      color: #303133;
    }

    /*.demo-panel {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
    }*/

    .demo-panel {
        width: 100%;
        height: 100%;
    }

    .demo-tree {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
    }

    .tree-full {
    width: 100%;
    height: 100%;
    overflow: auto;
}

    .panel-nombre{
        background: rgb(241, 189, 189);
         width: 100%;
    height: 100%;
    overflow: auto;
    }

    .panel-carac{
        background: rgb(189, 231, 241);
    }
    
    .panel-nomcomun{
        background: rgb(236, 241, 189);
    }

    .origen-rojo {
        color: #d32f2f;
        font-weight: bold;
    }

    .origen-verde {
        color: #0556cf;
        font-weight: bold;
    }

    .origen-amarillo {
        color: #b8860b;
        font-weight: bold;
    }
</style>
