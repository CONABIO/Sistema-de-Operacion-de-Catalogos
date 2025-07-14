<script setup>
import { ref, h } from 'vue';
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
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
  { prop: "Autor", label: "Autor", minWidth: 160, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "Anio", label: "Anio", minWidth: 150, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "TituloPublicacion", label: "TituloPublicacion", minWidth: 250, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "TituloSubPublicacion", label: "TituloSubPublicacion", minWidth: 300, sortable: 'custom', filtrable: true, align: 'left' },
  { prop: "EditorialPaisPagina", label: "EditorialPaisPagina", minWidth: 300, sortable: 'custom', filtrable: false, align: 'left' },
  { prop: "NumeroVolumenAnio", label: "NumeroVolumenAnio", minWidth: 260, sortable: 'custom', filtrable: false, align: 'left' },
  { prop: "EditoresCompiladores", label: "EditoresCompiladores", minWidth: 250, sortable: 'custom', filtrable: false, align: 'left' },
  { prop: "ISBNISSN", label: "ISBNISSN", minWidth: 200, sortable: 'custom', filtrable: true, align: 'left' }
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
  console.log('¡CLIC RECIBIDO EN BIBLIOGRAFIA!', row);
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
        mostrarNotificacion("Ingreso", "La información ha sido ingresada correctamente.", "success");
      } else {
        await axios.put(`/bibliografias/${rowEdit.value.IdBibliografia}`, datosDelFormulario);
        mostrarNotificacion("Ingreso", "La información ha sido modificada correctamente.", "success");
      }
      if (tablaRef.value) {
        tablaRef.value.fetchData();
      }
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
  if (datosDelFormulario.accionOriginal === 'crear') {
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
  <LayoutCuerpo :usar-app-layout="true" tituloPag="Bibliografía" tituloArea="Catálogo de referencias bibliográficas">

    <div class="layout-dos-columnas">

      <div class="columna-principal">

        <TablaFiltrable class="flex-grow tabla-bibliografia-chica" ref="tablaRef" :columnas="columnasDefinidas"
          v-model:datos="localTableData" v-model:total-items="total" endpoint="/bibliografias-api"
          id-key="IdBibliografia" @editar-item="editar" @eliminar-item="borrarDatos" @nuevo-item="crear"
          @row-click="handleRowClick">
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

    <div class="widget-card">
        
        <div class="widget-table-container">
            <el-table :data="datosGrupos" border style="width: 100%">
                <el-table-column prop="grupo" label="Grupo taxonómico" />
                <el-table-column prop="observaciones" label="Observaciones" />
                <el-table-column label="Acciones" width="100" align="center">
                    <template #default="scope">
                        <div class="action-buttons">
                            <button class="widget-button-edit"></button>
                            <button class="widget-button-delete"></button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </div>

    <div class="widget-card">
        
        <div class="widget-table-container">
            <el-table :data="datosObjetos" border style="width: 100%">
                <el-table-column prop="objeto" label="Objeto externo" />
                <el-table-column prop="observaciones" label="Observaciones" />
                <el-table-column label="Acciones" width="100" align="center">
                    <template #default="scope">
                        <div class="action-buttons">
                            <button class="widget-button-edit"></button>
                            <button class="widget-button-delete"></button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </div>

</div>
    </div>

    <DialogForm v-model="dialogFormVisible" style="width:1250px" :bot-cerrar="true" :press-esc="true">
      <FormBibliografia v-if="dialogFormVisible" :accion="accBiblio" :biblio-edit="rowEdit" @cerrar="cerrarDialogo"
        @form-submited="handleFormSubmited" />
    </DialogForm>
    <Teleport to="body">
      <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
        :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
        @close="cerrarNotificacion" />
    </Teleport>
  </LayoutCuerpo>
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
  max-height: 480px;
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
  height: 370px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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
</style>