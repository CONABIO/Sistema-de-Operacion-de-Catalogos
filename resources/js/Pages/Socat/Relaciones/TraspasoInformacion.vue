<template>
    <div>
        <el-card class="box-card">
            <div class="common-layout">
                <el-container style="height: 98vh;">
                    <el-header class="header">
                        <div class="header-content">
                            <h2 class="titulo">Traspaso de catálogos relacionados con el taxón</h2>
                        </div>
                    </el-header>  
                        
                    <el-main style="padding: 15px; background: #fff; overflow: hidden;">    
                        <el-row justify="space-between" align="middle">

                            <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                {{ props.taxonAct.label }} 
                            </span>

                            <div style="display: flex; gap: 50px;">
                                <BotonSalir accion="cerrar" @salir="cerrarDialogo"
                                                style="flex-shrink: 0; min-width: max-content;"/>
                            </div>
                        </el-row>
                        <br/>
                        <el-row style="width: 100%;">
                            <el-tabs v-model="tabActiva" type="card" style="width: 100%;">
                                <!--Pestaña para mostrar los nombres comunes-->
                                <el-tab-pane label="Nombre(s) común(es)" name="NomComun" :disabled ="nombresComunes.length === 0" 
                                             style="border: 3px solid blue;">
                                    <div :class="{ 'contenido-deshabilitado': nombresComunes.length === 0 }">
                                        <el-splitter class="splitter-full">
                                            <el-splitter-panel size="48%">
                                                <el-card style="min-height: 500px;">
                                                    <el-container>
                                                        <el-header class="headerInterno">
                                                            <div class="header-content">
                                                                <h1 class="titulo-interno">{{ nombreValido }}</h1>
                                                            </div>
                                                        </el-header>
                                                        <div>
                                                            <div v-show="!checkAllVal" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllVal" 
                                                                            @change="marcarTodos(arbolNomComVal, nomComTreeVal, 'id', true)">
                                                                    Marcar todos
                                                                </el-checkbox> 
                                                            </div>
                                                            <div v-show="checkAllVal" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllVal" 
                                                                            @change="marcarTodos(arbolNomComVal, nomComTreeVal, 'id', false)">
                                                                    Desmarcar todos
                                                                </el-checkbox>                                                        
                                                            </div>
                                                        </div>
                                                        <div class="arbol-wrapper">
                                                            <el-tree :data="nomComTreeVal"
                                                                    :props="{ children: 'Regiones' }"
                                                                    ref="arbolNomComVal"
                                                                    node-key="id"
                                                                    :highlight-current="true" 
                                                                    show-checkbox>
                                                                <template #default="{ node, data }">
                                                                    <span
                                                                        style="display: flex; align-items: center; gap: 8px; width: 100%;"
                                                                        >
                                                                        <template v-if="data.NombreComun">
                                                                            <span
                                                                                style="font-weight: bold; font-size: 13px; color: #333;">
                                                                                {{ data.NombreComun }}
                                                                                <span v-if="data.Lengua"
                                                                                    style="font-weight: normal; color: #606266; font-size: 12px; margin-left: 5px;">
                                                                                    ({{ data.Lengua }})
                                                                                </span>
                                                                            </span>
                                                                        </template>

                                                                        <template v-else>
                                                                            <Logo :rutaCategoria="data.Biblio?.url"
                                                                                style="width: 18px; height: 18px; flex-shrink: 0;" />
                                                                            <span style="font-size: 12px; color: #606266;">
                                                                                {{ data.Region }}
                                                                            </span>
                                                                        </template>
                                                                    </span>
                                                                </template>
                                                            </el-tree>
                                                        </div> 
                                                    </el-container>
                                                </el-card>
                                            </el-splitter-panel>
                                            <el-splitter-panel size="4.5%">
                                                <div
                                                    style="
                                                        display: flex;
                                                        justify-content: center;
                                                        align-items: center;
                                                        width: 100%;
                                                        height: 100%;
                                                    ">
                                                    <el-tooltip
                                                        effect="dark"
                                                        content="Quitar del válido"
                                                        placement="top"
                                                    >
                                                        <el-button
                                                            @click="transferirSelccionados(
                                                                arbolNomComSin,
                                                                nombresComunes,
                                                                nomComTreeVal,
                                                                'IdNomComun',
                                                                'NomCom'
                                                            )"
                                                            circle
                                                            color="#8e44ad"
                                                        >
                                                            <el-icon>
                                                                <iconoTraspaso :rotacion="90" />
                                                            </el-icon>
                                                        </el-button>
                                                    </el-tooltip>
                                                </div>
                                            </el-splitter-panel>
                                            <el-splitter-panel size="48%">
                                                <el-card style="min-height: 500px;">
                                                    <el-container>
                                                        <el-header class="headerInterno">
                                                            <div class="header-content">
                                                                <h1 class="titulo-interno">{{ nombreSinonimo }}</h1>
                                                            </div>
                                                        </el-header>
                                                        <div>
                                                            <div v-show="!checkAllSin" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllSin" 
                                                                            @change="marcarTodos(arbolNomComSin, nombresComunes, 'id', true)">
                                                                    Marcar todos
                                                                </el-checkbox> 
                                                            </div>
                                                            <div v-show="checkAllSin" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllSin" 
                                                                            @change="marcarTodos(arbolNomComSin, nombresComunes, 'id', false)">
                                                                    Desmarcar todos
                                                                </el-checkbox>                                                        
                                                            </div>
                                                        </div>
                                                        <div class="arbol-wrapper">
                                                            <el-tree :data="nombresComunes"
                                                                    :props="{ children: 'Regiones' }"
                                                                    ref="arbolNomComSin"
                                                                    node-key="id"
                                                                    :highlight-current="true" 
                                                                    show-checkbox>
                                                                <template #default="{ node, data }">
                                                                    <span
                                                                        style="display: flex; align-items: center; gap: 8px; width: 100%;"
                                                                        >
                                                                        <template v-if="data.NombreComun">
                                                                            <span
                                                                                style="font-weight: bold; font-size: 13px; color: #333;">
                                                                                {{ data.NombreComun }}
                                                                                <span v-if="data.Lengua"
                                                                                    style="font-weight: normal; color: #606266; font-size: 12px; margin-left: 5px;">
                                                                                    ({{ data.Lengua }})
                                                                                </span>
                                                                            </span>
                                                                        </template>

                                                                        <template v-else>
                                                                            <Logo :rutaCategoria="data.Biblio?.url"
                                                                                style="width: 18px; height: 18px; flex-shrink: 0;" />
                                                                            <span style="font-size: 12px; color: #606266;">
                                                                                {{ data.Region }}
                                                                            </span>
                                                                        </template>
                                                                    </span>
                                                                </template>
                                                            </el-tree>
                                                        </div> 
                                                    </el-container>
                                                </el-card>
                                            </el-splitter-panel>
                                        </el-splitter>
                                    </div>
                                </el-tab-pane>
                                <!--Pestaña para mostrar las caracteristicas-->                               
                                <el-tab-pane label="Caracteristicas" name="Caracteristicas" :disabled ="caracteristicas.length === 0"
                                             style="border: 3px solid green;">
                                    <div :class="{ 'contenido-deshabilitado': caracteristicas.length === 0 }">
                                        <el-splitter class="splitter-full">
                                            <el-splitter-panel size="48%">
                                                <el-card style="min-height: 500px;">
                                                    <el-container>
                                                        <el-header class="headerInterno">
                                                            <div class="header-content">
                                                                <h1 class="titulo-interno">{{ nombreValido }}</h1>
                                                            </div>
                                                        </el-header>
                                                        <div>
                                                            <div v-show="!checkAllVal" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllVal" 
                                                                            @change="marcarTodos(arbolCaractVal, caractTreeVal, 'treeKey', true)">
                                                                    Marcar todos
                                                                </el-checkbox> 
                                                            </div>
                                                            <div v-show="checkAllVal" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllVal" 
                                                                            @change="marcarTodos(arbolCaractVal, caractTreeVal, 'treeKey', false)">
                                                                    Desmarcar todos
                                                                </el-checkbox>                                                        
                                                            </div>
                                                        </div>
                                                        <div class="arbol-wrapper">
                                                            <el-tree :data="caractTreeVal"
                                                                    :props="{ label: 'label', children: 'children' }"
                                                                    ref="arbolCaractVal"
                                                                    node-key="treeKey"
                                                                    :highlight-current="true" 
                                                                    show-checkbox>
                                                                <template #default="{ data }">
                                                                    <div class="tree-node-wrapper"
                                                                    :class="{ 'nodo-region': data.tipo === 'region' }"
                                                                    >
                                                                        <Logo class="tree-node-logo" :rutaCategoria="data.biblio" />
                                                                        <span class="nodo-texto">{{ data.label }}</span>
                                                                    </div>
                                                                </template>
                                                            </el-tree>  
                                                        </div> 
                                                    </el-container>
                                                </el-card>
                                            </el-splitter-panel>
                                            <el-splitter-panel size="4.5%">
                                                <div 
                                                    style="
                                                        display: flex;
                                                        justify-content: center;
                                                        align-items: center;
                                                        width: 100%;
                                                        height: 100%;
                                                    ">
                                                    <el-tooltip effect="dark" content="Quitar del valido" placement="top">
                                                        <el-button @click="transferirSelccionados(arbolCaractSin, caractTree, caractTreeVal, 'id', 'Caract')" circle color="#8e44ad" 
                                                                    style="margin-left: 10px;">
                                                            <el-icon>
                                                            <iconoTraspaso :rotacion="90"/>
                                                            </el-icon>
                                                        </el-button>
                                                    </el-tooltip>
                                                </div>
                                            </el-splitter-panel>
                                            <el-splitter-panel size="48%">
                                                <el-card style="min-height: 500px;">
                                                    <el-container>
                                                        <el-header class="headerInterno">
                                                            <div class="header-content">
                                                                <h1 class="titulo-interno">{{ nombreSinonimo }}</h1>
                                                            </div>
                                                        </el-header>
                                                        <div>
                                                            <div v-show="!checkAllSin" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllSin" 
                                                                            @change="marcarTodos(arbolCaractSin, caractTree, 'treeKey', true)">
                                                                    Marcar todos
                                                                </el-checkbox>
                                                            </div>
                                                            <div v-show="checkAllSin" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllSin" 
                                                                            @change="marcarTodos(arbolCaractSin, caractTree, 'treeKey', false)">
                                                                    Desmarcar todos
                                                                </el-checkbox>                                                        
                                                            </div>
                                                        </div>
                                                        <div class="arbol-wrapper">
                                                            <el-tree :data="caractTree"
                                                                    :props="{ label: 'label', children: 'children' }"
                                                                    ref="arbolCaractSin"
                                                                    node-key="treeKey"
                                                                    :highlight-current="true" 
                                                                    show-checkbox>
                                                                <template #default="{ data }">
                                                                    <div class="tree-node-wrapper"
                                                                    :class="{ 'nodo-region': data.tipo === 'region' }"
                                                                    >
                                                                        <Logo class="tree-node-logo" :rutaCategoria="data.biblio" />
                                                                        <span class="nodo-texto">{{ data.label }}</span>
                                                                    </div>
                                                                </template>
                                                            </el-tree>  
                                                        </div> 
                                                    </el-container>
                                                </el-card>
                                            </el-splitter-panel>
                                        </el-splitter>
                                    </div>         
                                </el-tab-pane>
                                <!--Pestaña para mostrar las regiones-->
                                <el-tab-pane label="Regiones" name="Regiones" :disabled ="regiones.length === 0" 
                                                style="border: 3px solid red;">
                                    <div :class="{ 'contenido-deshabilitado': regiones.length === 0 }"> 
                                        <el-splitter class="splitter-full">
                                            <el-splitter-panel size="48%">
                                                <el-card style="min-height: 500px;">
                                                    <el-container>
                                                        <el-header class="headerInterno">
                                                            <div class="header-content">
                                                                <h1 class="titulo-interno">{{ nombreValido }}</h1>
                                                            </div>
                                                        </el-header>
                                                        <div>
                                                            <div v-show="!checkAllVal" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllVal" 
                                                                            @change="marcarTodos(arbolRegVal, regionesValido, 'IdRegion', true)">
                                                                    Marcar todos
                                                                </el-checkbox> 
                                                            </div>
                                                            <div v-show="checkAllVal" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllVal" 
                                                                            @change="marcarTodos(arbolRegVal, regionesValido, 'IdRegion', false)">
                                                                    Desmarcar todos
                                                                </el-checkbox>
                                                            </div>
                                                        </div>
                                                        <div class="arbol-wrapper">
                                                            <el-tree :data="regionesValido"
                                                                    :props="{ children: 'Regiones' }" 
                                                                    ref="arbolRegVal"
                                                                    node-key="IdRegion"
                                                                    highlight-current 
                                                                    show-checkbox>
                                                                <template #default="{ node, data }">
                                                                    <span
                                                                        style="display: flex; align-items: center; gap: 8px; width: 100%;"
                                                                        >
                                                                        <template v-if="data.NombreComun">
                                                                            <span
                                                                                style="font-weight: bold; font-size: 13px; color: #333;">
                                                                                {{ data.NombreComun }}
                                                                                <span v-if="data.Lengua"
                                                                                    style="font-weight: normal; color: #606266; font-size: 12px; margin-left: 5px;">
                                                                                    ({{ data.Lengua }})
                                                                                </span>
                                                                            </span>
                                                                        </template>

                                                                        <template v-else>
                                                                            <Logo :rutaCategoria="data.Biblio?.url"
                                                                                style="width: 18px; height: 18px; flex-shrink: 0;" />
                                                                            <span style="font-size: 12px; color: #606266;">
                                                                                {{ data.Region }}
                                                                            </span>
                                                                        </template>
                                                                    </span>
                                                                </template>
                                                            </el-tree>  
                                                        </div> 
                                                    </el-container>
                                                </el-card>
                                            </el-splitter-panel>
                                            <el-splitter-panel size="4.5%">
                                           <div 
                                                style="
                                                    display: flex;
                                                    justify-content: center;
                                                    align-items: center;
                                                    width: 100%;
                                                    height: 100%;
                                                ">
                                                <el-tooltip effect="dark" content="Quitar del valido" placement="top">
                                                    <el-button @click="transferirSelccionados(arbolRegSin, regiones, regionesValido, 'IdRegion', 'Region')" circle color="#8e44ad" 
                                                                style="margin-left: 10px;">
                                                        <el-icon>
                                                        <iconoTraspaso :rotacion="90"/>
                                                        </el-icon>
                                                    </el-button>
                                                </el-tooltip>
                                            </div>
                                            </el-splitter-panel>
                                            <el-splitter-panel size="48%">
                                                <el-card style="min-height: 500px;">
                                                    <el-container>
                                                        <el-header class="headerInterno">
                                                            <div class="header-content">
                                                                <h1 class="titulo-interno">{{ nombreSinonimo }}</h1>
                                                            </div>
                                                        </el-header>
                                                        <div>
                                                            <div v-show="!checkAllSin" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllSin" 
                                                                            @change="marcarTodos(arbolRegSin, regiones, 'IdRegion', true)">
                                                                    Marcar todos
                                                                </el-checkbox> 
                                                            </div>
                                                            <div v-show="checkAllSin" class="header-content">
                                                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAllSin" 
                                                                            @change="marcarTodos(arbolRegSin, regiones, 'IdRegion', false)">
                                                                    Desmarcar todos
                                                                </el-checkbox>
                                                            </div>
                                                        </div>
                                                        <div class="arbol-wrapper">
                                                            <el-tree :data="regiones"
                                                                    :props="{ children: 'Regiones' }" 
                                                                    ref="arbolRegSin"
                                                                    node-key="IdRegion"
                                                                    highlight-current 
                                                                    show-checkbox>
                                                                <template #default="{ node, data }">
                                                                    <span
                                                                        style="display: flex; align-items: center; gap: 8px; width: 100%;"
                                                                        >
                                                                        <template v-if="data.NombreComun">
                                                                            <span
                                                                                style="font-weight: bold; font-size: 13px; color: #333;">
                                                                                {{ data.NombreComun }}
                                                                                <span v-if="data.Lengua"
                                                                                    style="font-weight: normal; color: #606266; font-size: 12px; margin-left: 5px;">
                                                                                    ({{ data.Lengua }})
                                                                                </span>
                                                                            </span>
                                                                        </template>

                                                                        <template v-else>
                                                                            <Logo :rutaCategoria="data.Biblio?.url"
                                                                                style="width: 18px; height: 18px; flex-shrink: 0;" />
                                                                            <span style="font-size: 12px; color: #606266;">
                                                                                {{ data.Region }}
                                                                            </span>
                                                                        </template>
                                                                    </span>
                                                                </template>
                                                            </el-tree>  
                                                        </div> 
                                                    </el-container>
                                                </el-card>
                                            </el-splitter-panel>
                                        </el-splitter>
                                    </div>                                                              
                                </el-tab-pane>
                            </el-tabs>
                        </el-row>
                    </el-main>
                </el-container>
            </div>
        </el-card>
    </div>
</template>

<script setup>
    import { onMounted, ref, computed, watch, nextTick } from 'vue';
    import BotonSalir from '@/Components/Biotica/SalirButton.vue';
    import arbolCheck from "@/Components/Biotica/ArbCheck.vue";
    import Logo from '@/Components/Biotica/LogoCategoria.vue';
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
    import iconoTraspaso from "@/Components/Biotica/Icons/TraspasoInfo.vue";
    import GuardarButton from '@/Components/Biotica/GuardarButton.vue';

    const tabActiva = ref("NomComun");

    const nombreValido = ref("");
    const nombreSinonimo = ref("");

    const idNombreVal = ref(0);
    const idNombreSin = ref(0);

    const nombresComunes = ref([]);
    const caracteristicas = ref([]);
    const caracteristicasValido = ref([]);
    const regiones= ref([]);
    const regionesValido = ref([]);
    
    const regionesTransfer = ref([]);

    const arbolNomComVal = ref([]);
    const arbolNomComSin = ref([]);
    const nomComTreeVal = ref([]);

    const arbolCaractVal = ref([]);
    const arbolCaractSin = ref([]);
    const caractTreeVal = ref([]);

    const arbolRegVal = ref(null);
    const arbolRegSin = ref(null);
    const checkAllVal = ref(false);
    const checkAllSin = ref(false);
    const isIndeterminate = ref(false);

    const emit = defineEmits(['cerrar']);
    const props = defineProps({
        taxonAct: {
            type: Object
        },
        sinonimo: {
            type: Object
        }    
    });

    const cerrarDialogo = () => {
        emit('cerrar');
    };

    onMounted( async () => {
        let resp;

        //resp = await axios.get(`/carga-taxon/${props.taxonAct.id}`);
       
        if(props.taxonAct.estatus === "Sinonimo"){
            resp = await axios.get(`/cargar-nomcomun-taxon/${props.taxonAct.id}`);
            nombresComunes.value = resp.data;

            resp = await axios.get(`/cargaCaracTaxon/${props.taxonAct.id}`);
            caracteristicas.value = resp.data;

            resp = await axios.get(`/cargaRegionesTaxon/${props.taxonAct.id}`);
            regiones.value = resp.data.regPorNombre;
            regionesTransfer.value = resp.data.regPorNombre;          
        }else if(props.sinonimo.Nombrecompleto.estatus === "Sinonimo") {
            resp = await axios.get(`/cargar-nomcomun-taxon/${props.sinonimo.idNombre}`);
            nombresComunes.value = resp.data;
            
            resp = await axios.get(`/cargaCaracTaxon/${props.sinonimo.idNombre}`);
            caracteristicas.value = resp.data;
            
            resp = await axios.get(`/cargaRegionesTaxon/${props.sinonimo.idNombre}`);
            regiones.value = resp.data.regPorNombre;
            regionesTransfer.value = resp.data.regPorNombre;
        }

        if(props.taxonAct.estatus === "Sinonimo"){
            nombreValido.value = props.sinonimo.Nombrecompleto.texto;
            nombreSinonimo.value = props.taxonAct.label;
            console.log("Este es el sinonimo: ", props.sinonimo);
            console.log("Este es el taxonAct: ", props.taxonAct);
            idNombreVal.value = props.sinonimo.idNombre;
            idNombreSin.value = props.taxonAct.id;
        }else{
            nombreValido.value = props.taxonAct.label;
            nombreSinonimo.value =props.sinonimo.Nombrecompleto.texto;
            console.log("Este es el taxonAct: ", props.taxonAct);
            console.log("Este es el sinonimo: ", props.sinonimo);
            idNombreVal.value = props.taxonAct.id;
            idNombreSin.value = props.sinonimo.idNombre;
        }
    });

    const colDefRegionNombre = ref([
        {
            prop: 'Region', label: 'Región', minWidth: '120',
            align: 'left', tipo: 'Texto', filtrable: true
        },
    ]);

    const regionesNomComFull = computed(() => {
        let lista = [];
        nombresComunes.value.forEach(nc => {
            if (nc.Regiones && Array.isArray(nc.Regiones)) {
                nc.Regiones.forEach(reg => {
                    lista.push({
                        ...reg,
                        NombreComunPadre: nc.NombreComun
                    });
                });
            }
        });
        return lista;
    });

    const regionesTaxon = computed(() => {
        console.log("Este es el valor de regiones: ", regiones.value);
        let lista = [];
        regiones.value.forEach(nc => {
            if(nc.Regiones && Array.isArray(nc.Regiones)){
                nc.regiones.forEach(reg => {
                    lista.push({
                        ...reg,
                        Region: nc.Region
                    })
                });
            }
        })
    });

    const caractTree = computed(() => {

        const caracteristicasOrdenadas = [...(caracteristicas.value || [])]
            .sort((a, b) => {

                const nombreA = a.Caracteristica || '';
                const nombreB = b.Caracteristica || '';

                return nombreA.localeCompare(
                    nombreB,
                    'es',
                    {
                        sensitivity: 'base'
                    }
                );
            });

        return caracteristicasOrdenadas.map(caract => {

            const regionesOrdenadas = [...(caract.Regiones || [])]
                .filter(region => region)
                .sort((a, b) => {

                    const partesA = (a.Region || '').split('/');
                    const partesB = (b.Region || '').split('/');

                    const niveles = Math.max(
                        partesA.length,
                        partesB.length
                    );

                    for (let i = 0; i < niveles; i++) {

                        const nivelA = partesA[i] || '';
                        const nivelB = partesB[i] || '';

                        const resultado = nivelA.localeCompare(
                            nivelB,
                            'es',
                            {
                                sensitivity: 'base'
                            }
                        );

                        if (resultado !== 0) {
                            return resultado;
                        }
                    }

                    return 0;
                });

            return {
                treeKey: `C-${caract.IdCatNombre}`,
                id: caract.IdCatNombre,
                tipo: 'caracteristica',
                label: caract.Caracteristica,
                biblio: caract.BiblioCaract?.url || '',
                observaciones: caract.Observaciones || '',

                children: regionesOrdenadas.map(region => ({
                    treeKey: `C-${caract.IdCatNombre}-R-${region.IdRegion}`,
                    id: region.IdRegion,
                    tipo: 'region',
                    label: region.Region,
                    biblio: region.Biblio?.url || '',
                    observaciones: region.Observaciones || '',
                    tipDistribucion: region.TipDistribucion?.id ?? null
                }))
            };
        });
    });

    const desmarcar = () => {
        if (arbolRegVal.value) {
            arbolRegVal.value.desmarcarTodos();
        }
    }

    //Definicion de la funcion 
    const marcarTodos = (treeRef, listaNodos, campoKey, marcar) => {

        // Obtener la instancia del árbol de Vue 
        const tree = treeRef?.value ?? treeRef;

        if (!tree || typeof tree.setCheckedKeys !== 'function') { 
            console.warn("El árbol no está disponible o no es una instancia de el-tree"); 
            
            return; 
        }

        const ids = [];

        const recorrer = (nodos) => {

            if (!Array.isArray(nodos)) return;

            nodos.forEach(nodo => {

                // Obtener la llave indicada al llamar la función 
                if (nodo[campoKey] !== undefined && nodo[campoKey] !== null) { 
                    ids.push(nodo[campoKey]); 
                } 
                
                // Recorrer los hijos 
                if (Array.isArray(nodo.Regiones)) { 
                    recorrer(nodo.Regiones); 
                }
            });
        };

        // Aquí inicia el recorrido del árbol
        if(marcar){
            recorrer(listaNodos);
        }

        // Marca todos los nodos encontrados
        treeRef.setCheckedKeys(ids, false);
    };

    const transferirSelccionados = async(arbolOrig, arrayOrig, arrayDest,
                                         campoId, campoTipo = null) => {

        // Aceptar tanto un ref de Vue como la instancia directa
        const tree = arbolOrig?.value ?? arbolOrig;

        if (!tree || typeof tree.getCheckedNodes !== 'function') {
            console.warn("El árbol no está disponible o no es una instancia de el-tree", arbolOrig);
            return;
        }

        // Recuperar los nodos marcados
        const nodos = tree.getCheckedNodes();

        console.log("Nodos marcados:", nodos);

        let seleccionados;
        let idsEliminar;
        let params;

        // Filtrar únicamente los nodos de región
        // Identificar los nodos que se van a transferir
        switch(campoTipo){
            case 'Region':
                seleccionados = nodos.filter(nodo =>{
                    return nodo[campoId] != null;
                });

                params = { idVal: idNombreVal.value,
                           idSin: idNombreSin.value,
                           seleccion: seleccionados
                 }

                console.log("Estos son los parametros a pasar: ", params);
                await axios.put('/traspaso-Regiones', params);

                idsEliminar = new Set(
                    seleccionados.map(nodo => nodo[campoId])
                );
            break;
            case 'Caract':
                seleccionados = nodos.filter(nodo =>
                    nodo[campoId] != null &&
                    nodo.tipo === 'caracteristica'
                );

                params = { idVal: idNombreVal.value,
                           idSin: idNombreSin.value,
                           seleccion: seleccionados
                 }

                console.log("Estos son los parametros a pasar: ", params);
                await axios.put('/traspaso-Caracteristicas', params);

                idsEliminar = new Set(
                    seleccionados.map(nodo => nodo[campoId])
                );
                break;
            case 'NomCom':
                seleccionados = nodos.filter(nodo =>{
                    return nodo[campoId] != null;
                });

                //----------------------- 
                params = { idVal: idNombreVal.value,
                           idSin: idNombreSin.value,
                           seleccion: seleccionados
                };
                
                console.log("Estos son los parametros a pasar: ", params);
                await axios.put('/traspaso-NombresComunes', params);

                idsEliminar = new Set(
                    seleccionados.map(nodo => nodo[campoId])
                );
                break;
        }

        if (!seleccionados.length) {
            console.warn("No hay valores seleccionadas");
            return;
        }

        // Eliminar los nodos del origen        
        // eliminarRecursivo(arrayOrig, idsEliminar, campoId);
        switch(campoTipo){
            case 'Region':
                eliminarRecursivo(arrayOrig, idsEliminar, campoId);
            break;
            case 'Caract':
                eliminarRecursivo(caracteristicas.value, idsEliminar, 'IdCatNombre');
                break;
            case 'NomCom':
                eliminarRecursivo(arrayOrig, idsEliminar, campoId);
            break;
        }

        // Agregar los nodos al destino, evitando duplicados
        seleccionados.forEach(nodo => {
            console.log("Este es el valor de nodo existente: ",  nodo[campoId]);
            const existe = arrayDest.some(
                elemento => elemento[campoId] === nodo[campoId]
            );

            if (!existe) {
                arrayDest.push(nodo);
            }
        });

        await nextTick();

        // Limpiar las selecciones del árbol de origen
        tree.setCheckedKeys([]);
    };

    const eliminarRecursivo = (nodos, idsEliminar, campoId) => {
        for (let i = nodos.length - 1; i >= 0; i--) {
            const nodo = nodos[i];

            if (idsEliminar.has(nodo[campoId])) {
                nodos.splice(i, 1);
                continue;
            }

            if (Array.isArray(nodo.Regiones)) {
                eliminarRecursivo(nodo.Regiones, idsEliminar, campoId);
            }

            if (Array.isArray(nodo.children)) {
                eliminarRecursivo(nodo.children, idsEliminar, campoId);
            }
        }
    };

</script>

<style scoped>
    .box-card {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        height: 791px;
    }

    .header {
        background-color: #d9e1eb;
        padding: 15px;
        border-bottom: 1px solid #e0e0e0;
        height: auto !important;
        min-height: auto !important;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 8px;
        color: white;
    }

    .headerInterno {
        background-color: #e5e7eb;
        padding: 15px;
        border-bottom: 1px solid #e0e0e0;
        height: auto !important;
        min-height: auto !important;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 8px;
        color: white;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .header-button {
        margin-left: 15px; /* Espacio entre título y botón */
        flex-shrink: 0; /* Evita que el botón se reduzca */
    }

    .titulo {
        font-size: 1.25rem;
        font-weight: bold;
        color: #333;
        margin: 0;
        text-align: center;
    }

    .titulo-interno {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
        margin: 0;
        text-align: center;
    }

    /* Estilos para el árbol */
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

    .tree-node-label {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .custom-element-tree .el-tree-node:has(.nodo-region) > .el-tree-node__content .el-checkbox {
        display: none;
    }


    /* ----------------------------------------------------------------------------------------------*/

    .demo-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .splitter-full {
        width: 100%;
        height: 100%;
    }

    .contenido-deshabilitado {
        pointer-events: none;
        opacity: 0.5;
        user-select: none;
    }

</style>