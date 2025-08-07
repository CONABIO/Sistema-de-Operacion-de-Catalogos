<template>
    <div>
        <el-card class="box-card">
            <div class="common-layout">
                <el-container style="min-height: 300px;">
                    <el-header style="background: #f5f5f5; padding: 10px;">
                        <el-row :gutter="10" align="middle">
                            <h2 class="titulo">Relaciones taxonómicas</h2>
                        </el-row>
                    </el-header>
                    <el-main style="padding: 20px; background: #fff;">
                        <el-row>
                            <el-col :xs="24" :sm="24" :md="12" :lg="8" style="padding: 12px;">
                                <span>
                                    Relaciones nomenclaturales
                                </span>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <el-cascader
                                        :options="tiposRel"
                                        :props="cascaderProps"
                                        clearable
                                        filterable
                                        v-model="tipRel"
                                        placeholder="Seleccione"
                                        @change="cargaRelaciones"
                                        style="flex-grow: 1; font-size: 14px;">
                                        <template #default="{ data }">
                                            <span style="display: inline-flex; align-items: center;">
                                                <span
                                                    v-if="typeof data.icono === 'string' && 
                                                            data.icono.trim().startsWith('<svg')"
                                                        v-html="data.icono"
                                                        style="width: 16px; height: 16px; 
                                                        display: inline-block; margin-right: 6px;" />

                                                <img
                                                    v-else-if="typeof data.icono === 'string'"
                                                                :src="data.icono" alt=""
                                                    style="width: 16px; height: 16px; margin-right: 6px;" />

                                                <span>{{ data.label }}</span>
                                            </span>
                                        </template>
                                    </el-cascader>
                                    <el-tooltip
                                        class="item"
                                        effect="dark"
                                        content="Catálogo de Relaciones taxonómicas"
                                        placement="bottom">
                                        <el-button
                                            @click="catalogoRelTax()"
                                            type="primary"
                                            circle
                                            color="#2420b4">
                                            <el-icon>
                                                <Rompecabezas />
                                            </el-icon>
                                        </el-button>
                                    </el-tooltip>
                                </div>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-card style="width: 100%;">
                                <div style="max-height: 300px; overflow-y: auto;">
                                    <el-table :data="relDetalle"
                                              :bordder="true"
                                              style="width: 100%;">
                                        <el-table-column type="expand">
                                            <template #default ="props">
                                                <div m="4">
                                                    <p m="t-0 b-2">Fecha Captura: {{ props.row.fechaCaptura }}</p>
                                                    <p m="t-0 b-2">Fecha Modificacion: {{ props.row.fechaModificacion }}</p>
                                                    <h3 style="font-weight: bold; margin: 16px 0; color: #450A03;">
                                                        Bibliografía
                                                    </h3>
                                                    <el-table :data="props.row.Bibliografia" :border="true">
                                                        <el-table-column type="expand">
                                                            <template #default ="prop">
                                                                <p>Fecha de alta: {{ prop.row.FechaCaptura }}</p>
                                                                <p>Fecha de modificación {{ prop.row.FechaModificacion }}</p>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column label = "Autor" prop = "Autor" />
                                                        <el-table-column label = "Año" prop = "Anio" />
                                                        <el-table-column label = "Catalogo" prop = "Catalogo" />
                                                        <el-table-column label = "Cita Completa" prop = "CitaCompleta" />
                                                    </el-table>
                                                </div>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="Tipo Relación" prop="iconoTipRel" width="80">
                                            <template #default="{ row }">
                                                <el-tooltip v-if="row.desIconTip" :content="row.desIconTip" placement="top">
                                                    <span v-html="row.iconoTipRel"></span>
                                                </el-tooltip>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="Nivel Taxonomico" prop="iconoCat" width="120">
                                            <template #default="{ row }">
                                                <el-tooltip v-if="row.desIconCat" :content="row.desIconCat" placement="top">
                                                    <img :src="row.iconoCat"
                                                        alt="icono"
                                                        style="width: 24px; height: 24px;" />
                                                </el-tooltip>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="Taxon" prop="label"/>
                                    </el-table>
                                </div>
                            </el-card>
                        </el-row>
                        <br />
                        <el-row>
                            <el-card style="width: 100%;">
                                <el-container>
                                    <el-header>
                                        <FiltroGrupo></FiltroGrupo>
                                    </el-header>
                                    <el-main>
                                        <div style="flex-grow: 1;">
                                            <el-container style="height: 100%; border: 1px solid red;">
                                                <el-aside width="450px" style="background-color: rgb(238, 241, 246); height: 100%; overflow: auto;">
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
                                                </el-aside>
                                                <el-container style="height: 500px; border: 1px;">
                                                    <el-header style="text-align: left; font-size: 12px; height: 70px;">
                                                    <h3>Observaciones</h3>
                                                    </el-header>
                                                    <br/>
                                                    <br/>
                                                    <el-main width="500px" style="height: 500px;">
                                                        <el-textarea>
                                                            algo va a ir aqui 
                                                        </el-textarea>
                                                    </el-main>                  
                                                </el-container>
                                            </el-container>
                                        </div>
                                    </el-main>
                                </el-container>
                            </el-card>
                        </el-row>
                    </el-main>
                    <el-footer style="background: #f0f0f0; text-align: center;">
                        Footer
                    </el-footer>
                </el-container>
            </div>
        </el-card>
        <DialogForm v-model="dialogFormVisibleCat" :botCerrar="true" :pressEsc="true">
            <FiltroGrupos 
                :gruposTax="gruposTax" 
                :catalogPadre="catalogos"
                :gruposPadre="grupos"
                :idsGruposPadre="idsGrupos"
                @cerrar="cerrarDialog" 
                @enviagrupos="recibirGrupos" 
            />
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
    import FiltroGrupo from '@/Components/Biotica/FiltroGrupoTax.vue';

    // Datos reactivos
    const tiposRel = ref([]);
    const tipRel = ref("");
    const dialogFormVisibleCat = ref(false);
    const categ = ref(null);
    const catego = ref('');
    const catalogos = ref('');
    const grupos = ref('');
    const idsGrupos = ref('');
    const tipRelSelec = ref('');
    const gruposTax = ref([]);
    const relDetalle = ref([]);

    // Datos de ejemplo para el transfer
    const leftValue = ref([]);
    const selectedValues = ref([]);
    const transferData = [
        {
            key: 1,
            label: 'Usuario',
            icon: User,
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

    // Función para abrir diálogo
    const openDialog = async (nodo) => {
        console.log(nodo.label);    
        alert("Estoy mandando " + nodo.label);
    };

    // Props del componente
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

    const cascaderProps = {
        label: 'label',
        value: 'value',
        children: 'children'
    };

    // Función para cargar grupos iniciales
    const cargaGrupos = () => {
        catalogos.value = props.catalogPadre;
        grupos.value = props.gruposPadre;
        idsGrupos.value = props.idsGruposPadre;
    };

    // Función para recibir grupos del componente hijo
    const recibirGrupos = (payload) => {
        catalogos.value = payload.catalogos;
        grupos.value = payload.grupos;
        idsGrupos.value = payload.idsGrupos;
    };

    // Función para cerrar diálogo
    const cerrarDialog = (valor) => {
        dialogFormVisibleCat.value = valor;
    };

    // Función para cargar relaciones taxonómicas
    const cargaRelaciones = async(value) => {
        console.log("Esta es la realcion selccionada: ", value);
        let idsNombreSin = 0;
        let idsNombreVal = 0;
        let params = {};
        let listAct = {};


        const etiqueta = tiposRel.value.find(tipoRel => tipoRel.value === value[value.length - 1]);

        if(etiqueta != undefined)
        {
            tipRelSelec.value = etiqueta.value 
        }

        for (const child of tiposRel.value) {
            if (child.children && child.children.length > 0) {
                const found = await updateChildNode(child.children, value[value.length - 1]);
            } 
        }

        if(props.taxonAct.estatus === 'Correcto' || props.taxonAct.estatus === 'Válido')
        {
            listAct= props.taxonAct.completo.nombre_rel;
            console.log("Esta es la lista actual: ", listAct);
            const relaciones = Array.isArray(props.taxonAct.completo.nombre_rel) 
                ? props.taxonAct.completo.nombre_rel 
                : [props.taxonAct.completo.nombre_rel];

            // Filtrar y mapear
            if(tipRelSelec.value === 0)
            {
                idsNombreSin = relaciones.map(r => ({idNom: r.IdNombreRel,
                                                     obser: r.Observaciones,
                                                     idTipRel: r.IdTipoRelacion,
                                                     fechaCap: r.FechaCaptura,
                                                     fechaMod: r.FechaModificacion
                }));
            }
            else{
                idsNombreSin = relaciones
                    .filter(r => r.IdTipoRelacion === tipRelSelec.value)
                    .map(r => ({idNom: r.IdNombreRel,
                                obser: r.Observaciones,
                                idTipRel: r.IdTipoRelacion,
                                fechaCap: r.FechaCaptura,
                                fechaMod: r.FechaModificacion
                    }));
            }
     }
        else
        {
            listAct= props.taxonAct.completo.nombre_rel_val;
         
            const relaciones = Array.isArray(props.taxonAct.completo.nombre_rel_val) 
                ? props.taxonAct.completo.nombre_rel_val 
                : [props.taxonAct.completo.nombre_rel_val];

            if(tipRelSelec.value === 0)
            {
                idsNombreVal = relaciones.map(r => ({idNom: r.IdNombre,
                                                     obser: r.Observaciones,
                                                     idTipRel: r.IdTipoRelacion,
                                                     fechaCap: r.FechaCaptura,
                                                     fechaMod: r.FechaModificacion
                }));
            }
            else{
                idsNombreVal = relaciones 
                    .filter(r => r.IdTipoRelacion === tipRelSelec.value)
                    .map(r => ({idNom: r.IdNombre,
                                obser: r.Observaciones,
                                idTipRel: r.IdTipoRelacion,
                                fechaCap: r.FechaCaptura,
                                fechaMod: r.FechaModificacion
                    }));
            }
        }

        if(idsNombreSin.length > 0)
        {
            params = {ids: idsNombreSin,
                      idAct: props.taxonAct.id 
            };
        }
        else
        {
            params = {ids: idsNombreVal,
                      idAct: props.taxonAct.id
            };
        }

        const response = await axios.get('/cargar-relaciones', { params });
        
         if (response.status === 200) {
            console.log("esta es la respuesta del servidor: ", response);
            console.log("Esta es la lista de relaciones actual: ", listAct);

            relDetalle.value = response.data;
            /*const resultado = listAct.map(rel => {
                const resp = response.data.find(r => r.id === rel.IdNombreRel);
                return {
                    resp,
                    FechaCaptura: rel.FechaCaptura,
                    FechaModificacion: rel.FechaModificacion
                }
            })*/
            //console.log("Esto es completo: ", resultado);
        }
        else {
            console.log("Se presentó un error en la recuperación de los datos");
        }
    };

    // Función recursiva para buscar nodos hijos
    const updateChildNode = async (children, res) => {
        for (const child of children) {
            if (child.value === res) {
                tipRelSelec.value = child.value;
                return true; 
            }
            
            if (child.children && child.children.length > 0) {
                const found = await updateChildNode(child.children, res);
                if (found) return true;
            }
        }
        return false;
    };

    // Inicialización de datos
    onMounted( async () => {
        const response = await axios.get('/cargar-tipoRel');
        
        if (response.status === 200) {            
            tiposRel.value = response.data;
            tiposRel.value.unshift({
                label: "Todos",
                value: 0
            });
        }
       await cargaGrupos();
        gruposTax.value = props.gruposTax;
    });

    // Watcher para cambios en los props
    watchEffect(() => {
        if (props.gruposPadre) {
            cargaGrupos();
        }
    });

</script>

<style>
    .titulo {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    .table-responsive {
        overflow-x: auto;
    }
</style>