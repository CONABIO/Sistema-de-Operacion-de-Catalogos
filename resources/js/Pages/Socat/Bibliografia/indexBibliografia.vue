<script setup>
import { ref, h } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { ElMessageBox, ElTableColumn } from 'element-plus';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import FormBibliografia from '@/Pages/Socat/Bibliografia/FormBibliografia.vue';
import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import NuevoButton from '@/Components/Biotica/NuevoButton.vue';

const tablaRef = ref(null);
const localTableData = ref([]);
const total = ref(0);
const dialogFormVisible = ref(false);
const rowEdit = ref({});
const accBiblio = ref('');
const cita = ref('');

const columnasDefinidas = ref([
  { prop: "Autor", label: "Autor(es)", minWidth: 150, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "Anio", label: "Año(s)", minWidth: 80, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "TituloPublicacion", label: "Titulo de la publicación", minWidth: 250, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "TituloSubPublicacion", label: "Titulo de la subpublicación", minWidth: 200, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "EditorialPaisPagina", label: "Editorial, país, lugar, páginas", minWidth: 200, sortable: 'custom', filtrable: false, align: 'left' },
  { prop: "NumeroVolumenAnio", label: "Número, volumen, año, mes(es)", minWidth: 200, sortable: 'custom', filtrable: false, align: 'left' },
  { prop: "EditoresCompiladores", label: "Editor(es)/compílador(es)", minWidth: 200, sortable: 'custom', filtrable: false, align: 'left' },
  { prop: "ISBNISSN", label: "ISBN / ISSN", minWidth: 150, sortable: 'custom', filtrable: true, align: 'left' }
]);

const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);

const mostrarNotificacion = (titulo, mensaje, tipo = "info", duracion = 5000) => {
  notificacionTitulo.value = titulo;
  notificacionMensaje.value = mensaje;
  notificacionTipo.value = tipo;
  notificacionDuracion.value = duracion;
  notificacionVisible.value = true;
};
const cerrarNotificacion = () => {
  notificacionVisible.value = false;
};

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
const cerrarDialogo = () => {
  dialogFormVisible.value = false;
};

const handleRowClick = (row) => {
  citaCompleta(row);
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

const handleFormSubmited = (datosDelFormulario) => {
  cerrarDialogo();
  const procederConGuardado = async () => {
    ElMessageBox.close();
    try {
      if (accBiblio.value === 'crear') {
        await axios.post('/bibliografias', datosDelFormulario);
      } else {
        await axios.put(`/bibliografias/${rowEdit.value.IdBibliografia}`, datosDelFormulario);
      }
      if (tablaRef.value) {
        tablaRef.value.fetchData();
      }
      mostrarNotificacion("¡Operación Exitosa!", "Los cambios se guardaron correctamente.", "success");
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
  const mensaje = `¿Estás seguro de que deseas guardar los cambios para la bibliografía de "${datosDelFormulario.Autor || "nuevo registro"}"?`;
  ElMessageBox({
    title: 'Confirmar Guardado', showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
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
};

const borrarDatos = (idBibliografia) => {
  const procederConEliminacion = async () => {
    try {
      ElMessageBox.close();
      const itemAEliminar = localTableData.value.find(item => item.IdBibliografia === idBibliografia);
      const nombreItem = itemAEliminar ? `"${itemAEliminar.Autor}"` : 'el registro';
      await axios.delete(`/bibliografias/${idBibliografia}`);
      if (tablaRef.value) {
        tablaRef.value.fetchData();
      }
      mostrarNotificacion("¡Eliminación Exitosa!", `El registro ${nombreItem} fue eliminado.`, "success");
    } catch (apiError) {
      mostrarNotificacion("Error al Eliminar", apiError.response?.data?.message || 'Ocurrió un error.', "error");
    }
  };
  const cancelarEliminacion = () => { ElMessageBox.close(); };
  const itemAEliminar = localTableData.value.find(item => item.IdBibliografia === idBibliografia);
  const mensaje = `¿Está seguro de eliminar la bibliografía de "${itemAEliminar?.Autor || 'seleccionado'}"? Esta acción no se puede revertir.`;
  ElMessageBox({
    title: 'Confirmar eliminación', showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
    message: h('div', { class: 'custom-message-content' }, [
      h('div', { class: 'body-content' }, [
        h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
        h('div', { class: 'text-container' }, [h('p', null, mensaje)])
      ]),
      h('div', { class: 'footer-buttons' }, [
        h(BotonCancelar, { onClick: cancelarEliminacion }), h(BotonAceptar, { onClick: procederConEliminacion }),
      ])
    ])
  }).catch(() => { });
};
</script>

<template>
  <AppLayout title="Catálogo de referencias bibliograficas">
    <div class="app-container">
      <div class="page-title-header">
        <h1 class="page-main-title-class"> Catálogo de referencias bibliograficas</h1>
      </div>
      <div class="content-wrapper2">
        <div class="content-wrapper">
          <div class="table-wrapper">
            <TablaFiltrable ref="tablaRef" :columnas="columnasDefinidas" v-model:datos="localTableData"
              v-model:total-items="total" endpoint="/bibliografias-api" id-key="IdBibliografia" @editar-item="editar"
              @eliminar-item="borrarDatos" @nuevo-item="crear" @row-dblclick="handleRowClick">
              
              <template #header-actions>
                
                  <el-col :span="6" class="action-buttons-col">
                      <div class="action-group">
                        <NuevoButton @crear="crear" />
                        <el-tooltip class="item" effect="dark" content="Exportar" placement="bottom">
                          <el-button type="primary" circle>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                              class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                              <path fill-rule="evenodd"
                                d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                              <path fill-rule="evenodd"
                                d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                            </svg>
                          </el-button>
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" content="Filtros" placement="bottom">
                          <el-button type="primary" @click="filtroCatalogos" circle>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                              class="bi bi-sliders" viewBox="0 0 16 16">
                              <path fill-rule="evenodd"
                                d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z" />
                            </svg>
                          </el-button>
                        </el-tooltip>
                      </div>
                    </el-col>
              </template>
              <template #expand-column>
                <el-table-column type="expand">
                  <template #default="{ row }">
                    <div class="expand-content-detail">
                      <p><strong>IdOriginal:</strong> {{ row.IdOriginal }}</p>
                    </div>
                  </template>
                </el-table-column>
              </template>
            </TablaFiltrable>
            <br>
            <el-input type="textarea" :rows="2" v-model="cita" readonly disabled resize="none"
                  placeholder="Seleccione una fila para ver la cita completa..." />
          </div>
        </div>
      </div>
      <DialogForm v-model="dialogFormVisible" style="width:1250px" :bot-cerrar="true" :press-esc="false">
        <FormBibliografia v-if="dialogFormVisible" :accion="accBiblio" :biblio-edit="rowEdit" @cerrar="cerrarDialogo"
          @form-submited="handleFormSubmited" />
      </DialogForm>
    </div>
    <Teleport to="body">
      <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
        :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
        @close="cerrarNotificacion" />
    </Teleport>
  </AppLayout>
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

.bibliografia-header {
  display: flex;
  align-items: center; /* Centra verticalmente */
  gap: 15px; /* Espacio entre el textarea y los botones */
  width: 100%;
}

.cita-container {
  flex-grow: 1; /* Hace que el contenedor del textarea ocupe todo el espacio posible */
}

.action-group {
  display: flex;
  gap: 10px;
  flex-shrink: 0; /* Evita que el grupo de botones se encoja */
}



.page-title-header {
  background-color: #d9e1eb;
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  margin-bottom: 20px;
}

.page-title-header .page-main-title-class {
  color: rgb(31, 30, 30) !important;
  margin: 0 !important;
  font-size: 1.25rem !important;
}

.app-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f0f2f5;
  align-items: center;
  padding: 20px;
}

.content-wrapper2 {
  width: 100%;
  max-width: 1600px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
  padding: 25px;
}

.content-wrapper {
  width: 100%;
  max-width: 1600px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
  padding: 25px;
}

.page-main-title-class {
  font-size: 22px !important;
  font-weight: 600 !important;
  color: #303133 !important;
  margin-bottom: 20px !important;
  width: 1550px;
}

.page-main-title-class {
  font-size: 22px !important;
  font-weight: 600 !important;
  color: #303133 !important;
  margin-bottom: 20px !important;
}

.table-wrapper {
  width: 100%;
  margin-top: 0;
}

.expand-content-detail {
  padding: 10px 15px;
  background-color: #fdfdfd;
  font-size: 13px;
}

.card-header-title {
  font-weight: 500;
  color: #303133;
}

.action-group {
  display: flex;
  gap: 10px;
}
</style>