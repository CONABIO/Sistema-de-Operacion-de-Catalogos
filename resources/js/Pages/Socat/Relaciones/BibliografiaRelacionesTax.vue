<template>
  <div>
    <el-card class="box-card">
      <div class="common-layout">
        <el-container style="height: 98vh;">
          <el-header style="background: #f5f5f5; padding: 10px; flex-shrink: 0; display: flex; justify-content: space-between; ">
            <el-row :gutter="10" align="middle">
              <h2 class="titulo">Citas bibliograficas asociadas</h2>
            </el-row>
            <div class="form-actions">
                <BotonSalir accion="cerrar" @salir="cerrarDialogo" />
            </div>  
          </el-header>
            <el-main style="padding: 15px; background: #fff; overflow: hidden;">
                <el-row>
                    <el-card class="main-content-card">
                        <div class="dual-panel-container">
                            <el-card class="table-panel">
                                <span style="font-size: 20px; font-weight: bold;">
                                    Nombres cientificos asociados a:
                                </span>
                                <br/>
                                <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                    {{ props.taxonAct.label }}
                                </span>
                                <br/>
                                <br/>
                                <span style="font-size: 18px; color: #2A661E; font-weight: bold;">
                                    {{ tipRelacion }}
                                </span>
                                <br/>
                                <div style="flex: 1; overflow-y: auto; border: 1px solid #dcdfe6; border-radius: 4px; margin-top: 10px;">
                                    <TablaFiltrable :container-class="'main-section'" :columnas="columnasDefinidas"
                                        v-model:datos = "tablaRelaciones" v-model:total-items="props.totalRegistros"
                                        :opciones-filtro = "opcionesFiltroNomenclatura"
                                        :origen = "true"
                                        @row-click = "manejaClick">
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
                            <el-card class="table-panel">
                                <span style="font-size: 20px; font-weight: bold;">
                                    Cita(s) bibliografica(s) asociada(s) a:
                                </span>
                                <br/>
                                <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                    {{ taxonRelacionado }}
                                </span>
                                <br/>
                                <br/>
                                <br/>
                                <div style="flex: 1; overflow-y: auto; border: 1px solid #dcdfe6; border-radius: 4px; margin-top: 10px;">
                                    <TablaFiltrable :container-class="'main-section'" :columnas="columnasDefinidasBiblio"
                                        v-model:datos = "bibliografiaRel" v-model:total-items="bibliografiaRel.length"
                                        :opciones-filtro = "opcionesFiltroNomenclatura"
                                        :origen = "true"
                                        :mostrarAcci = "true"
                                        :mostrarNuevo = "true"
                                        @eliminar-item = "manejarEliminarBiblio"
                                        @editar-item = "manejarEditarBiblio"
                                        @row-click = "manejaClickObs"
                                        @nuevo-item="crear">
                                        <template #expand-column>
                                            <el-table-column type="expand">
                                                <template #default="{ row }">
                                                    <div class="expand-content-detail">
                                                        <p><strong>IdBibliografia:</strong> {{ row.IdBibliografia }}</p>
                                                        <p><strong>Observaciones:</strong> {{ row.Observaciones }}</p>
                                                        <p><strong>OrdenCitaCompleta:</strong> {{ row.OrdenCitaCompleta }}</p>
                                                        <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                                                        <p><strong>FechaModificacion:</strong> {{ row.FechaModificacion }}</p>
                                                        <p><strong>IdOriginal:</strong> {{ row.IdOriginal }}</p>
                                                        <p><strong>Catalogo:</strong> {{ row.Catalogo }}</p>
                                                        <p><strong>AutorOriginal:</strong> {{ row.AutorOriginal }}</p>
                                                        <p><strong>usuario:</strong> {{ row.usuario }}</p>
                                                        <p><strong>Marca:</strong> {{ row.Marca }}</p>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                        </template>
                                    </TablaFiltrable>
                                </div>
                            </el-card>
                        </div>
                        <br/>
                        <el-card class="table-panel">
                            <div style="display: flex; flex-direction: column; height: 100%;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <span style="font-size: 18px; font-weight: bold;">
                                        Observaciones de la asociación:
                                    </span>
                                    <el-input  :rows="2"
                                            type="textarea"
                                            placeholder="Observaciones"
                                            :disabled = habObservaciones
                                            v-model="observacionRel">
                                    </el-input>
                                    <el-popconfirm confirm-button-text="Si" 
                                                    cancel-button-text="No" 
                                                    :icon="InfoFilled" 
                                                    icon-color="#E6A23C"
                                                    title="¿Realmente desea guardar los cambios?" 
                                                    @confirm="Guardar()">
                                        <template #reference>
                                            <!--el-tooltip class="item" effect="dark" content="Guardar" placement="bottom"-->
                                            <el-button circle type="warning" :disabled="habObservaciones">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-usb-drive" viewBox="0 0 16 16">
                                                    <path d="M6 .5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4H6v-4ZM7 1v1h1V1H7Zm2 0v1h1V1H9ZM6 5a1 1 0 0 0-1 1v8.5A1.5 1.5 0 0 0 6.5 16h4a1.5 1.5 0 0 0 1.5-1.5V6a1 1 0 0 0-1-1H6Zm0 1h5v8.5a.5.5 0 0 1-.5.5h-4a.5.5 0 0 1-.5-.5V6Z"/>
                                                </svg>
                                            </el-button>
                                            <!--/el-tooltip-->
                                        </template>
                                    </el-popconfirm>
                                </div>
                            </div>
                        </el-card>
                        <br/>
                        <el-card class="table-panel">
                            <span style="font-size: 18px; font-weight: bold;">
                                Cita bibliográfica:
                            </span>
                            <el-input  :rows="2"
                                       type="textarea"
                                       placeholder="Observaciones"
                                       :disable = true
                                       v-model="citaCompleta">
                            </el-input>
                        </el-card> 
                    </el-card>
                </el-row>
            </el-main>
        </el-container>
      </div>
    </el-card>

    <DialogForm v-model="dialogFormVisibleBiblio" :botCerrar="false" :pressEsc="false" :width="'83%'">
      <Bibliografia :isModal = "true"  @cerrarBiblio = "cerrarRelBiblio" />
    </DialogForm>

    <Teleport to="body">
      <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
        :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
        @close="cerrarNotificacion" />
    </Teleport>

  </div>
</template>
<script setup>
    import { ref, h, onMounted, watchEffect } from 'vue';
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrableImg.vue";
    import { ElLoading, ElMessageBox } from 'element-plus';
    import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
    import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
    import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
    import BotonSalir from '@/Components/Biotica/SalirButton.vue';
    import Bibliografia from '@/Pages/Socat/Bibliografia/CuerpoBibliografia.vue';
    import DialogForm from '@/Components/Biotica/DialogGeneral.vue';

    // Props del componente
    const props = defineProps({
        taxonAct: {
            type: Object
        },
        relaciones: {
            type: Object,
            required: true,
        }, 
        totalRegistros:{
            type: Number,
            required:true, 
            default: 0
        }
    });

    const totalRegNom = ref(0);
    const tipRelacion = ref("");
    const bibliografiaRel = ref([]);
    const taxonRelacionado = ref(""); 
    const citaCompleta = ref("");
    const observacionRel = ref("");
    const habObservaciones = ref(true);
    const notificacionVisible = ref(false);
    const notificacionTitulo = ref("");
    const notificacionMensaje = ref("");
    const notificacionTipo = ref("info");
    const notificacionDuracion = ref(5000);
    const relacionAct = ref([]);
    const idBibliografia = ref(0);
    const tablaRelaciones = ref([]);
    const dialogFormVisibleBiblio = ref(false);
    const habNuevaBiblio = ref(false);

    const emit = defineEmits(['cerrar']);

    const columnasDefinidas = ref([
        {
            prop: 'TipoRelacion', label: 'Tipo Relación', minWidth: '190', sortable: true,
            align: 'left', tipo: 'imagenTexto', filtrable: true
        },
        {
            prop: 'Nombrecompleto', label: 'Nombre Completo', minWidth: '230', sortable: true,
            align: 'left', tipo: 'imagenTexto', filtrable: true
        },
        {
            prop: 'Biblio', label: 'Ref.', minWidth: '90', sortable: false, align: 'center',
            tipo: 'imagenTexto', filtrable: false
        }
    ]);

    const cerrarNotificacion = () => {
        notificacionVisible.value = false;
    };

    const manejaClick = (row) => {   
        console.log("Esto es lo que tiene row: ", row);     
        tipRelacion.value = row.TipoRelacion.texto;
        taxonRelacionado.value = row.Nombrecompleto.texto; 
        bibliografiaRel.value = row.bibliografia;
        observacionRel.value = row.bibliografia.ObsRelBiblio;
        habObservaciones.value = true;
        relacionAct.value = row.TipoRelacion.relCompleta;
        habNuevaBiblio.value = true;
    }

    const manejaClickObs = (row) => {
        observacionRel.value = row.ObsRelBiblio;
        citaCompleta.value = row.CitaCompleta
    }

    const crear = () => {
        if(taxonRelacionado.value != ""){
            dialogFormVisibleBiblio.value = true;
        }
        else{
            mostrarNotificacionError('Aviso', `Se debe seleccionar al menos una relación.`, 'success');
        }
    };

    const cerrarRelBiblio = async (datos) =>{

        const loading = ElLoading.service({
            lock: true,
            text: "Loading",
            spinner: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="none" d="M0 0h200v200H0z"></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M70 95.5V112m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5L92 57.3M33.6 91 48 82.7m0-25.5L33.6 49m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;-120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M130 155.5V172m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5-14.3 8.3M93.6 151l14.3-8.3m0-25.4L93.6 109m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>`,
            backgroud: 'rgba(255,255,255,0.85)',
        });

        dialogFormVisibleBiblio.value = false;
        
        try{
        const response = await axios.post('/alta-RelacionesBiblio', { data: {relCompleta: relacionAct.value, 
                                                                             biblioRel: datos,
                                                                             taxAct: props.taxonAct.id}});

        tablaRelaciones.value = response.data;
        bibliografiaRel.value = [];
                                                                             
        } catch (error) {
            mostrarNotificacionError('Aviso', error.response.data.message, 'error');
            console.log("Error 422:", error.response.data.message);
        }
        
        loading.close();
    };

    const cerrarDialogo = () => {
        emit('cerrar');
    };

    const manejarEliminarBiblio = (row) => {
        idBibliografia.value = row.IdBibliografia;

         const procederConEliminacion = async () => {

            try {
                ElMessageBox.close();

                const response = await axios.delete('/elimina-RelacionesBiblio', { data: {relCompleta: relacionAct.value, 
                                                                                          idBiblio: idBibliografia.value,
                                                                                          taxAct: props.taxonAct.id}});
                
                tablaRelaciones.value = response.data;
                bibliografiaRel.value = [];

                mostrarNotificacion('Eliminación Exitosa', `La relación con la bibliografia fue eliminada correctamente.`, 'success');
            } catch (apiError) {
                mostrarNotificacionError('Aviso', `La relación no se puede eliminar.`, 'success');
            }
        };
        const cancelarEliminacion = () => {
            ElMessageBox.close();
        };
        
        const mensaje = "La relación con la bibliografia sera eliminada. ¿Realmente desea realizarlo?. Esta acción no se puede revertir"
        
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
    }

    const manejarEditarBiblio = (row) => {
        habObservaciones.value = false;
        idBibliografia.value = row.IdBibliografia;
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

    const mostrarNotificacionError = (titulo, mensaje, tipo = "info", duracion = 5000) => {
        notificacionTitulo.value = titulo;
        notificacionMensaje.value = mensaje;
        notificacionTipo.value = tipo;
        notificacionDuracion.value = 0;
        notificacionVisible.value = true;
    };

    const Guardar = async() => {
        const procederConActualizacion = async () => {
            try {
                ElMessageBox.close();
                const response = await axios.put('/actualiza-RelacionesBiblio', { data: {relCompleta: relacionAct.value, 
                                                                                        idBiblio: idBibliografia.value,
                                                                                        observacion: observacionRel.value,
                                                                                        taxAct: props.taxonAct.id}});

                tablaRelaciones.value = response.data;
                habObservaciones.value = true;

                mostrarNotificacion('Actualización Exitosa', `Las observaciones se actualizaron correctamente.`, 'success');
            } catch (apiError) {
                mostrarNotificacionError('Aviso', `Las observaciones no se pueden actualizar.`, 'success');
            }
        };

        const cancelarActualizacion = () => {
            ElMessageBox.close();
        };
        
        const mensaje = ` Las observaciones seran actualizadas. ¿Realmente desea relizar el cambio?. Esta acción no se puede revertir`;
        
        ElMessageBox({
            title: 'Confirmar actualización', showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
            message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
                h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
                h('div', { class: 'text-container' }, [h('p', null, mensaje)])
            ]),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, { onClick: cancelarActualizacion }),
                h(BotonAceptar, { onClick: procederConActualizacion }),
            ])
            ])
        }).catch(() => { });
    }

    const columnasDefinidasBiblio = ref([
        { prop: "Autor", label: "Autor", minWidth: 160, sortable: 'custom', filtrable: true, align: 'left' },
        { prop: "Anio", label: "Año", minWidth: 150, sortable: 'custom', filtrable: true, align: 'left' },
        { prop: "TituloPublicacion", label: "Titulo de la publicacion", minWidth: 250, sortable: 'custom', filtrable: true, align: 'left' },
        { prop: "TituloSubPublicacion", label: "Titulo de la subpublicacion", minWidth: 300, sortable: 'custom', filtrable: true, align: 'left' },
        { prop: "EditorialPaisPagina", label: "Editorial, Pais, Pagina", minWidth: 300, sortable: 'custom', filtrable: false, align: 'left' },
        { prop: "NumeroVolumenAnio", label: "Número, Volumen, Año", minWidth: 260, sortable: 'custom', filtrable: false, align: 'left' },
        { prop: "EditoresCompiladores", label: "Editores / Compiladores", minWidth: 250, sortable: 'custom', filtrable: false, align: 'left' },
        { prop: "ISBNISSN", label: "ISBN / ISSN", minWidth: 200, sortable: 'custom', filtrable: true, align: 'left' }
    ]);

    // Inicialización de datos
    onMounted(async () => {
        tablaRelaciones.value = props.relaciones;
    });

    watchEffect(() => {
        tablaRelaciones.value = props.relaciones;
        bibliografiaRel.value = [];
    });


</script>
<style scope>
    .titulo {
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0;
    }

    .main-content-card {
        width: 100%;
        height: 100%;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
        padding: 15px;
    }

    .dual-panel-container {
        display: flex;
        gap: 12px;
        width: 100%;
        height: 60vh;
    }

    .table-panel {
        flex: 1;
        min-width: 400px;
        display: flex;
        flex-direction: column;
    }
</style>