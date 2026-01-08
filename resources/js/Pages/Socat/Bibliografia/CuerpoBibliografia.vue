<script setup>
import { ref, h, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { ElMessage, ElMessageBox, ElTableColumn, ElButton } from 'element-plus';
import AppLayout from '@/Layouts/AppLayout.vue';
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import axios from 'axios';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import FormBibliografia from '@/Pages/Socat/Bibliografia/FormBibliografia.vue';
import DialogGeneral from "@/Components/Biotica/DialogGeneral.vue";
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import NuevoButton from '@/Components/Biotica/NuevoButton.vue';
import EditarButton from '@/Components/Biotica/EditarButton.vue';
import EliminarButton from '@/Components/Biotica/EliminarButton.vue';
import GuardarButton from '@/Components/Biotica/GuardarButton.vue';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';

const tablaRef = ref(null);
const localTableData = ref([]);
const total = ref(0);
const dialogFormVisible = ref(false);
const rowEdit = ref({});
const accBiblio = ref('');
const cita = ref('');
const datosGrupos = ref([]);
const loadingGrupos = ref(false);
const esModalGruposVisible = ref(false);
const selectedBibliografia = ref(null);
const datosObjetos = ref([]);

const biblioRelacion = ref([]);

const loadingObjetos = ref(false);
const esModalObjetosVisible = ref(false);

const esModalEditarGrupoVisible = ref(false);
const grupoParaEditar = ref({
  IdBibliografia: null,
  IdGrupoSCAT: null,
  grupo: '',
  observaciones: ''
});

const emit = defineEmits(['cerrar',
  'formSubmited',
  'cerrarBiblio']);

const columnasDefinidas = ref([
  { prop: "Autor", label: "Autor", minWidth: 160, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "Anio", label: "Año", minWidth: 150, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "TituloPublicacion", label: "Titulo de la publicacion", minWidth: 250, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "TituloSubPublicacion", label: "Titulo de la subpublicacion", minWidth: 300, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "EditorialPaisPagina", label: "Editorial, Pais, Pagina", minWidth: 300, sortable: 'custom', filtrable: false, align: 'left' },
  { prop: "NumeroVolumenAnio", label: "Número, Volumen, Año", minWidth: 260, sortable: 'custom', filtrable: false, align: 'left' },
  { prop: "EditoresCompiladores", label: "Editores / Compiladores", minWidth: 250, sortable: 'custom', filtrable: false, align: 'left' },
  { prop: "ISBNISSN", label: "ISBN / ISSN", minWidth: 200, sortable: 'custom', filtrable: true, align: 'left' }
]);

const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);

const props = defineProps({
  isModal: {
    type: Boolean,
    default: false
  },
  traspaso: {
    type: Boolean,
    default: false
  }
});


const abrirModalEditar = (filaGrupo) => {
  grupoParaEditar.value = { ...filaGrupo };
  esModalEditarGrupoVisible.value = true;
};

const guardarObservaciones = async () => {
  if (!grupoParaEditar.value?.IdBibliografia || !grupoParaEditar.value?.IdGrupoSCAT) {
    mostrarNotificacion('Error', 'Faltan datos para actualizar el grupo.', 'error');
    return;
  }

  try {
    await axios.put(route('bibliografias.asociarGrupo.actualizar'), {
      IdBibliografia: grupoParaEditar.value.IdBibliografia,
      IdGrupoSCAT: grupoParaEditar.value.IdGrupoSCAT,
      Observaciones: grupoParaEditar.value.observaciones
    });

    mostrarNotificacion('Éxito', 'Observaciones actualizadas.', 'success');
    esModalEditarGrupoVisible.value = false;
    handleRowClick(selectedBibliografia.value);

  } catch (error) {
    const mensajeError = error.response?.data?.message || 'No se pudieron guardar los cambios.';
    mostrarNotificacion('Error', mensajeError, 'error');
    console.error("Error al guardar observaciones:", error.response);
  }
};

const confirmarEliminacionGrupo = (filaGrupo) => {

  console.log("Este es el grupo selccionado:", filaGrupo);
  if (!filaGrupo?.IdBibliografia || !filaGrupo?.IdGrupoSCAT) {
    mostrarNotificacion('Error', 'Faltan datos para eliminar la asociación.', 'error');
    return;
  }

  ElMessageBox.confirm(
    `¿Estás seguro de que quieres desasociar el grupo "${filaGrupo.grupo}" de esta bibliografía?`,
    'Confirmar eliminación',
    {
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      type: 'warning',
    }
  ).then(async () => {
    try {
      await axios.delete(route('bibliografias.asociarGrupo.eliminar'), {
        data: {
          IdBibliografia: filaGrupo.IdBibliografia,
          IdGrupoSCAT: filaGrupo.IdGrupoSCAT
        }
      });

      mostrarNotificacion('Éxito', 'Grupo desasociado correctamente.', 'success');
      handleRowClick(selectedBibliografia.value);
    } catch (error) {
      const mensajeError = error.response?.data?.message || 'No se pudo desasociar el grupo.';
      mostrarNotificacion('Error', mensajeError, 'error');
      console.error("Error al eliminar asociación:", error.response);
    }
  }).catch(() => {
    ElMessage({ type: 'info', message: 'Eliminación cancelada' });
  });
};





const mostrarNotificacion = (titulo, mensaje, tipo = "info", duracion = 5000) => {
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


const cerrarNotificacion = () => { notificacionVisible.value = false; };
const crear = () => {
  accBiblio.value = 'crear';
  rowEdit.value = {};
  dialogFormVisible.value = true;
};
const editar = (row) => {
  accBiblio.value = 'editar';
  rowEdit.value = { ...row };
  dialogFormVisible.value = true;
};
const cerrarDialogo = () => { dialogFormVisible.value = false; };

const cerrarDialogo2 = () => {
  emit('cerrar');
};

const handleRowClick = async (row) => {
  selectedBibliografia.value = row;
  citaCompleta(row);
  const idBibliografia = row.IdBibliografia;
  if (!idBibliografia) return;

  datosGrupos.value = [];
  loadingGrupos.value = true;
  try {
    const responseGrupos = await axios.get(`/api/bibliografias/${idBibliografia}/grupos-taxonomicos`);
    datosGrupos.value = responseGrupos.data;
  } catch (error) {
    console.error("Error al cargar los grupos taxonómicos:", error);
    mostrarNotificacion("Error", "No se pudieron cargar los grupos taxonómicos asociados.", "error");
  } finally {
    loadingGrupos.value = false;
  }

  datosObjetos.value = [];
  loadingObjetos.value = true;
  try {
    const responseObjetos = await axios.get(route('bibliografias.objetosExternos.get', { bibliografiaId: idBibliografia }));
    datosObjetos.value = responseObjetos.data;
  } catch (error) {
    console.error("Error al cargar los objetos externos:", error);
    mostrarNotificacion("Error", "No se pudieron cargar los objetos externos asociados.", "error");
  } finally {
    loadingObjetos.value = false;
  }
};


const citaCompleta = (row) => {
  let orden = row.OrdenCitaCompleta || '1243765';
  let citaComp = '';
  const campos = ['', 'Autor', 'Anio', 'TituloSubPublicacion', 'TituloPublicacion', 'EditoresCompiladores', 'NumeroVolumenAnio', 'ISBNISSN'];
  const myArray = orden.split("");
  for (let i = 0; i < myArray.length; i++) {
    const campoActual = campos[myArray[i]];
    if (row[campoActual]) {
      citaComp += (citaComp ? ' ' : '') + row[campoActual];
    }
  }
  cita.value = citaComp;
};

const agregarGrupo = () => {
  if (!selectedBibliografia.value) {
    mostrarNotificacion("Advertencia", "Por favor, seleccione una bibliografía de la tabla principal primero.", "warning");
    return;
  }
  esModalGruposVisible.value = true;
};

const cerrarModalGrupos = () => {
  esModalGruposVisible.value = false;
  if (selectedBibliografia.value) {
    handleRowClick(selectedBibliografia.value);
  }
};

const traspasaBiblio = () => {

  const id = selectedBibliografia.value.IdBibliografia;

  if (!biblioRelacion.value.includes(id)) {
    biblioRelacion.value.push(id);
    mostrarNotificacion("Bibliografia", "Se asignara la bibliografia seleccionada.", "info");
  }

};

const cerrarModal = () => {
  emit('cerrarBiblio', biblioRelacion.value);
  biblioRelacion.value = [];
}

const handleFormSubmited = (datosDelFormulario) => {
  cerrarDialogo();
  const procederConGuardado = async () => {
    ElMessageBox.close();
    try {
      if (accBiblio.value === 'crear') {
        await axios.post('/bibliografias', datosDelFormulario);
        mostrarNotificacion("Ingreso", "La información ha sido ingresada correctamente.", "success");
      } else {
        await axios.put(`/bibliografias/${rowEdit.value.IdBibliografia}`, datosDelFormulario);
        mostrarNotificacion("Ingreso", "La información ha sido modificada correctamente.", "success");
      }
      tablaRef.value?.fetchData();
    } catch (error) {
      if (error.response && error.response.status === 422) {
        let errorMsg = "Error de validación:<ul>" + Object.values(error.response.data.errors).flat().map(e => `<li>${e}</li>`).join("") + "</ul>";
        mostrarNotificacion("Error de Validación", errorMsg, "error", 0);
      } else {
        mostrarNotificacion("Error del Servidor", error.response?.data?.message || "Ocurrió un error.", "error");
      }
    }
  };
  const cancelarGuardado = () => { ElMessageBox.close(); };
  if (accBiblio.value === 'crear') {
    procederConGuardado();
  } else {
    const mensaje = `¿Estás seguro de que deseas guardar los cambios para la bibliografía de "${datosDelFormulario.Autor || "nuevo registro"}"?`;
    ElMessageBox({
      title: 'Confirmar modificación', showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
      message: h('div', { class: 'custom-message-content' }, [
        h('div', { class: 'body-content' }, [
          h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
          h('div', { class: 'text-container' }, [h('p', null, mensaje)])
        ]),
        h('div', { class: 'footer-buttons' }, [
          h(BotonCancelar, { onClick: cancelarGuardado }), h(BotonAceptar, { onClick: procederConGuardado }),
        ])
      ])
    }).catch(() => { });
  }
};


const borrarDatos = (idBibliografia) => {
  // Buscamos el item para el mensaje de confirmación
  const itemAEliminar = localTableData.value.find(item => item.IdBibliografia == idBibliografia);
  
  const procederConEliminacion = async () => {
    try {
      ElMessageBox.close();
      await axios.delete(route('bibliografias.destroy', { bibliografia: idBibliografia }));
      selectedBibliografia.value = null;
      cita.value = '';
      datosGrupos.value = [];
      datosObjetos.value = [];
      if (tablaRef.value) {
        await tablaRef.value.fetchData();
      }
      mostrarNotificacion("Eliminación exitosa", "El registro fue eliminado correctamente.", "success");
    } catch (apiError) {
      console.error(apiError);
      mostrarNotificacion("Error al Eliminar", apiError.response?.data?.message || 'Ocurrió un error.', "error");
    }
  };

  const cancelarEliminacion = () => { ElMessageBox.close(); };
  
  const mensaje = `¿Está seguro de eliminar la bibliografía de "${itemAEliminar?.Autor || 'este registro'}"? Esta acción no se puede revertir.`;
  
  ElMessageBox({
    title: 'Confirmar eliminación', 
    showConfirmButton: false, 
    showCancelButton: false, 
    customClass: 'message-box-diseno-limpio',
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
};


onMounted(() => {
  const handleMessageFromIframe = (event) => {
    if (event.data && event.data.type === 'grupoTaxonomicoSeleccionado') {
      const grupoSeleccionado = event.data.payload;
      if (!selectedBibliografia.value) {
        mostrarNotificacion("Error", "No hay una bibliografía seleccionada.", "error");
        return;
      }
      axios.post(route('bibliografias.asociarGrupo'), {
        IdBibliografia: selectedBibliografia.value.IdBibliografia,
        IdGrupoSCAT: grupoSeleccionado.id,
      })
        .then((response) => {
          mostrarNotificacion("Éxito", response.data.message, "success");
          esModalGruposVisible.value = false;
          const nuevoGrupo = response.data.grupo;
          if (nuevoGrupo && !datosGrupos.value.some(g => g.grupo === nuevoGrupo.grupo)) {
            datosGrupos.value.push(nuevoGrupo);
          } else {
            handleRowClick(selectedBibliografia.value);
          }

        })
        .catch(error => {
          const errorMessage = error.response?.data?.message || 'No se pudo asociar el grupo.';
          mostrarNotificacionError("Aviso", errorMessage, "error");
        });
    }
     if (event.data && event.data.type === 'cerrarModal') {
        esModalGruposVisible.value = false;
    }
  };
  window.addEventListener('message', handleMessageFromIframe);
  onUnmounted(() => {
    window.removeEventListener('message', handleMessageFromIframe);
  });
});




</script>

<template>
  <LayoutCuerpo :usar-app-layout="false" tituloPag="Bibliografía" tituloArea="Catálogo de referencias bibliográficas">

    <div class="layout-dos-columnas">
      <div class="columna-principal">
        <TablaFiltrable class="flex-grow tabla-bibliografia-chica" ref="tablaRef" :columnas="columnasDefinidas"
          v-model:datos="localTableData" v-model:total-items="total" endpoint="/bibliografias-api"
          id-key="IdBibliografia" @editar-item="editar" @eliminar-item="borrarDatos" @nuevo-item="crear"
          @row-click="handleRowClick" @traspasaBiblio="traspasaBiblio" @cerrar="cerrarModal" :botCerrar="props.isModal"
          :highlight-current-row="true" :mostrarTraspaso="props.traspaso">
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
        <div class="cita-container">
          <el-input type="textarea" :rows="2" v-model="cita" readonly disabled resize="none"
            placeholder="Haga clic en una fila para ver la cita completa..." />
        </div>
      </div>
      <div class="columna-lateral">
        <div class="widget-card" v-loading="loadingGrupos">
          <div class="widget-header">
            <h3>Grupo taxonómico</h3>
            <NuevoButton @crear="agregarGrupo" />
          </div>
          <div class="widget-table-container">
            <el-table :data="datosGrupos" border style="width: 100%"
              :empty-text="!selectedBibliografia ? 'Seleccione una bibliografía' : 'Sin grupos asociados'">
              <el-table-column prop="grupo" label="Grupo taxonómico" />
              <el-table-column prop="observaciones" label="Observaciones" />
              <el-table-column label="Acciones" width="100" align="center">
                <template #default="{ row }">
                  <div class="action-buttons-container">
                    <EditarButton @editar="abrirModalEditar(row)" />
                    <EliminarButton @eliminar="confirmarEliminacionGrupo(row)" />
                  </div>
                </template>
              </el-table-column>

            </el-table>
          </div>
        </div>
        <div class="widget-card">
          <div class="widget-header">
            <h3>Objeto externo</h3>
          </div>
          <div class="widget-table-container">
            <el-table :data="datosObjetos" border style="width: 100%" empty-text="Sin Datos">
              <el-table-column prop="objeto" label="Objeto externo" />
              <el-table-column prop="observaciones" label="Observaciones" />
              <el-table-column label="Acciones" width="100" align="center">
                <template #default="{ }">
                  <div class="action-buttons-container">
                    <EditarButton />
                    <EliminarButton />
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </div>
        </div>
      </div>
    </div>
  </LayoutCuerpo>

  <Teleport to="body">
    <DialogGeneral v-model="dialogFormVisible" style="width:1250px" :bot-cerrar="true" :pressEsc="true">
      <FormBibliografia v-if="dialogFormVisible" :accion="accBiblio" :biblio-edit="rowEdit" @cerrar="cerrarDialogo"
        @form-submited="handleFormSubmited" />
    </DialogGeneral>
    <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
      :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
      @close="cerrarNotificacion" />
    <DialogGeneral v-model="esModalGruposVisible" :bot-cerrar="true" :pressEsc="true" width="100%"
      @close="cerrarModalGrupos" :draggable="true">

      <div class="dialog-body-iframe-container" style="padding: 0; border: none; display: flex; flex-direction: column;">
        <iframe v-if="esModalGruposVisible" :src="route('grupoTaxonomico.index', { modal: true })" class="iframe-full"
          frameborder="0">
        </iframe>
      </div>
    </DialogGeneral>
    <DialogGeneral v-model="esModalEditarGrupoVisible" title="Editar Observaciones" width="500px" :pressEsc="false" :bot-cerrar="true">
      <div v-if="grupoParaEditar" class="edit-observaciones-modal-content">
        <div class="form-actions" style="margin-top: 10px;">
          <GuardarButton @click="guardarObservaciones" />
          <BotonSalir accion="cerrar" @salir="cerrarDialogo2" />
        </div>

        <div class="info-grupo">
          <span class="info-label">Grupo:</span>
          <span class="info-valor" style="color: red; font-weight: bold;">{{ grupoParaEditar.grupo }}</span>
        </div>

        <el-form label-position="top" class="form-observaciones">
          <el-form-item label="Observaciones">
            <el-input type="textarea" v-model="grupoParaEditar.observaciones" :rows="4"
              placeholder="Añade tus observaciones aquí" />
          </el-form-item>
        </el-form>
      </div>
    </DialogGeneral>

  </Teleport>
</template>

<style>
.message-box-diseno-limpio .el-message-box__header {
  border-bottom: none;
}

.message-box-diseno-limpio .el-message-box__content {
  padding: 10px 20px 20px 20px;
}

.custom-message-content {
  display: flex;
  flex-direction: column;
}

.body-content {
  display: flex;
  align-items: center;
  gap: 15px;
}

.text-container p {
  margin: 0;
  line-height: 1.5;
  color: #606266;
}

.custom-warning-icon-container {
  flex-shrink: 0;
}

.custom-warning-circle {
  width: 30px;
  height: 30px;
  border-radius: 90%;
  background-color: #F56C6C;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: bold;
  line-height: 1;
}

.footer-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 35px;
}
</style>

<style scoped>
.cita-container {
  flex-shrink: 0;
  margin-top: 20px;
}

.expand-content-detail {
  padding: 10px 15px;
  background-color: #fdfdfd;
  font-size: 13px;
}

.tabla-bibliografia-chica :deep(.el-table__body-wrapper) {
  max-height: 500px;
}

.layout-dos-columnas {
  display: flex;
  flex-direction: row;
  gap: 24px;
}

.columna-principal {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  min-width: 0;
}

.columna-lateral {
  display: flex;
  flex-direction: column;
  gap: 24px;
  flex-basis: 400px;
  flex-shrink: 0;
}

.widget-card {
  background-color: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.widget-table-container {
  max-height: 250px;
  overflow-y: auto;
}

.widget-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  border-bottom: 1px solid #f0f0f0;
  padding-bottom: 12px;
}

.widget-header h3 {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
}

.widget-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 16px;
}

.action-buttons-container {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

:deep(.el-dialog .dialog-body-iframe-container) {
  height: 700px;
}

:deep(.el-dialog__body) {
  padding: 0 !important;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.dialog-body-iframe-container {
  flex-grow: 1;
  height: 75vh;
  display: flex;
}

.iframe-full {
  width: 100%;
  border: none;
  flex-grow: 1;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 4px;
  margin-right: 35px;
  gap: 10px;
}


.edit-observaciones-modal-content {
  padding: 10px 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-grupo {
  background-color: #f5f7fa;
  padding: 10px 15px;
  border-radius: 6px;
  border: 1px solid #e9e9eb;
  font-size: 14px;
}

.info-label {
  font-weight: 600;
  color: #606266;
  margin-right: 8px;
}

.info-valor {
  color: #303133;
}

.form-observaciones .el-form-item {
  margin-bottom: 0;
}

.form-observaciones :deep(.el-form-item__label) {
  font-weight: 600;
  color: #606266;
  padding-bottom: 5px !important;
}

.dialog-footer-custom {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 10px 20px;
}


.tabla-bibliografia-chica {
  --el-table-current-row-bg-color: #ddf6dd !important;
  --el-table-row-hover-bg-color: #cbf0cb !important;
}

:deep(.el-table__body tr.current-row > td.el-table__cell) {
  background-color: #ddf6dd !important;
}
</style>