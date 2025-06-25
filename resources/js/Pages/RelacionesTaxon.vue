<template>
    <div class="common-layout">
        <div class="table-wrapper">
            <el-card class="box-card">
                <el-container>
                    <el-header class="header">
                        <h3 class="titulo">
                            Relaciones taxonómicas
                        </h3>
                        
                    </el-header>
                    <el-main class="contenido">                        
                        <div>
                            <h1 class="subtitulo">
                                <span style="color: red;">{{ props.taxonAct.label }}</span>
                            </h1>
                            <el-row style="padding: 8px;">
                                <el-col :xs="24" :sm="24" :md="12" :lg="8"style="padding: 8px;">
                                    <span style="display: block; margin-bottom: 2px;">Relaciones nomenclaturales</span>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <el-cascader
                                            :options="tiposRel"
                                            clearable
                                            filterable
                                            v-model="tipRel"
                                            placeholder="Seleccione"
                                            @change="cargaRelaciones"
                                            style="flex-grow: 1;">
                                        </el-cascader>
                                        <el-tooltip class="item" 
                                                        effect="dark" 
                                                        content="Catálogo de Relaciones taxonómicas" 
                                                        placement="bottom">
                                            <el-button 
                                                @click="catalogoRelTax()" 
                                                type="primary" 
                                                circle
                                                color="#2420b4">
                                                <el-icon><Rompecabezas /></el-icon>
                                            </el-button>
                                        </el-tooltip>
                                    </div>
                                </el-col>
                                <el-col :xs="15" :sm="15" style="padding: 12px;">
                                    <el-row :gutter="16" style="margin: 0 10px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <el-col :span="10" style="padding: 8px; border-right: 1px solid #ebeef5;">
                                                <div style="margin-left: 8px;"> 
                                                    <span class="demo-input-label">Catálogo(s)</span>
                                                    <el-input type="textarea" :rows="2" placeholder="Catálogos" v-model="catalogos" :disabled="true">
                                                    </el-input>
                                                </div>
                                            </el-col>
                                            <el-col :span="10" style="padding: 8px;">
                                                <div style="margin-left: 8px;">
                                                    <span class="demo-input-label">Grupo SCAT</span>
                                                    <el-input type="textarea" :rows="2" placeholder="Grupo SCAT" v-model="grupos" :disabled="true">
                                                    </el-input>
                                                </div>
                                            </el-col> 
                                            <el-col :span="2" style="padding: 8px;display: flex; align-items: center; justify-content: center;">
                                            <el-tooltip class="item" 
                                                        effect="dark" 
                                                        content="Catálogo de Grupos taxonómicos" 
                                                        placement="bottom">
                                                    <el-button @click="filtro_Catalogos()" 
                                                            type="primary" 
                                                            circle 
                                                            color="#5bb1f0">
                                                        <el-icon><Conectado /></el-icon>
                                                    </el-button>
                                                </el-tooltip>
                                            </el-col> 
                                        </div>
                                    </el-row>
                                </el-col>
                            </el-row>  
                            Este es el 1: {{ mensajes.validaciones.nombreRequerido }}
                            <el-row style="padding: 8px;">
                                <el-col :xs="24" :sm="12" :md="6" style="padding: 8px; display: flex; flex-direction: column;">
                                    <span class="block">Nivel taxonómico</span>
                                    <el-cascader :options="categoriasTax" clearable filterable v-model="categ"
                                        placeholder="Seleccione" @change="handleChange">
                                    </el-cascader>
                                </el-col>
                            </el-row>
                            <el-row>
                                <div style="text-align: center">
                                    <el-transfer
                                        v-model="selectedValues"
                                        :data="transferData"
                                        filterable
                                        :titles="[etiquetaList1, etiquetaList2]">
                                        <template #default="{ option }">
                                            <div style="display: flex; align-items: center;">
                                                <i class = "icon">
                                                    <img :src = "'/storage/images/7lYNZw1WlcDnyANR1mr6IlrlbZT2f4WHlSpK3mKH.png'" style="width: 28px; height: 28px">
                                                </i>

                                                <el-tooltip content="Información">
                                                    <el-icon @click.prevent="openDialog(option)">
                                                        <!-- Agregamos el @click.stop y el openDialog -->
                                                        <InfoFilled />
                                                    </el-icon>
                                                </el-tooltip>

                                                <span style="margin-left: 8px;">{{ option.label }}</span>
                                            </div>
                                        </template>
                                    </el-transfer>
                                </div>
                            </el-row>
                        </div>
                        
                    </el-main>
                </el-container>
            </el-card>
        </div>
        <DialogForm v-model="dialogFormVisibleCat" :botCerrar="true" :pressEsc="true">
            <FiltroGrupos :grupos="gruposTax" @cerrar="cerrarDialog" @regresaGrupos="recibeGrupos" />
        </DialogForm>
    </div>
</template>

<script setup>
    import {ref, onMounted, watchEffect} from 'vue';
    import { Setting, User, Location, ShoppingCart, InfoFilled } from '@element-plus/icons-vue';
    import FiltroGrupos from '@/Pages/Socat/NombreTaxonomico/FiltroGrupoTax.vue';
    import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
    import Logo from '@/Components/Biotica/LogoCategoria.vue';
    import Rompecabezas from '@/Components/Biotica/Icons/Rompecabezas.vue';
    import Conectado from '@/Components/Biotica/Icons/Conectado.vue';
    import { mensajes } from '@/Composables/mensajes';

    const tiposRel = ref([]);
    const tipRel = ref("");
    const dialogFormVisibleCat = ref(false);
    const categ = ref(null);
    const catego = ref('');
    const catalogos = ref('');
    const grupos = ref('');
    const idsGrupos = ref('');
    const etiquetaList1 = ref('');
    const etiquetaList2 = ref('');
//------------------------------------------------
    const leftValue = ref([]);
    const selectedValues = ref([]);
    const transferData = [
  {
    key: 1,
    label: 'Usuario',
    icon: User, // Icono de Element Plus
  },
  {
    key: 2,
    label: 'Ubicación',
    icon: Location,
  },
  {
    key: 3,
    label: 'Carrito',
    icon: ShoppingCart,
  },
];

const openDialog = async (nodo) => {
    console.log(nodo.label);    
    alert("Estoy mandando " + nodo.label);
};
//-------------------------------------------------


    const props = defineProps({
        taxonAct: {
            type: Object
        },
        categoriasTax: {
            type: Object,
            required: true,
        },
        gruposTax: {
            type: Object,
            required: true,
        },
        catalogPadre: {
            type: String,
            required: true,
        },
        gruposPadre: {
            type: String,
            required: true,
        },
        idsGruposPadre: {
            type: String,
            required: true
        }
    });

    // Función para cargar las variables del componente padre en el hijo 
    const cargaGrupos = async () => {
        tipRel.value = "";
        categ.value = "";
        etiquetaList1.value = "Categoría";
        etiquetaList2.value = "Tipo relación";
        catalogos.value = props.catalogPadre;
        grupos.value = props.gruposPadre;
        idsGrupos.value = props.idsGruposPadre;
    };

    onMounted(async () => {
        const response = await axios.get('/cargar-tipoRel');
        if (response.status === 200) {
            tiposRel.value = response.data;
        }
        
        cargaGrupos();
    });

    watchEffect(() => {
        if (props.gruposPadre) {
            cargaGrupos();
        }
    });

    //Declaración de Funciones
    const filtro_Catalogos = () => {
        //Funcion para abrir el modal que muestra los catalogos taxonómicos
        dialogFormVisibleCat.value = true;
        console.log("Estoy en el llamado de lista de grupos");
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

    //Esta funcion se dispara una vez que se selecciona una categia taxonomica
    const handleChange = async (value) => {
        console.log(value);
         console.log(props.categoriasTax);
        const etiqueta = props.categoriasTax.find(categoria => categoria.value === value[0]);
        etiquetaList1.value = etiqueta.label;
        console.log("Se dispara para cargar la lista de taxones");
    };

    //Funcion para recuperar la relacion taxonomica 
    const cargaRelaciones = async(value) => {
        let idsNombreSin = 0;
        let idsNombreVal = 0;
        let params = {};
   
        const etiqueta = tiposRel.value.find(tipoRel => tipoRel.value === value[value.length - 1]);
        
        if(etiqueta != undefined)
        {
            etiquetaList2.value = etiqueta.label 
        }

        for (const child of tiposRel.value) {
            if (child.children && child.children.length > 0) {
                const found = await updateChildNode(child.children, value[value.length - 1]);
            } 
        }
        //Aqui se valida la recuperación de los sinonimos o validos confirmando si es un objeto o un array
        if (props.taxonAct?.completo?.nombre_rel !== undefined && 
            props.taxonAct.completo.nombre_rel !== null) 
        {     
            // Convertir a array si no lo es
            const relaciones = Array.isArray(props.taxonAct.completo.nombre_rel) 
                ? props.taxonAct.completo.nombre_rel 
                : [props.taxonAct.completo.nombre_rel];
            
            // Filtrar y mapear
            idsNombreSin = relaciones
                .filter(r => r.IdTipoRelacion === 1)
                .map(r => r.IdNombreRel);
            
            console.log("Estas son los sinonimos: ", idsNombreSin.length);
        }
        
        if (props.taxonAct?.completo?.nombre_rel_val !== undefined && 
            props.taxonAct.completo.nombre_rel_val !== null) 
        {            
            // Convertir a array si no lo es
            const relaciones = Array.isArray(props.taxonAct.completo.nombre_rel_val) 
                ? props.taxonAct.completo.nombre_rel_val 
                : [props.taxonAct.completo.nombre_rel_val];
            
            // Filtrar y mapear
            idsNombreVal = relaciones
                .filter(r => r.IdTipoRelacion === 1)
                .map(r => r.IdNombre);
            
            console.log("Estas son los validos: ", idsNombreVal.length);
        }
        if(idsNombreSin.length > 0)
        {
            params = {ids: idsNombreSin};
        }
        else
        {
            params = {ids: idsNombreVal};
        }
        console.log("Estos son los parametros a pasar: ", params);

        const response = await axios.get('/cargar-relaciones', { params });
      
        if (response.status === 200) {
            data.value = response.data[0];
            totalReg.value = response.data[1].total;
            paginas.value = response.data[1].last_page;
        }
        else {
            console.log("Se presentó un error en la recuperación de los datos");
        }
    }

    //Funcion recursiva para encontrar el elemento de la relacion taxonomica en los hijos
    const updateChildNode = async (children, res) => {
        for (const child of children) {
            if (child.value === res) {
                console.log("Lo encontre: ", child);
                etiquetaList2.value = child.label;
                return true; 
            }
            
            if (child.children && child.children.length > 0) {
                const found = await updateChildNode(child.children, res);
                if (found) return true;
            }
        }
        return false; // Nodo no encontrado en los hijos
    };

    const cerrarDialog = (valor) => {
        dialogFormVisibleCat.value = valor; // Cambia la visibilidad del diálogo
    };

</script>
<style>
    .titulo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem; /* Añadir margen inferior para separar del contenido */
        }

    .subtitulo {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 0.5rem; /* Añadir margen inferior para separar del contenido */
    }

    .header {
        text-align: left;
        padding: 0.5rem; /* Reducir el padding en móviles */
        background-color: #f5f5f5;
        border-bottom: 1px solid #ddd;
    }
    /* Aumentar el ancho del panel derecho */
    .el-transfer-panel:last-child {
        width: 220px; /* Ajusta este valor según necesites */
    }

    /* Asegurar que el contador tenga espacio */
    .el-transfer-panel__header .el-checkbox__label span {
        min-width: 10px;
        display: inline-block;
    }

    /*Estos son los estilos para reescribir los botones el el-transfer */
    .el-transfer {
        display: flex;
        flex-direction: row; /* alineación horizontal */
        justify-content: center;
        align-items: center;
        gap: 16px; /* espacio entre listas y botones */
    }

    /* Paso 2: Comportamiento responsivo si el espacio es pequeño */
    @media (max-width: 600px) {
        .el-transfer {
            flex-direction: column; /* vertical en pantallas pequeñas */
            align-items: center;
        }
    }

    /* Paso 3: Tus estilos para botones */
    .el-transfer__buttons {
        display: flex;
        flex-direction: row; /* horizontal por defecto */
        justify-content: center;
        align-items: center;
        gap: 8px;
    }

    .el-transfer__buttons .el-button {
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        padding: 0;
    }

    .el-transfer__buttons .el-button .el-icon {
        display: none !important;
    }

    .el-transfer__buttons .el-button:first-child::after {
        content: '';
        display: inline-block;
        width: 20px;
        height: 20px;
        background: url('/storage/images/punto-de-la-mano-hacia-atras-a-la-izquierda.svg') no-repeat center center;
        background-size: contain;
    }

    .el-transfer__buttons .el-button:last-child::after {
        content: '';
        display: inline-block;
        width: 20px;
        height: 20px;
        background: url('/storage/images/punto-de-la-mano-hacia-atras-a-la-derecha.svg') no-repeat center center;
        background-size: contain;
    }
</style>