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
                <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                    {{ props.taxonAct.label }}
                </span>
            </el-row>
            <el-row>
              <el-col :xs="24" :sm="24" :md="12" :lg="8" style="padding: 12px;">
                <span>Relaciones nomenclaturales</span>
                <div style="display: flex; align-items: center; gap: 8px;">
                  <el-cascader 
                    :options="tiposRel" 
                    :props="cascaderProps" 
                    clearable 
                    filterable 
                    v-model="tipRel" 
                    placeholder="Seleccione" 
                    @change="cargaRelaciones"
                    style="flex-grow: 1; font-size: 14px;"
                  >
                    <template #default="{ data }">
                      <span style="display: inline-flex; align-items: center;">
                        <span v-if="typeof data.icono === 'string' && data.icono.trim().startsWith('<svg')" v-html="data.icono" style="width: 16px; height: 16px; display: inline-block; margin-right: 6px;" />
                        <img v-else-if="typeof data.icono === 'string'" :src="data.icono" alt="" style="width: 16px; height: 16px; margin-right: 6px;" />
                        <span>{{ data.label }}</span>
                      </span>
                    </template>
                  </el-cascader>
                  <el-tooltip class="item" effect="dark" content="Catálogo de Relaciones taxonómicas" placement="bottom">
                    <el-button @click="catalogoRelTax()" type="primary" circle color="#2420b4">
                      <el-icon><Rompecabezas /></el-icon>
                    </el-button>
                  </el-tooltip>
                </div>
              </el-col>
            </el-row>
            
            <el-row>
              <el-card class="main-content-card">
                <div>
                    <el-input
                        v-model="textarea"
                        style="width: 100%"
                        :rows="2"
                        type="textarea"
                        placeholder="Observaciones"
                    />
                </div>
                <br/>
                <div class="dual-panel-container">
                  <!-- Panel del Árbol -->
                  <el-card class="tree-panel">
                    <el-container style="display: flex; flex-direction: column; height: 100%;">
                      <el-header style="resize: vertical; overflow: auto; min-height: 80px; max-height: 400px; height: auto;">
                        <el-row :gutter="16" style="display: flex; flex-wrap: wrap;">
                          <el-col :xs="24" :sm="12" :md="7" style="display: flex; flex-direction: column;">
                            <span>Ir a:</span>
                            <el-input clearable placeholder="" v-model="filterText" @change="filterNode" style="height: 28px;" size="small" />
                          </el-col>
                          <el-col :xs="24" :sm="12" :md="5" style="display: flex; flex-direction: column;">
                            <span class="block">Nivel taxonómico</span>
                            <el-cascader 
                              :options="categoriasTax" 
                              clearable 
                              filterable 
                              v-model="categ" 
                              placeholder="Nivel taxonómico" 
                              @change="handleChange"
                            >
                              <template #default="{ data }">
                                <span style="display: inline-flex; align-items: center;">
                                  <img :src="data.RutaIcono" alt="" style="width: 16px; height: 16px; margin-right: 6px;" />
                                  <span>{{ data.label }}</span>
                                </span>
                              </template>
                            </el-cascader>
                          </el-col>
                          <el-col :span="12">
                            <el-row :gutter="10" style="display: flex; align-items: flex-start;">
                              <el-col :span="11">
                                <div style="display: flex; flex-direction: column;">
                                  <span class="demo-input-label" style="margin-bottom: 4px;">Catálogo(s)</span>
                                  <el-input type="textarea" :rows="2" placeholder="Catálogos" v-model="catalogos" :disabled="true" />
                                </div>
                              </el-col>
                              <el-col :span="11">
                                <div style="display: flex; flex-direction: column;">
                                  <span class="demo-input-label" style="margin-bottom: 4px;">Grupo SCAT</span>
                                  <el-input type="textarea" :rows="2" placeholder="Grupo SCAT" v-model="grupos" :disabled="true" />
                                </div>
                              </el-col>
                              <el-col :span="2" style="display: flex; align-items: flex-start;">
                                <el-tooltip effect="dark" content="Selección Catálogo de Grupos taxonómicos" placement="bottom">
                                  <el-button @click="filtro_Catalogos()" type="primary" circle style="margin-top: 4px;">
                                    <el-icon><filtroGrupos /></el-icon>
                                  </el-button>
                                </el-tooltip>
                              </el-col>
                            </el-row>
                          </el-col>
                        </el-row>
                      </el-header>
                      
                      <el-main style="flex: 1; overflow: auto;">
                        <div style="flex-grow: 1;">
                          <el-container style="height: 650px;">
                            <el-aside width="600px" style="height: 470px; display: flex; flex-direction: column;">
                              <div style="flex: 1; overflow: auto;">
                                <el-scrollbar height="100%">
                                  <el-tree 
                                    class="filter-tree" 
                                    :data="data" 
                                    node-key="id" 
                                    @node-click="expande" 
                                    :expand-on-click-node="true" 
                                    :filter-node-method="filterNode" 
                                    :draggable="false" 
                                    empty-text='' 
                                    ref="tree" 
                                    :highlight-current="true" 
                                    :current-node-key="selectedNodeKey" 
                                    :props="defaultProps" 
                                    @node-contextmenu="handleNodeRightClick"
                                  >
                                    <template #default="{ node }">
                                      <div class="tree-node-wrapper">
                                        <Logo class="tree-node-logo" :rutaCategoria="node.data.completo.categoria.RutaIcono" />
                                        <span class="tree-node-label" :class="{ 'highlight-node': node.data.customClass === 'highlight-node' }">
                                          {{ node.label }}
                                        </span>
                                      </div>
                                    </template>
                                  </el-tree>
                                </el-scrollbar>
                              </div>
                              
                              <br/>
                              
                              <div v-if="totalItems > 0" style="padding: 10px; border-top: 1px solid #ccc; display: flex; align-items: center; justify-content: space-between;">
                                <el-pagination 
                                  :current-page="currentPage" 
                                  :page-size="itemsPerPage" 
                                  :total="totalItems" 
                                  @current-change="handlePageChange" 
                                  layout="prev, pager, next, total" 
                                  background
                                />
                                <span v-show="numHijos > 0" style="margin-left: auto;">
                                  Num. Hijos: {{ numHijos }}
                                </span>
                              </div>
                            </el-aside>
                          </el-container>
                        </div>
                      </el-main>
                    </el-container>
                  </el-card>
                  <el-card class="table-panel1" style="display: flex; flex-direction: column;
                                  height: 100%">
                    <el-tooltip
                        class="box-item"
                        effect="dark"
                        content="Relaciona taxón"
                        placement="right"
                    >
                        <el-button  @click = "traspasaDatos"
                                        circle
                                        type="primary"
                                        :disabled = "habTraspaso">
                            <el-icon>
                                <traspasoInfo />
                            </el-icon>
                        </el-button>
                    </el-tooltip>
                    <br/>
                    <br/>
                    <el-button  @click = "traspasaDatos"
                                    circle
                                    type="primary">
                        <el-icon>
                            <regresoInfo />
                        </el-icon>
                    </el-button>
                    <br/>
                    <br/>
                    <el-button  @click = "traspasaDatos"
                                    circle
                                    type="primary">
                        <el-icon>
                            <reemplazo />
                        </el-icon>
                    </el-button>
                    <br/>
                  </el-card>
                  <!-- Panel de la Tabla -->
                  <el-card class="table-panel" style="display: flex; flex-direction: column;
                                  height: 100%">
                    <div style="flex-shrink: 0;">
                        <el-input
                            v-model="observ"
                            style="width: 100%"
                            :rows="2"
                            type="textarea"
                            placeholder="Observaciones"
                        />
                    </div>
                    <br/>
                    <div ref="scrollContainer"
                            style="flex: 1; overflow-y: auto; border: 1px solid #dcdfe6; 
                                    border-radius: 4px; margin-top: 10px;">
                      <TablaFiltrable 
                        class="flex-grow" 
                        :container-class="'main-section'" 
                        :columnas="columnasDefinidas" 
                        v-model:datos="tablaNomenclatura" 
                        v-model:total-items="totalRegNom" 
                        :opciones-filtro="opcionesFiltroNomenclatura"
                        @row-click="filaSeleccion">

                        <template #expand-column>
                            <el-table-column type="expand">
                                <template #default="{ row }">
                                    <div class="expand-content-detail">
                                        <p><strong>Fecha de alta:</strong>{{ row.FechaCaptura }}</p>
                                        <p><strong>Fecha de modificación:</strong>{{ row.FechaModificacion }}</p>
                                    </div>
                                </template>
                            </el-table-column>
                        </template>
                     </TablaFiltrable>
                    </div>
                  </el-card>
                </div>
              </el-card>
            </el-row>
          </el-main>
        </el-container>
      </div>
    </el-card>

    <DialogForm v-model="dialogFormVisibleCat" :botCerrar="false" :pressEsc="false" :width="'35%'">
      <FiltroGrupos :grupos="gruposTax" @cerrar="cerrarDialog" @regresaGrupos="recibeGrupos" />
    </DialogForm>

    <Teleport to="body">
            <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
                @close="cerrarNotificacion" />
    </Teleport>
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
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrableImg.vue";
    import filtroGrupos from '@/Components/Biotica/Icons/Conectado.vue';
    import { ElLoading } from 'element-plus';
    import usePermisos from '@/composables/usePermisos';
    import { usePage } from '@inertiajs/vue3';
    import traspasoInfo from '@/Components/Biotica/Icons/TraspasoInfo.vue';
    import regresoInfo from '@/Components/Biotica/Icons/RegresoInfo.vue';
    import reemplazo from '@/Components/Biotica/Icons/Reemplazar.vue';
    import NotificacionExitoErrorModal from"@/Components/Biotica/NotificacionExitoErrorModal.vue";
    import axios from 'axios';

    const { permisos } = usePermisos();

    const page = usePage();
    const authUser = page.props.auth.user || [];

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
    const totalItems = ref(0);

    const habTraspaso = ref(true);
    const notificacionVisible = ref(false);
    const notificacionTitulo = ref("");
    const notificacionMensaje = ref("");
    const notificacionTipo = ref("info");
    const notificacionDuracion = ref(5000);
    const observ = ref("");

    const filterText = ref('');
    const mostrar = ref(false);
    const data = ref([]);
    const paginas = ref('');
    const currentPage = ref(1);
    const itemsPerPage = ref(150);
    const taxonAct = ref([]);
    const taxonActRel = ref([]);
    const tree = ref(null);
    const selectedNodeKey = ref(null);
    const numHijos = ref(0);
    const totalReg = ref(0);

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

    const scrollContainer = ref(null);
    const tablaNomenclatura = ref([]);

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

    const filaSeleccion = async(row) =>{
      console.log("Esta es la fila seleccionada desde el padre: ", row);
      observ.value = row.Observaciones;
    }

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

    const filtro_Catalogos = () => {
        //Funcion para abrir el modal que muestra los catalogos taxonómicos
        dialogFormVisibleCat.value = true;
    };

    // Función para cargar grupos iniciales
    const cargaGrupos = () => {
        catalogos.value = props.catalogPadre;
        grupos.value = props.gruposPadre;
        idsGrupos.value = props.idsGruposPadre;
    };

    //Funcion que se ejecuta para la expancion de un nodo
    const expande = async (draggingNode, nodeData, nodeComponent) => {

        mostrar.value = true;
        taxonActRel.value = draggingNode;
        
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
        
        numHijos.value = draggingNode.children.length;
        selectedNodeKey.value = draggingNode.id;

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
    //Funcion para validae la visbilidad de los objetos 
    const hasPermisos = (etiqueta, modulo) => {

        const permiso = permisos.find(item => item.NombreModulo === etiqueta);

        return permiso[modulo];
    };

    const handlePageChange = (page) => {
        console.log("Este es el valor de page:", page);
        currentPage.value = page;
        fetchFilteredData();
    };

    const fetchFilteredData = async () => {
        console.log("estoy en esta funcion");
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
        let idsNombreSin = 0;
        let idsNombreVal = 0;
        let params = {};
        let listAct = {};
        
        if(value != undefined)
        {
           const etiqueta = await buscaTipoRelacion (tiposRel.value, value[value.length - 1]);

            if(etiqueta != undefined)
            {
              tipRelSelec.value = etiqueta.value;
            }

            const params= {
                  taxAct: props.taxonAct.id
              };                  

        const loading = ElLoading.service({
            lock: true,
            text: "Loading",
            spinner: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="none" d="M0 0h200v200H0z"></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M70 95.5V112m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5L92 57.3M33.6 91 48 82.7m0-25.5L33.6 49m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;-120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M130 155.5V172m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5-14.3 8.3M93.6 151l14.3-8.3m0-25.4L93.6 109m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>`,
            backgroud: 'rgba(255,255,255,0.85)',
        });

          const response = await axios.get('/carga-RelacionesTax', { params });

            if(tipRelSelec.value > 0)
            {
                for (const child of tiposRel.value) {
                    if (child.children && child.children.length > 0) {
                        const found = await updateChildNode(child.children, value[value.length - 1]);
                    } 
                }
                
                let filtrados = response.data.filter(item => 
                                item.TipoRelacion?.idTipoRel === tipRelSelec.value)

                tablaNomenclatura.value = filtrados ?? [];

                totalRegNom.value = filtrados.length;
                habTraspaso.value = false;
            }
            else{
                tablaNomenclatura.value = response.data;
                totalRegNom.value = response.data.length;
                habTraspaso.value = false;
            }
            loading.close();
        }
        else {
            tablaNomenclatura.value = [];
            totalRegNom.value = 0;
        }
    };

    const buscaTipoRelacion = async(tiposRelacion, valor) => {
      for(const nodo of tiposRelacion){

        if(nodo.value === valor){
          return nodo;
        }
        //console.log("Nodo revisado:", nodo);
        if(nodo?.children && nodo?.children.length > 0){
          const encontrado = await buscaTipoRelacion(nodo.children, valor);
          if(encontrado){
            return encontrado;
          }
        }
      }
       return null;
    }

    const traspasaDatos = async() => {
        
        let sinonimos = false;
        let basonimos = false;
        let equivalencia = false;

        if(taxonActRel.value.length === 0)
        {
            console.log("No a seleccionado ningun taxon");
             mostrarNotificacion(
                "Alerta",
                "Se debe selccionar al menos un taxón a relacionar",
                "error",
                7000
            );
        }   

        switch (tipRelSelec.value){
            case 1:
                sinonimos = validacionSinonimos();

                if(sinonimos){
                    altaRelacion();
                }
                break;
            case 2:
                sinonimos = validacionSinonimos();
                if (sinonimos){
                  basonimos = validacionBasonimos();
                  if(basonimos){
                    altaRelacion();
                  }
                }
              break;
            case 3:
                equivalencia = validacionEquivalencia();
                if(equivalencia)
                {
                  altaRelacion();
                }
              break;
            case 7:
                huesped = validacionHuesped();
                if(huesped)
                {
                  altaRelacion();
                }
              break;
        }
    } 
    
    const validacionSinonimos = async () => {
        
      if(tipRelSelec.value === 2)
      {
        let filtraVal = props.taxonAct.relaciones.filter(item => 
                                item.TipoRelacion?.idTipoRel === 1);

        let filtraRel = taxonActRel.value.relaciones.filter(item => 
                                item.TipoRelacion?.idTipoRel === 1);
      }else{
        let filtraVal = props.taxonAct.relaciones.filter(item => 
                                  item.TipoRelacion?.idTipoRel === tipRelSelec.value);

        let filtraRel = taxonActRel.value.relaciones.filter(item => 
                                  item.TipoRelacion?.idTipoRel === tipRelSelec.value);
      }
      const contValidoAct = filtraVal.some(rel => rel.Nombrecompleto?.estatus === "Válido" || 
                                                  rel.Nombrecompleto?.estatus === "Correcto");

      const conValidoRel = filtraRel.some(rel => rel.Nombrecompleto?.estatus === "Válido" || 
                                                 rel.Nombrecompleto?.estatus === "Correcto");                                                      

        //Se valida que el taxon no sea del mismo estatus 
        if(taxonActRel.value.estatus === props.taxonAct.estatus)
        {
            mostrarNotificacion(
                "Alerta",
                "El taxón actual y el taxon a relacionar o pueden tener el mismo estatus",
                "error",
                7000
            ); 
            return false;//Se valida si el taxon a relacionar no cuente con un valido relacionado si el taxon a relacionar es válido
        }else if(contValidoAct || conValidoRel){
            mostrarNotificacion(
                "Alerta",
                "El taxón sinonimo seleccionado ya cuenta con un valido relacionado ",
                "error",
                7000
            );
            return false;//Se valida que el taxon a relacionar no tenga validos asociados
        }else if(props.taxonAct.estatus === "ND"){
            mostrarNotificacion(
                "Alerta",
                "El taxón actual tiene estatus ND por lo cual no puede tener relaciones de sinonimia",
                "error",
                7000
            );
            return false;//Se valida que el nivel taxonomico de los taxones a relacionar no se superior a familia 
        }else if(props.taxonAct.completo.categoria.IdNivel1 < 5 || taxonActRel.value.completo.categoria.IdNivel1 < 5){
            mostrarNotificacion(
                "Alerta",
                "No se puede tener relaciones de sinonimia en taxones de categoria superior a familia",
                "error",
                7000
            );
            return false;
        }

        return true;
    }

    const validacionBasonimos = async () => {

      let filtraVal = props.taxonAct.relaciones;

      let filtraRel = taxonActRel.value.relaciones;

      const contValidoAct = filtraVal.some(rel => rel.Nombrecompleto?.estatus === "Válido" || 
                                                  rel.Nombrecompleto?.estatus === "Correcto" ||
                                                  rel.TipoRelacion.idTipoRel === 2);

      const conValidoRel = filtraRel.some(rel => rel.Nombrecompleto?.estatus === "Válido" || 
                                                 rel.Nombrecompleto?.estatus === "Correcto" ||
                                                 rel.TipoRelacion.idTipoRel === 2);      

      if((props.taxonAct.estatus === 'Válido' || props.taxonAct.estatus === 'Correcto') && props.taxonAct.completo.categoria.IdNivel1 < 7){
         mostrarNotificacion(
                "Alerta",
                "El taxón actual es de categoria superior a especie por lo cual no se puede generar la relación de basonimia",
                "error",
                7000
            ); 
            return false;
      }

      if((taxonActRel.value.estatus === 'Válido' || taxonActRel.value.estatus === 'Correcto') && taxonActRel.value.completo.categoria.IdNivel1 < 7){
         mostrarNotificacion(
                "Alerta",
                "El taxón a relacionar es de categoria superior a especie por lo cual no se puede generar la relación de basonimia",
                "error",
                7000
            ); 
            return false;
      }

      if(contValidoAct || conValidoRel){
        mostrarNotificacion(
                "Alerta",
                "El taxón ya cuenta con una relación de basonimia o el taxon sinonimo ya cuenta con una relacion valida",
                "error",
                7000
            ); 
            return false;
      }

      return true;
    }

    const validacionEquivalencia = async () => {

      if(props.taxonAct.completo.SistClasCatDicc === taxonActRel.value.completo.SistClasCatDicc){
            mostrarNotificacion(
                "Alerta",
                "No se puede generar la relacion ya que el sistema de clasificación es el mismo en ambos taxones",
                "error",
                7000
            ); 
            return false;
      }

      if(props.taxonAct.completo.SistClasCatDicc === 'NA' || 
         props.taxonAct.completo.SistClasCatDicc === 'ND' || 
         taxonActRel.value.completo.SistClasCatDicc === 'NA' || 
         taxonActRel.value.completo.SistClasCatDicc === 'ND'){
          mostrarNotificacion(
                "Alerta",
                "No se puede generar la relación porque el sistema de clasificación de uno de los taxones es NA o ND",
                "error",
                7000
            ); 
            return false;
      }

      if(props.taxonAct.completo.categoria.NombreCategoriaTaxonomica === taxonActRel.value.completo.categoria.NombreCategoriaTaxonomica){
          mostrarNotificacion(
                "Alerta",
                "No se puede generar la relación porque la categoria taxonomica no es la misma en ambos taxones",
                "error",
                7000
            ); 
            return false;
      }

      if((props.taxonAct.completo.categoria.IdNivel1 < 7 && props.taxonAct.completo.categoria.idNivel3 === 0) ||
         (taxonActRel.value.completo.categoria.IdNivel1 < 7 && taxonActRel.value.completo.categoria.IdNivel3 === 0)){
          mostrarNotificacion(
                "Alerta",
                "No se puede generar la relación porque la debe ser género o superior",
                "error",
                7000
            ); 
            return false;
      }

      return true;
    } 

    const validacionHuesped = async () => {
      const gruposPara = ["ARACH", "COLEO", "DIPTE", "HYMEN", "INSEC", 
                          "NEMAT", "ACANT", "ANNEL", "CESTO", "CRUST", 
                          "MONOG", "PROT", "MYXOZ", "TREMA"];
      const gruposVert = ["ANFIB", "AVES", "MAMIF", "PECES", "REPTI"];   
      const estatusPermitidos = ["Válido", 'Aceptado'] 
      

      if(!estatusPermitidos.includes(props.taxonAct.estatus) || !estatusPermitidos.includes(taxonActRel.value.estatus)){
        mostrarNotificacion(
                "Alerta",
                "No se puede generar la relación ya que uno o ambos taxones tienen estatus diferente de válido/correcto",
                "error",
                7000
            ); 
            return false;
      }

      if(!(gruposPara.includes(props.taxonAct.completo.scat.grupo_scat.GrupoAbreviado) &&
          gruposVert.includes(taxonActRel.value.completo.scat.grupo_scat.GrupoAbreviado)) ||
          !(gruposVert.includes(props.taxonAct.completo.scat.grupo_scat.GrupoAbreviado) &&
           gruposPara.includes(taxonActRel.value.completo.scat.grupo_scat.GrupoAbreviado))){
            mostrarNotificacion(
                "Alerta",
                "El vertebrado o parásito que selecciono no pertenece aun grupo válido - Vertebrados válidos (ANFIB, AVES, MAMIF, PECES, REPTI), Parásitos válidos (ARACH, COLEO, DIPTE, HYMEN, INSEC, NEMAT, ACANT, ANNEL, CESTO, CRUST, MONOG, PROT, MYXOZ, TREMA)",
                "error",
                7000
            ); 
            return false;
           }
    }

    const altaRelacion = async() => {
        
        const params= {
                        taxonAct: props.taxonAct, 
                        taxonActRel: taxonActRel.value,
                        tipRelacion: tipRelSelec.value
                    };                  

        const loading = ElLoading.service({
            lock: true,
            text: "Loading",
            spinner: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="none" d="M0 0h200v200H0z"></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M70 95.5V112m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5L92 57.3M33.6 91 48 82.7m0-25.5L33.6 49m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;-120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M130 155.5V172m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5-14.3 8.3M93.6 151l14.3-8.3m0-25.4L93.6 109m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>`,
            backgroud: 'rgba(255,255,255,0.85)',
        });
        try{
          const response = await axios.post('/alta-RelacionesTax', { params });

           mostrarNotificacion(
                "Alerta",
                "La relación se genero correctamente",
                "info",
                7000
            );

          tablaNomenclatura.value = response.data;
          loading.close();
        }catch(error){
          if (error.response && error.response.status === 422) {
              // Aquí están los errores de validación
              console.log("Este es el error completo: ", error.response.data.message);
              
              mostrarNotificacion(
                "Alerta",
                error.response.data.message,
                "info",
                7000
            );
            
          } else {
              // Otros errores inesperados
              console.error("Error inesperado:", error);
              ElMessage.error("Ocurrió un error en el servidor");
          }

          loading.close();
        }      
    }

    const mostrarNotificacion = (
        titulo,
        mensaje,
        tipo = "warning",
        duracion = 5000,
        dangerouslyUseHTML = false
    ) => {
        notificacionTitulo.value = titulo;
        notificacionMensaje.value = mensaje;
        notificacionTipo.value = tipo;
        notificacionDuracion.value = duracion;
        notificacionVisible.value = true;
    };

    const cerrarNotificacion = () => {
        notificacionVisible.value = false;
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
        taxonAct.value = props.taxonAct;
    });

    // Watcher para cambios en los props
    watchEffect(() => {
        if (props.gruposPadre) {
            cargaGrupos();
        }
    });

</script>

<style scoped>
/* Estilos generales */
.box-card {
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.common-layout {
  width: 100%;
  height: 100%;
}

.titulo {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0;
}

/* Contenedor principal de dos paneles */
.main-content-card {
  width: 100%;
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
  padding: 15px;
}

.dual-panel-container {
  display: flex;
  gap: 16px;
  width: 100%;
  overflow-x: auto;
  padding-bottom: 15px;
}

.tree-panel, .table-panel {
  flex: 0 0 auto;
  width: 680px;
  height: 600px;
}

.table-panel1 {
  flex: 0 0 auto;
  width: 80px !important;
  height: 600px;
}

/* Estilos para la barra de scroll */
.dual-panel-container::-webkit-scrollbar {
  height: 8px;
}

.dual-panel-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.dual-panel-container::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.dual-panel-container::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
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

.highlight-node {
  color: #a52f2f !important;
}

/* Estilos para pantallas muy pequeñas (ajustes mínimos) */
@media (max-width: 600px) {
  .tree-panel, .table-panel {
    width: 95%;
    min-width: 95%;
  }
  
  .dual-panel-container {
    gap: 10px;
  }
}

/* Estilo para el nodo seleccionado en el árbol */
:deep(.el-tree-node.is-current > .el-tree-node__content) {
  background-color: rgb(203, 233, 200) !important;
  color: #0d6efd !important;
}
</style>