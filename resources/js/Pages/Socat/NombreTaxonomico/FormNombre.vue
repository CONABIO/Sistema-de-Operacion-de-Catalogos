<template>
  <div class="form-nombre-container">
    <el-card>
      <el-container>
        <el-header class="header">
          <div class="header-content">
            <h1 class="titulo">Información del taxón</h1>
          </div>
        </el-header>
      <el-main>
        <el-form ref="formRef" :model="nombreTax" :rules="rules" label-width="180px" label-position="left">
          <el-row :gutter="21">
              <el-col :span="16">
                <span class="subtitulo" style="color: red;">
                  Taxón seleccionado: {{ taxonAct?.label }}
                </span>
              </el-col>

              <el-col :span="8" >
                  <div class="botonera-biotica">
                    <NuevoButton v-if="hasPermisos('MnuNomCientifico', 'Altas')"
                                @crear="nuevoTax" toolPosicion="bottom" :habActTax="habNuevo"
                                style="flex-shrink: 0;"/>
                    <EditarButton v-if="hasPermisos('MnuNomCientifico', 'Cambios')"
                                @editar="editarTax()" toolPosicion = 'bottom' :habActTax = 'habMod' 
                                style="flex-shrink: 0;"/>
                    <EliminarButton v-if="hasPermisos('MnuNomCientifico', 'Bajas')" 
                                @eliminar="borrarDatos()" toolPosicion = 'bottom' :habActTax = 'habElim' 
                                style="flex-shrink: 0;"/>
                    <!--Juan Carlos - 05/02/2026 - https://ecoinformatica.atlassian.net/browse/SOCAT-6
                        Se cambia el boton de guardar por el componente del boton guardar-->  
                    <GuardarButton :habilitar = "habGuardar" @click="Guardar"
                                    style="flex-shrink: 0; min-width: max-content;"/>
                    <BotonSalir accion="cerrar" @salir="cerrarDialogo"
                                style="flex-shrink: 0; min-width: max-content;"/>
                  </div>
              </el-col>
          </el-row>
          <el-form-item label = "Nivel taxonómico" prop="catTax" style="max-width: 400px;">
            <el-select v-model="nombreTax.catTax"  
                       placeholder = "Nivel taxonómico" 
                       popper-class="select-verde-dropdown"
                       :disabled = nivelAct>
              <el-option
                v-for="item in categorias"
                      :key="item.id"
                      :label="item.label"
                      :value="item.id">
              </el-option>
            </el-select>
          </el-form-item>   
          <el-form-item label = "Estatus: " prop="estatusTax">
            <div>
              <el-radio-group v-model="nombreTax.estatusTax" @change="CambioEstatus()">
                <el-radio :disabled="estCor" :value="2">{{ estDinamico }}</el-radio>
                <el-radio :disabled="estSin" :value="1">Sinónimo</el-radio>
                <el-radio :disabled="estNa" :value="6">ND</el-radio>
                <el-radio :disabled="estNd" :value="-9">NA</el-radio>
              </el-radio-group>
            </div>
          </el-form-item>
          <el-tabs type="card" v-model="tabInicial">
            <el-tab-pane label="Taxón" name="taxon">
              <el-form-item label = "Taxón" prop = "nombreTaxon">
                <el-input type="text" 
                                maxlength="100" 
                                v-model="nombreTax.nombreTaxon"
                                placeholder="Nombre taxón" 
                                show-word-limit 
                                @keydown="onPressSistC"
                                :disabled="habAltEdic" />
              </el-form-item>
              <el-form-item label="Nombre autoridad" prop="nombreAutoridad">
                <el-input type="textarea" 
                          :rows="2" 
                          maxlength="255" 
                          show-word-limit 
                          placeholder="Nombre autoridad"
                          :disabled="true"
                          :value="autorComp" 
                          style="width: 90%" />
                <el-tooltip class="item" effect="dark" content="Catálogo de Autoridades taxonómicas" placement="bottom">
                  <el-button 
                              @click="carga_autor" 
                              type="primary" 
                              circle style="background-color: #AF7AC5; border-color: #985ede; margin-left: 10px;"
                              :disabled = autorAct>
                    <el-icon>
                      <autoridades />
                    </el-icon>
                  </el-button>
                </el-tooltip>
              </el-form-item>
              <el-form-item label="Sist. Clas. / Catálogo de autoridad / Diccionario" prop="sistClassDicc" >
                <el-input type="textarea" 
                          :row = 2
                          maxlength="255" 
                          show-word-limit placeholder="Sistema de clasificación"
                          @keydown="onPressSistC" 
                          :disabled="habAltEdic"
                          v-model="nombreTax.sistClassDicc" />
              </el-form-item>
              <el-form-item label="Cita nomenclatural" prop="citaNomenclatural">
                <el-input type="text" 
                          maxlength="255" 
                          show-word-limit placeholder="Cita nomenclatural"
                          @keydown="onPressSistC" 
                          :disabled="habAltEdic"
                          v-model="nombreTax.citaNomenclatural" />
              </el-form-item>
              <el-form-item label="Anotación al taxón" prop="anotacionTaxon">
                <el-input type="textarea" 
                          :rows="3" maxlength="1650" 
                          show-word-limit 
                          placeholder="Anotación al taxón"
                          @keydown="onPressSistC" 
                          :disabled="habAltEdic" 
                          v-model="nombreTax.anotacionTaxon" />
              </el-form-item>
              <el-form-item label="Otras observaciones" prop="otrasObservaciones">
                <el-input type="text" 
                          maxlength="50" 
                          show-word-limit 
                          placeholder="Otras observaciones"
                          @keydown="onPressSistC" 
                          :disabled="habAltEdic"
                          v-model="nombreTax.otrasObservaciones" />
              </el-form-item>
            </el-tab-pane>

            <el-tab-pane label="SCAT" name="scat">
                    <el-row :gutter="250" type="flex" align="middle" style="flex-wrap: nowrap;">
                      <el-col :span="10">
                        <table style="border: 1px solid black; width: 100%;">
                          <tbody>
                            <tr style="border: 1px solid black;">
                              <th style="border: 1px solid black;">IdNombre</th>
                              <th style="border: 1px solid black;">IDCat</th>
                            </tr>
                            <tr>
                              <td style="border: 1px solid black; background-color: #F5F527;">{{ idNombre }}</td>
                              <td style="border: 1px solid black; background-color: #F57327;">{{ idCat }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </el-col>
                      <el-col :span="14" style="display:flex; align-items: center; gap: 8px;">
                        <div  style="flex: 1; min-width:0; display: flex; align-items:center;">
                            <el-form-item label="Grupo" 
                                          prop="grpSelec" 
                                          label-width="60px"
                                          style="width: 100%; margin: 0;">
                              <el-select v-model="nombreTax.grpSelec" 

                                          placeholder="Select" 
                                          :disabled="actGrupo" 
                                          popper-class="select-verde-dropdown"
                                          style="width: 100%;"
                                          filterable
                                          clearable>
                                <el-option 
                                  v-for="item in listGrp" 
                                  :key="item.id" 
                                  :label="item.label" 
                                  :value="item.id">
                                </el-option>
                              </el-select>
                            </el-form-item>
                            
                          </div>
                          <el-tooltip class="item" effect="dark" content="Catálogo de Grupos taxonómicos"
                            placement="bottom">
                            <el-button @click="carga_Grupos()" 
                                        circle 
                                        style="flex-shrink: 0; background-color: #a08223;"
                                        :disabled="actGrupo">
                              <el-icon>
                                <filtroGrupos />
                              </el-icon>            
                            </el-button>
                          </el-tooltip>
                      </el-col>
                    </el-row>
                    <br>
                    <el-row :gutter='25'>
                      <el-col :span="7">
                        <el-form-item label="Validación SNIB" prop="valSnib" class="custom-form-item" label-position="top">
                          <el-select 
                              v-model="valSnib" 
                              placeholder="" 
                              :disabled="autorAct" 
                              popper-class="select-verde-dropdown"
                              style="width: 100%">
                            <el-option v-for="item in opcSnib" 
                                      :key="item.id" 
                                      :label="item.label" 
                                      :value="item.id">
                            </el-option>
                          </el-select>
                        </el-form-item>
                      </el-col>
                      
                      <el-col :span="10">
                        <el-form-item label="Nivel de revisión" prop="nivelRev" label-position="top">
                          <el-select 
                                v-model="nombreTax.nivelRev" 
                                placeholder="Nivel de revisión" 
                                :disabled="autorAct" 
                                popper-class="select-verde-dropdown"
                                style="width: 100%">
                            <el-option v-for="item in opcNivRev" 
                                        :key="item.id" 
                                        :label="item.label" 
                                        :value="item.value">
                            </el-option>
                          </el-select>
                        </el-form-item>
                      </el-col>
                      
                      <el-col :span="7">
                        <el-form-item label="Publico" prop="estado" label-position="top">
                          <el-radio-group v-model.number="nombreTax.estado" :disabled="autorAct">
                            <el-radio :disabled="estPubS" :value="1">Si</el-radio>
                            <el-radio :disabled="estPubN" :value="0">No</el-radio>
                          </el-radio-group>
                        </el-form-item>
                      </el-col>
                    </el-row>
                    <br>
                    <el-row :gutter='25'>
                      <el-col :span='9'>
                        <el-row>Id Invasora</el-row>
                        <el-row>
                          <el-input placeholder="Id Invasora" 
                                    type="number"
                                    v-model="idInvasora" 
                                    @keydown="onPressSistC"
                                    :disabled="autorAct" 
                                    :value="idInvasora" />
                        </el-row>
                      </el-col>
                      <el-col :span='6'>
                        <el-row>Comentarios SNIB</el-row>
                        <el-row>
                          <el-input placeholder="Comentarios SNIB" 
                                    v-model="comentariosSnib" 
                                    @keydown="onPressSistC"
                                    :disabled="true" />
                        </el-row>
                      </el-col>
                      <el-col :span='2'>
                        <el-tooltip class="item" effect="dark" content="Total de comentarios al nombre en el SNIB"
                          placement="bottom">
                          <el-button @click="comentarios_Snib()" 
                                      circle 
                                      type="success" 
                                      :disabled="comDet">
                            <el-icon>
                              <comentarioSnib />
                            </el-icon>
                          </el-button>
                        </el-tooltip>
                      </el-col>
                    </el-row>
                    <br>
                    <el-row :gutter='25'>
                      <el-col :span="24">
                        <!--p></p>
                        <el-row>
                          Homonimia SNIB
                        </el-row>
                        <el-row>
                          <el-input type="input" 
                                    placeholder="Homonimia SNIB" 
                                    v-model="homonimiaSnib"
                                    show-word-limit 
                                    @keydown="onPressSistC" 
                                    :disabled="autorAct" 
                                    maxlength="255" />
                        </el-row-->
                        <el-form-item label = "Homonimia SNIB" prop = "homonimiaSnib">
                          <el-input type="text" 
                                          placeholder="Homonimia SNIB" 
                                          v-model="homonimiaSnib"
                                          show-word-limit 
                                          @keydown="onPressSistC"
                                          :disabled="autorAct"
                                          maxlength="255"  />
                        </el-form-item>
                      </el-col>
                    </el-row>
                    <br>
                    <el-row :gutter='25'>
                      <el-col :span='8'>
                        <el-row>IdIUCN</el-row>
                        <el-row>
                          <el-input placeholder="IdIUCN" 
                                    type="number"
                                    v-model="idIUCN" 
                                    @keydown="onPressSistC"
                                    :disabled="autorAct"/>
                        </el-row>
                      </el-col>
                      <el-col :span='8'>
                        <el-row>IdCOL</el-row>
                        <el-row>
                          <el-input placeholder="IdCOL" 
                                    v-model="idCol" 
                                    show-word-limit 
                                    @keydown="onPressSistC"
                                    :disabled="autorAct"
                                    maxlength="255" />
                        </el-row>
                      </el-col>
                      <el-col :span='8'>
                        <el-row>IdCITES</el-row>
                        <el-row>
                          <el-input placeholder="IdCITES" 
                                    v-model="idCites" 
                                    show-word-limit 
                                    @keydown="onPressSistC"
                                    :disabled="autorAct"
                                    maxlength="255"
                                    />
                        </el-row>
                      </el-col>
                    </el-row>
                  </el-tab-pane>
          </el-tabs>
        </el-form>
      </el-main>  
    </el-container>
  </el-card>
    
    <div>
      <DialogAutor v-model="dialogFormVisibleAutor" :botCerrar="false" :pressEsc="false">
        <CurpAutorTax :autorRel = listAutores
                      :nombre = true
                      @traspasoAutores="recibeAutores">
        </CurpAutorTax>
      </DialogAutor>
    </div>
      
    <div>
      <!--Juan Carlos - 26/01/2026 - https://ecoinformatica.atlassian.net/browse/SOCAT-6
        Se agregaron las propiedades para determinar si es modal y se muestra el
        boton de traspaso-->
      <DialogGrp v-model="dialogFormVisibleGrupos" :botCerrar="true" :pressEsc="true">
        <CurpGruposTax :isModal = "true"  @cerrar="cerrar_Grupos" :traspaso ="false" />
      </DialogGrp>
    </div>

    <div>
      <DialogComSnib v-model="dialogFormVisibleComentarios" :botCerrar="true" :pressEsc="true">
        <ComenSnibNom :taxon = "taxonAct"  @cerrar="closeDialogComSnib" />
      </DialogComSnib>
    </div>

    <Teleport to="body">
      <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
        :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
        @close="cerrarNotificacion" />
    </Teleport>
  </div>
</template>

<script setup>
  import { ref, reactive, onMounted, watch, defineExpose} from 'vue';
  import { usePage } from '@inertiajs/inertia-vue3';
  import { nextTick } from 'vue';
  import { ElMessage, ElInput, ElPopconfirm, ElMessageBox} from 'element-plus';
  import axios from 'axios';
  import { User, Connection, ChatDotSquare, InfoFilled } from '@element-plus/icons-vue';
  import CurpAutorTax from '@/Pages/Socat/Autores/CuerpoAutorTaxon.vue';
  import CurpGruposTax from '@/Pages/Socat/Grupos/CuerpoGrupoTaxon.vue';
  import ComenSnibNom from '@/Pages/Socat/NombreTaxonomico/ComentariosSnibNombre.vue';
  import NuevoButton from '@/Components/Biotica/NuevoButton.vue';
  import EditarButton from '@/Components/Biotica/EditarButton.vue';
  import EliminarButton from '@/Components/Biotica/EliminarButton.vue';
  import DialogAutor from '@/Components/Biotica/DialogGeneral.vue';
  import DialogGrp from '@/Components/Biotica/DialogGeneral.vue';
  import DialogComSnib from '@/Components/Biotica/DialogGeneral.vue';
  import usePermisos from '@/composables/usePermisos';
  import autoridades from '@/Components/Biotica/Icons/Autor.vue';
  import filtroGrupos from '@/Components/Biotica/Icons/Conectado.vue';
  import comentarioSnib from '@/Components/Biotica/Icons/Comentarios.vue';
  import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
  import BotonSalir from '@/Components/Biotica/SalirButton.vue';
  import GuardarButton from '@/Components/Biotica/GuardarButton.vue';
  import { showConfirmMessage } from '@/Composables/mensajeConfirm';

  const { permisos, usuario } = usePermisos();

  const page = usePage();

  const taxon = ref('');

  const hasPermisos = (etiqueta, modulo) => {
      
      const permiso = permisos.find(item => item.NombreModulo === etiqueta);

      return permiso[modulo];
  };

  const abrirAutorTaxon = () => {
    dialogFormVisibleAutor.value = true;
  };

  const props = defineProps({
    autTaxEdit: [],
    taxonAct: {
      type: Object,
      default: () => ({})
    },
    paginaActual: Number,
    categoria: {
      type: String,
      default: ""
    },
    catalogos: [], 
    regNomenclatura: {
      type: Number,
      default: 0
    },
    numHijos: {
      type: Number,
      default: 0
    },
    nuevoTax:{
      type: Boolean,
      default: false
    },
    catNuevoTax:{
      type: Object,
      default: () => ({})
    }, 
     categoriasTax: {
      type: Object,
  }
  });

  const habNuevo = ref(false);
  const habMod = ref(false);
  const habElim = ref(false);
  const muestraGrd = ref(false);
  const currentZIndex = ref(1000);
  const estCor = ref(false);
  const estSin = ref(false);
  const estNa = ref(false);
  const estNd = ref(false);
  const estPubS = ref(false);
  const estPubN = ref(false);
  const actGrupo = ref(true);
  const estDinamico = ref("");
  const categorias = ref([]);
  const listGrp = ref([]);
  const listAutorTax = ref([]);
  const autorComp = ref("");
  const listAutores = ref([]);
  const tabInicial = ref("taxon");

  const relnomcat = ref(0);

  const habGuardar = ref(true);

  const opcSnib = ref([{
    id: 'si',
    label: 'Si'
  }, {
    id: 'no',
    label: 'No'
  }, {
    id: '',
    label: ''
  }
  ]);

  const opcNivRev = ref([{
    value: 'A',
    label: 'A - Publicado por especialista'
  }, {
    value: 'B',
    label: 'B - Revisado (no publicado) por especialista'
  }, {
    value: 'C',
    label: 'C - Compilación de literatura especializada'
  }, {
    value: 'D',
    label: 'D - Bases de datos especializadas'
  }, {
    value: 'E',
    label: 'E - Lista tipo checklist'
  }
  ]);

  const Observaciones = ref('');
  const comentariosSnib = ref('');
  const idInvasora = ref('');
  const idCol = ref('');
  const idCites = ref('');
  const idIUCN = ref('');
  const homonimiaSnib = ref('');
  const comDet = ref(true);
  const accion = ref('');
  const valSnib = ref('');
  const categ = ref('');
  const idNombre = ref('');
  const idNom = ref(0);
  const idCat = ref('');
  const grpSelec= ref('');
  const nivelAct = ref(true);
  const autorAct = ref(true);
  const borrarAct = ref(true);
  const habAltEdic = ref(props.habAltEdic_prop)
  const habActTax = ref(false);
  const habModTax = ref(false);
  const dialogFormVisibleGrupos = ref(false);
  const dialogFormVisibleComentarios = ref(false);
  const dialogFormVisibleAutor = ref(false);

  const notificacionTitulo = ref('');
  const notificacionMensaje = ref('');
  const notificacionTipo = ref('info');
  const notificacionDuracion = ref(5000);
  const notificacionVisible = ref(false);
  const numEjemp = ref('');

  const emit = defineEmits(['regresaTaxMod', 'cerrar', 
                            'resultadoAlta', 'resultadoBaja']);
  const formRef = ref(null);
  const nombreTax = reactive({
    nombreTaxon: '',
    nombreAutoridad: '',
    sistClassDicc: '',
    citaNomenclatural: '',
    anotacionTaxon: '',
    otrasObservaciones: '',
    catTax: '',
    estatusTax: '',
    estado: '',
    grpSelec: [],
    nivelRev: '',
  });

  const rules = ref({
    nombreTaxon: [
      { required: true, message: 'Ingrese un nombre de taxón', trigger: 'blur' },
      { min: 1, max: 100, message: 'El tamaño debe ser entre 1 y 100', trigger: 'blur' }
    ],
    nombreAutoridad: [
      { required: true, message: 'Ingrese un nombre de autoridad', trigger: 'blur' },
      { max: 255, message: 'El tamaño debe ser menor o igual a 255 caracteres', trigger: 'blur' }
    ],
    sistClassDicc: [
      { required: true, message: 'No es posible ingresar un taxón sin el dato del sistema de clasificación, catálogo de autoridad o diccionario', trigger: 'blur' },
      { max: 255, message: 'El tamaño debe ser menor o igual a 255 caracteres', trigger: 'blur' }
    ],
    citaNomenclatural: [
      { max: 255, message: 'El tamaño debe ser menor o igual a 255 caracteres', trigger: 'blur' }
    ],
    anotacionTaxon: [
      { max: 1650, message: 'El tamaño debe ser menor o igual a 1650 caracteres', trigger: 'blur' }
    ],
    otrasObservaciones: [
      { max: 255, message: 'El tamaño debe ser menor o igual a 50 caracteres', trigger: 'blur' }
    ],
    catTax: [
      { required: true, message: 'Se debe seleccionar una categoria taxonomica', trigger: 'change' }
    ],
    estatusTax: [
      { required: true, message: 'Debe seleccionar el estatus', trigger: 'change' }
    ],
    estado: [
      { required: true, message: 'Debe seleccionar el estado', trigger: 'change' }
    ],
    grpSelec: [
      { required: true, message: 'Se debe seleccionar un grupo', trigger: 'change' }
    ],
    nivelRev: [
      { required: true, message: 'Se debe seleccionar nivel de revisión', trigger: 'change' }
    ],
    homonimiaSnib: [
      { max: 255, message: 'El tamaño debe ser menor o igual a 255 caracteres', trigger: 'blur' }
    ]
  });

  const autTaxComp = ref('')

  const recibeAutores = (autores, autoridadTax) =>{
    listAutorTax.value = autores.value;
    autorComp.value = autoridadTax.value.trim();
    nombreTax.nombreAutoridad = autoridadTax.value.trim();
    dialogFormVisibleAutor.value= false;
  }

  const carga_inicio = () => {  
    muestraGrd.value = true;
    estCor.value = true;
    estSin.value = true;
    estNa.value = true;
    estNd.value = true;
    habGuardar.value = true;
    
    Object.assign(nombreTax, {
      nombreTaxon : props.taxonAct?.completo?.Nombre || '',
      nombreAutoridad : props.taxonAct?.completo?.NombreAutoridad || '',
      sistClassDicc : props.taxonAct?.completo?.SistClasCatDicc || '',
      citaNomenclatural : props.taxonAct?.completo?.CitaNomenclatural || '',
      anotacionTaxon : props.taxonAct?.completo?.Anotacion || '',
      otrasObservaciones : props.taxonAct?.completo?.otrasObservaciones || '',
      catTax : props.taxonAct?.completo?.categoria?.NombreCategoriaTaxonomica || '',
      estatusTax : props.taxonAct?.completo?.Estatus || '',
      estado : props.taxonAct?.completo?.scat?.Publico
    });
    idNombre.value = props.taxonAct?.completo?.IdNombre || '';
    idCat.value = props.taxonAct?.completo?.scat?.IDCAT || '';
    idInvasora.value = props.taxonAct?.completo?.scat.ListaInvasoras || '';
    idCol.value = props.taxonAct?.completo?.scat.IdCOL || '';
    idCites.value = props.taxonAct?.completo?.scat.IdCITES || '';
    idIUCN.value = props.taxonAct?.completo?.scat.IdIUCN || '';
    autorComp.value = props.taxonAct?.completo?.NombreAutoridad;
    nivelAct.value = true;
    autorAct.value = true;
    borrarAct.value = false;
    habAltEdic.value = true;
    comDet.value = true;
    listAutorTax.value = props.taxonAct?.completo?.rel_nombre_autor;
    numEjemp.value = 0;
    cargaListGrp();
    cargaValSnib();
    cargaNivRev();
    cargaComSnib();
    validaHijos();
    comDet.value = false;
    borrarAct.value = false;
    habAltEdic.value = true;
    nivelAct.value = true;
    autorAct.value = true;
    habActTax.value = false;
    habModTax.value = false;
    cargaCategorias();
    
    if (props?.taxonAct?.completo?.categoria?.IdNivel1 < 5) {
      actGrupo.value = true;
    }

    if (props?.taxonAct?.completo?.categoria?.IdNivel2 === 1) {
      estDinamico.value = "Valido";
    } else {
      estDinamico.value = "Correcto";
    }

    habNuevo.value = false;
    habMod.value = false; 
    habElim.value = false;

    if(props.nuevoTax){
      muestraGrd.value = false;
      estCor.value = true;
      estSin.value = true;
      estNa.value = true;
      estNd.value = true;
      habGuardar.value = false;

      Object.assign(nombreTax, {
        catTax : props.catNuevoTax.NombreCategoriaTaxonomica,
        estatusTax : 2,
        grpSelec : ""
      });

      cargaListGrp();

      autorAct.value = false;
      valSnib.value = '';

      comDet.value = true;
      borrarAct.value = true;
      habAltEdic.value = false;
      habActTax.value = true;
      habModTax.value = true;
      accion.value = 'crear';
      estPubS.value = false;
      estPubN.value = false;
      listAutores.value = [];
      numEjemp.value = 0;
      
      actGrupo.value = false;
    
      habNuevo.value = true;
      habMod.value = true; 
      habElim.value = true;
    }

  };

    const cerrarDialogo = () => {
          tabInicial.value = "taxon";
          emit('cerrar');
    };

  const carga_Grupos = () => {
    dialogFormVisibleGrupos.value = true;
  };

  /*Esta función es para cerrar el dialog de grupos taxonomicos*/
  const cerrar_Grupos = () => {
    
    cargaListGrp();

    dialogFormVisibleGrupos.value = false;
  };

  const comentarios_Snib = () => {
    dialogFormVisibleComentarios.value = true;
  };

  const closeDialogComSnib= () => {
    dialogFormVisibleComentarios.value = false;
  }

  const cargaListGrp = async () => {
      const response = await axios.get('/carga-list-grp');

      if (response.status === 200) {
        listGrp.value = response.data;

        if(Object.keys(props.taxonAct).length > 0)
        {
          let idGrp = props.taxonAct.completo.scat.IdGrupoSCAT;
          const resp = listGrp.value.find(grupo => grupo.id === idGrp);
          if(resp){
            nombreTax.grpSelec = resp.label;
          }
          grpSelec.value = resp.id;
        }
      }
      else {
        console.log("Se presentó un error en la recuperación de los datos");
      }
  };

  const validaHijos = async () => {
    if(Object.keys(props.taxonAct).length > 0)
    {
      let params;
      params = {
        idNombre: props.taxonAct.completo.IdCategoriaTaxonomica,
        IdNivel1: props.taxonAct.completo.categoria.IdNivel1,
        IdNivel2: props.taxonAct.completo.categoria.IdNivel2
      };

      const response = await axios.get('/carga-categ', params);
      
      if (response.status === 200) {
        if(response.data.length === 0)
        {
          habActTax.value = true;
        }else{
          habActTax.value = false;
        }
      }
      else {
        console.log("Se presentó un error en la recuperación de los datos");
      }
    }
  }; 

  const cargaCategorias = async () => {    
    if(Object.keys(props.taxonAct).length > 0)
    {
      let params = {
        idNombre: props.taxonAct?.completo?.IdCategoriaTaxonomica,
        IdNivel1: props.taxonAct.completo.categoria.IdNivel1,
        IdNivel2: props.taxonAct.completo.categoria.IdNivel2
      };

      const response = await axios.get('carga-categ', { params });
      if (response.status === 200) {
          categorias.value = response.data;
          categorias.value.unshift({
          id: props.taxonAct.completo.categoria.IdCategoriaTaxonomica,
          label: props.taxonAct.completo.categoria.NombreCategoriaTaxonomica
        });
      }else {
        console.log("Se presentó un error en la recuperación de los datos");
      }
    }
  };

  const cargaValSnib = async () => {

    valSnib.value = '';

    if (props?.taxonAct?.completo?.scat) {
      
      let valSnibValue = props.taxonAct.completo.scat.ValidacionSNIB;
  
      if(valSnibValue !== null)
      {
        let resp = opcSnib.value.find(niv => niv.id === valSnibValue);

        if (typeof resp === 'undefined') {
          valSnib.value = '';
        } else {
          valSnib.value = resp.label;
        }
      }
    }
  };

  const cargaNivRev = async () => {
    if (props?.taxonAct?.completo?.scat) {
      let nivRev = props.taxonAct.completo.scat.Nivel_de_revision;

      let resp = opcNivRev.value.find(niv => niv.value === nivRev);
      
      if (typeof resp === 'undefined') {
        nivRev.value = '';
      } else {
        nombreTax.nivelRev = resp.label;
      }
    }
  };

  const cargaComSnib = async () => {
    if(Object.keys(props.taxonAct).length > 0)
    {  

      if (props.taxonAct.completo.scat) {
        homonimiaSnib.value = props.taxonAct.completo.scat.HomonimiaSNIB;
        idInvasora.value = props.taxonAct.completo.scat.ListaInvasoras;
      }

      let params = {
        idNombre: props.taxonAct.id
      };
    
      const response = await axios.get('/cargar-comSnib', {params});
      
      if (response.status === 200) {

        comentariosSnib.value = response.data.snib[0].NumComEjemplares;
        numEjemp.value = response.data.snib[0].NumEjemplares
        relnomcat.value = response.data.relNombreCat
      
        if (comentariosSnib.value > 0) {
            comDet.value = false;
          
          } else {
            comDet.value = true;
          }
      }
      else {
        console.log("Se presentó un error en la recuperación de los datos");
      }
    }
  };

  const AltaEstatus = async () =>{
    if(props.taxonAct.completo.categoria.IdNivel1 < 4)
      {
        nombreTax.estatusTax = props.taxonAct.completo.Estatus; 
        estCor.value = true;
        estSin.value = true;
        estNa.value= true;
        estNd.value = true;
        return; 

      }
      switch(props.taxonAct.completo.Estatus){
        case 2:
          estCor.value = false;
          estSin.value = false;
          estNa.value = false;
          estNd.value = false;
        break;
        case 1:
          estCor.value = true;
          estSin.value = false;
          estNa.value = false;
          estNd.value = false;
          console.log("valido: ", estCor.value, 
                      "sinonimo: ", estSin.value,
                      "na: ", estNa.value,
                      "Nd: ", estNd.value);
        break;
        case 6:
        console.log("Entre a case 9");
          estCor.value = true;
          estSin.value = true;
          estNa.value = false;
          estNd.value = false;
        break;
      }         
  };

  const nuevoTax = async() => {
    muestraGrd.value = false;

    accion.value = "crear"; 

    if (props.taxonAct.estatus.value === 'NA') {
      await mostrarNotificacion(
            "Aviso",
            "No es posible ingresar un nuevo taxón que tenga como ascendente a un taxón con estatus NA",
            "error",
            5000
          );
      nuevoTax.break();
    }
  
    if (props.taxonAct.completo.categoria.IdNivel1 < 5) {
      actGrupo.value = false;
    }else{
      actGrupo.value = true;
    }
    
    habNuevo.value = true;
    habMod.value = true; 
    habElim.value = true;

    nombreTax.estatusTax = '';
    nivelAct.value = false;
    autorAct.value = false;
    nombreTax.catTax = '';
    valSnib.value = '';
    habGuardar.value = false;

    const resp = await AltaEstatus();
    
    nombreTax.nombreTaxon = '';
    nombreTax.nombreAutoridad = '';
    nombreTax.sistClassDicc = '';
    nombreTax.citaNomenclatural = '';
    nombreTax.anotacionTaxon = '';
    nombreTax.otrasObservaciones = '';
    nombreTax.nivelRev= '';
    nombreTax.estado= '';
    autorComp.value = '';
    idInvasora.value = '';
    idCol.value = '';
    idCites.value = '';
    idIUCN.value = '';
    homonimiaSnib.value = '';
    idNombre.value = '';
    idCat.value = '';
    grpSelec.value = '';
    comentariosSnib.value = '';
    comDet.value = true;
    borrarAct.value = true;
    habAltEdic.value = false;
    habActTax.value = true;
    habModTax.value = true;
    accion.value = 'crear';
    idNom.value = props.taxonAct.completo.IdNombre;
    estPubS.value = false;
    estPubN.value = false;
    listAutores.value = [];
    numEjemp.value = 0;
  };

  const editarTax = () => {
    if (props.taxonAct.completo.categoria.IdNivel1 < 5) {
      actGrupo.value = false;
    }
    habNuevo.value = true;
    habMod.value = true; 
    habElim.value = true;
    muestraGrd.value = false;
    comDet.value = true;
    borrarAct.value = true;
    habAltEdic.value = false;
    habActTax.value = true;
    habModTax.value = true;
    autorAct.value = false;
    accion.value = 'editar';
    idNom.value = props.taxonAct.completo.IdNombre;
    nombreTax.nivelRev = props.taxonAct.completo.scat.Nivel_de_revision;
    listAutores.value = props.taxonAct.completo.rel_nombre_autor;
    estPubS.value = false;
    estPubN.value = false;
    estCor.value = false;
    estSin.value = false;
    estNa.value = false;
    estNd.value = false;
    habGuardar.value = false;
    CambioEstatus();
  };

  const CambioEstatus = () => {
    let params;

    params = {
      estatusInicio: props.taxonAct.completo.Estatus,
      nomAct: props.taxonAct.id
    };

    if(accion.value === 'crear')
    {
      return;
    }

    axios.get("/valCamEstatus", { params }).then((response) => {
      switch (props.taxonAct.completo.Estatus) {
        case 1:
          estCor.value = response.data != 1 ? true : false;
          estSin.value = false;
          estNa.value = false;
          estNd.value = false;
          break;
        case 2:
          estCor.value = false;
          estSin.value = response.data != 1 ? false : true;
          estNa.value = false;
          estNd.value = false;
          break;
        case 6:
          estCor.value = response.data != 1 ? true : false;
          estSin.value = response.data != 1 ? true : false;
          estNa.value = false;
          estNd.value = false;
          break;
        case -9:
          estCor.value = response.data != 1 ? true : false;
          estSin.value = response.data != 1 ? true : false;
          estNa.value = false;
          estNd.value = false;
          break;
      }
    });
  };

  const borrarDatos = async () => {
    
    const confirmado = await showConfirmMessage({
                      title: 'Confirmar eliminación',
                      message: '¿Estás seguro de eliminar el taxón actual?'
                  });

    if (!confirmado) {
        return;
    }

    let rel = props.regNomenclatura;
    let hijos = props.numHijos;

    let mensaje = '<strong>El nombre no puede ser eliminado ya que tiene: </p> <br></strong>';

    if (numEjemp.value > 0) {
      if (numEjemp.value === 1) {
        mensaje += '<strong>' + numEjemp.value + ' ejemplar relacionado en el SNIB. </p> <br></strong>';
      } else {
        mensaje += '<strong>' + numEjemp.value + ' ejemplares relacionados en el SNIB. </p> <br></strong>';
      }
    }

    if (rel > 0) {
      if (rel === 1) {
        mensaje += '<strong>' + rel + ' taxón relacionado. </p> <br></strong>';
      } else {
        mensaje += '<strong>' + rel + ' taxones relacionados. </p> <br></strong>';
      }
    }

    if (hijos > 0) {
      if (hijos === 1) {
        mensaje += '<strong>' + hijos + ' taxón hijo </p> <br></strong>';
      } else {
        mensaje += '<strong>' + hijos + ' taxones hijos </p> <br></strong>';
      }
    }

    if (relnomcat.value > 0) {
      if (relnomcat.value === 1) {
        mensaje += '<strong>' + relnomcat.value + ' caracteristica relacionada </p> <br></strong>';
      } else {
        mensaje += '<strong>' + relnomcat.value + ' caracteristicas relacionadas </p> <br></strong>';
      }
    }

    if (comentariosSnib.value > 0) {

      if (comentariosSnib.value === 1) {
        mensaje += '<strong>' + comentariosSnib.value + ' comentario en el SNIB </p> <br></strong>';
      } else {
        mensaje += '<strong>' + comentariosSnib.value + ' comentarios en el SNIB </p> <br></strong>';
      }
    }

    if (rel > 0 || hijos > 0 || relnomcat.value > 0 || comentariosSnib.value > 0) {
      await mostrarNotificacion(
          "Aviso",
          mensaje,
          "Error",
          5000
        );
      return;
    }
  
    const params = { alias: usuario.user.Alias }

    try{
        const response = await axios.put(`/baja-nombre/${props.taxonAct.id}`, params);
                    
        emit('cerrar', false);
        emit('resultadoBaja', response.data);

        await mostrarNotificacion(
          "Aviso",
          "El taxón fue eliminado exitosamente",
          "success",
          5000
        );

      }
      catch(error){
        console.log("este es el error: ", error);
          if (error.response.status === 422) {
              const errorMessages = Object.values(error.response.data.errors).flat();
              errorMessages.forEach(msg => {
                  mostrarNotificacion(
                    "Aviso",
                    msg,
                    "Error",
                    5000
                  );
              });
        }
      }

  };

  const carga_autor = () => {
    dialogFormVisibleAutor.value = true;
  };

  const muestraFiltros = () => {
    nombreTax.nombreAutoridad = autTaxComp.value;
  };

  const onPressSistC = async (e) => {
    let allowedKeys = [0, 1, 2, 3, 4, 5, 6, 7, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
      21, 22, 23, 24, 25, 26, 27, 28, 29, 30
    ];

    if (allowedKeys.indexOf(e.keyCode) > -1) {
      event.preventDefault();
      return false;
    }
  };

  const resetForm = () => {
    nombreTax.nombreTaxon = '';
    nombreTax.nombreAutoridad = '';
    nombreTax.sistClassDicc = '';
    nombreTax.citaNomenclatural = '';
    nombreTax.anotacionTaxon = '';
    nombreTax.otrasObservaciones = '';
    nombreTax.catTax = '';
    nombreTax.estatusTax = '';
    nombreTax.estado = '';
    nombreTax.grpSelec = [];
    nombreTax.nivelRev = '';
  };

  const cambioPublico = async (estadoTaxon)=> {

    if(numEjemp.value > 0 && estadoTaxon != 1)
      {
        await mostrarNotificacion(
          "Aviso",
          'No se puede cambiar el valor a no publico ya que tiene nombres relacionados en el SNIB',
          "Error",
          5000
        );
        return false; 
      }
      return true;
  }

  const mostrarNotificacion = (titulo, mensaje, tipo = "info", duracion = 5000) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = duracion;
    notificacionVisible.value = true;
  };

  const mostrarNotificacionError = (titulo, mensaje, tipo = "error", duracion = 5000) => {
      notificacionTitulo.value = titulo;
      notificacionMensaje.value = mensaje;
      notificacionTipo.value = tipo;
      notificacionDuracion.value = duracion;
      notificacionVisible.value = true;
  };

  const cerrarNotificacion = () => {
    notificacionVisible.value = false;
  };

  const Guardar = async () =>{
    
    if (!formRef.value) return;
    
    const esValido = await formRef.value.validate().then(() => true).catch(() => false);

    if (!esValido) {
      await mostrarNotificacion(
        "Aviso",
        'Por favor, complete todos los campos requeridos.',
        "Error",
        5000
      );
      return;
    }

    const estadoPub = await cambioPublico(nombreTax.estado);
    
    if(!estadoPub)
    {
      return;
    }

    let grupoScat;
    let nivRevision;
    let catTax;
    let IdAscOblig;
    
    if(typeof nombreTax.grpSelec === 'number'){
      grupoScat =  nombreTax.grpSelec;
    }else{
      const grupo = listGrp.value.find(grupo => grupo.label === nombreTax.grpSelec);
      grupoScat = grupo.id;
    }

    if(nombreTax.nivelRev != '')
    {
      nivRevision = nombreTax.nivelRev;
    }
    
    if (props.nuevoTax)
    {
      catTax = props.catNuevoTax.IdCategoriaTaxonomica;
    }
    else{
      if(typeof nombreTax.catTax === 'number'){
        catTax = nombreTax.catTax;
      }else{
        const categ = categorias && nombreTax && categorias.value.find(cat => cat.label === nombreTax.catTax);
        catTax = categ;
      }
    }

    if(!props.nuevoTax){
      if(props.taxonAct.completo.categoria.IdNivel3 === 0){
        IdAscOblig = props.taxonAct.completo.IdNombre;
      }
      else{
        IdAscOblig = props.taxonAct.completo.IdAscendObligatorio;
      }
    }else{
      IdAscOblig = 1;
    }
    

    let params;
    let res;

    switch(accion.value){
                case 'crear':
                if(Array.isArray(listAutorTax.value) && 
                                        listAutorTax.value.length === 0){
                    await mostrarNotificacion(
                      "Aviso",
                      'No es posible continuar ya que no hay valores de lista de autores.',
                      "Error",
                      5000
                    );
                    return;
                  }
                  params = {
                    scat:{
                      Grupo: grupoScat,
                      HomonimiaSnib: homonimiaSnib.value,
                      IdInvasora: idInvasora.value,
                      NivelRevision: nivRevision,
                      ValidacionSnib: valSnib.value.toLowerCase(),
                      idIUCN: idIUCN.value,
                      idCol: idCol.value, 
                      idCites: idCites.value, 
                    },

                    nombreTaxon:{
                      IdAscendente: props.taxonAct.id,
                      IdAscenOblig: IdAscOblig,
                      NombreTax: nombreTax,
                      alias: usuario.user.Alias,
                      categoria: {id:catTax},
                    },
                  
                    relNombreAutor:{
                      listAutor: listAutorTax.value,
                    },
                    
                    raiz:props.nuevoTax
                  };
                  
                  if(props.nuevoTax){
                    params.nombreTaxon.IdAscendente = 1;
                  }

                  try{
                      
                      const response = await axios.post(`/nombres-store`, params);
                      emit('cerrar', false);
                      emit('resultadoAlta', response.data.nombreNuevo);
                  }
                  catch(error){
                    console.log("este es el error: ", error);
                      if (error.response.status === 422) {
                          const errorMessages = Object.values(error.response.data.errors).flat();
                          errorMessages.forEach(msg => {
                              mostrarNotificacion(
                                "Aviso",
                                msg,
                                "Error",
                                5000
                              );
                          });
                      }
                  }
                break;
                case 'editar':  
                
                  const confirmado = await showConfirmMessage({
                      title: 'Confirmar modificación',
                      message: '¿Estás seguro de guardar los cambios para el taxón actual?'
                  });

                  if (!confirmado) {
                      return;
                  }
                  console.log("Esto es props taxAct: ", listAutorTax.value);
                  if(Array.isArray(listAutorTax.value) && 
                                        listAutorTax.value.length === 0){
                    await mostrarNotificacionError(
                                "Aviso",
                                'No es posible continuar ya que no hay valores de lista de autores.',
                                "Error",
                                5000
                              );
                    return;
                  }

                  params = {
                    scat:{
                      Grupo: grupoScat,
                      HomonimiaSnib: homonimiaSnib.value,
                      IdInvasora: idInvasora.value,
                      NivelRevision: nivRevision,
                      ValidacionSnib: valSnib.value.toLowerCase(),
                      idIUCN: idIUCN.value,
                      idCol: idCol.value, 
                      idCites: idCites.value, 
                    },

                    nombreTaxon:{
                      IdAscendente: props.taxonAct.completo.IdNombreAscendente,
                      IdAscenOblig: props.taxonAct.completo.IdAscendObligatorio,
                      NombreTax: nombreTax,
                      alias: usuario.user.Alias,
                      categoria: catTax,
                    },

                    relNombreAutor:{
                      listAutor: listAutorTax.value,
                    }
                  };       
                  
                  try{
                      const response = await axios.put(`/actualiza-nombre/${props.taxonAct.id}`, params);
                      
                      emit('cerrar', false);
                      emit('regresaTaxMod', response.data.nombreMod);
                  }
                  catch(error){
                    console.log("este es el error: ", error);
                      if (error.response.status === 422) {
                          const errorMessages = Object.values(error.response.data.errors).flat();
                          errorMessages.forEach(msg => {
                              mostrarNotificacion(
                                "Aviso",
                                msg,
                                "Error",
                                5000
                              );
                          });
                      }
                  }
                break;
                default:
                break;
              } 

  }

  defineExpose({ resetForm, carga_inicio, tabInicial });

  watch(
    ()=> props.taxonAct,
    (newValue, oldValue) => {
      carga_inicio();
    },
    { deep: true, inmediate: true }
  );

  onMounted(() => {
    carga_inicio();
    try {
      usuario.value = page.props.value?.auth.user;
    } catch (error) {
      console.error('Error al obtener el usuario: ', error);
    }
  });
</script>

<style scoped>

  /*Juan Carlos 17/02/2026
  Esta funcion se agrega para evitar el conflicto de tailwind con element-plus
  si se retira la parte filtrable del select no funciona */
  :deep(.el-select__input) {
    background-color: transparent !important;
    border: none !important;
    padding: 0 !important;
    box-shadow: none !important;
  }

  .form-nombre-container {
    padding: 1px;
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

  .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .titulo {
        font-size: 1.25rem;
        font-weight: bold;
        color: #333;
        margin: 0;
        text-align: center;
    }

  .form-header {
    padding: 20px;
    min-width: 190px;
    height: 180px;
    box-sizing: border-box;
  }

  .icono {
    margin-right: 8px;
  }

  .custom-tree-node {
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    padding-right: 8px;
    width: 50%;
    overflow: auto;
  }

  .filter-tree .el-tree-node.is-current>.el-tree-node__content {
    background-color: rgb(203, 233, 200) !important;
    color: rgb(0 17 255) !important;
  }

  .filter-tree .el-tree-node.is-current .el-tree-node__children {
    background-color: transparent !important;
    color: inherit !important;
  }

  .highlight-node {
    color: #a52f2f !important;
  }

  .greenClass {
    background: rgb(90, 177, 90);
  }

  .redClass {
    background: rgb(226, 119, 119);
  }

  .context-menu {
    display: block !important;
    visibility: visible !important;
    position: absolute;
    z-index: 9999;
    background-color: hsl(223, 41%, 93%);
    border: 1px solid #dcdfe6;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    padding: 20px;
    min-width: 190px;
    height: auto;
    box-sizing: border-box;
  }

  .el-menu-item {
    padding: 4px 12px;
    font-size: 14px;
    line-height: 16px;
    height: auto;
  }

  .menu-item-submenu {
    padding: 4px 12px;
    font-size: 14px;
    line-height: 16px;
    height: auto;
  }

  .el-submenu .el-menu-item {
    padding: 4px 12px;
    font-size: 14px;
    line-height: 16px;
    height: auto;
  }

  .el-submenu__title {
    padding: 4px 12px;
    font-size: 14px;
    line-height: 16px;
    height: auto;
  }

  .icon-style {
    width: 16px;
    height: 16px;
    fill: currentColor;
    margin-right: 2px;
    vertical-align: middle;
  }

  .botonera-biotica {
      display: flex;
      gap: 12px !important;
      align-items: center;
      flex-wrap: wrap;
      justify-content: flex-end;
      width: 100%;
    }

    .botonera-biotica * {
      margin: 0 !important;
      padding: 0 !important;
      flex-shrink: 0;
    }
    
  .el-icon {
    font-size: 16px;
    margin-right: 2px;
    vertical-align: middle;
  }

  .custom-form-item .el-form-item__content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }
  .custom-form-item .el-form-item__label {
    margin-bottom: 8px;
  }

  .flex-container {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .flex-container span {
    white-space: nowrap;
  }

  .el-input,
  .el-cascader {
    flex: 1;
    min-width: 150px;
  }

  .tree-node-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
  }

  .tree-node-logo {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
  }

  .tree-node-label {
    font-size: 14px;
    line-height: 20px;
  }

  .el-tree-node:hover {
    background-color: transparent !important;
  }

  @media (max-width: 768px) {
    .filter-tree {
      min-width: 98%;
    }

    .el-aside {
      width: auto !important;
      height: auto !important;
      min-height: auto !important;
      max-width: 100%;
      margin-bottom: 30px;
    }

    .d-table-cell {
      max-width: 100%;
    }

    .el-header {
      width: 100%;
    }

    .el-row {
      justify-content: flex-start;
    }
}

</style>
<style scoped>
  :deep(.el-dialog) {
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    width: 90%;
  }

  @media (min-width: 768px) {
    .el-dialog {
      max-width: 900px;
    }
  }

  @media (max-width: 767px) {
    .el-dialog {
      width: 95%;
      margin: 0 auto;
      top: 15%;
      max-width: none;
    }
  }



.select-verde-dropdown .el-select-dropdown__item.is-selected {
  background-color: rgb(203, 233, 200) !important;
  color: #0d6efd !important;
  font-weight: bold;
}

.select-verde-dropdown .el-select-dropdown__item.is-selected.is-hovering {
  background-color: rgb(203, 233, 200) !important;
}

.select-verde-dropdown .el-select-dropdown__item.is-hovering:not(.is-selected) {
  background-color: rgb(240, 245, 239)  !important;
}
</style>