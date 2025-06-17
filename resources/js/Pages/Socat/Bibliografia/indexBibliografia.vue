<template>
  <AppLayout title="Bibliografía">
    
    <div class="app-container">
      <div class="page-title-header">
        <h1 class="page-main-title-class"> Catálogo de bibliografía</h1>
      </div>
      <div class="content-wrapper">

        <div class="content-wrapper2">
          <div class="table-wrapper">
            <el-card class="box-card">
              <template #header>
                <div class="card-header-content">
                  <el-row :gutter="20" align="bottom" class="filter-action-row">
                    <el-col :span="18">
                      <el-form label-position="top" class="filter-form" @submit.prevent>
                        <el-row :gutter="20">
                          <el-col :span="8">
                            <el-form-item label="Filtrar por">
                              <el-input v-model="filterText" placeholder="Escribe para buscar..."
                                @keyup.enter="querySearch" @clear="querySearch" clearable />
                            </el-form-item>
                          </el-col>
                          <el-col :span="16">
                            <el-form-item label="Referencia completa">
                              <el-input type="textarea" :rows="2" v-model="cita" readonly disabled resize="none" />
                            </el-form-item>
                          </el-col>
                        </el-row>
                      </el-form>
                    </el-col>

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
                  </el-row>

                  <div class="table-filters">
                    <el-checkbox v-model="checkAll" :indeterminate="isIndeterminate" @change="handleCheckAllChange">
                      Todos
                    </el-checkbox>
                    <el-checkbox-group v-model="opcionesElejidas" @change="updateIndeterminateState">
                      <el-checkbox v-for="opc in opciones" :key="opc" :label="opc">
                        {{ opc }}
                      </el-checkbox>
                    </el-checkbox-group>
                  </div>
                </div>
              </template>

              <div class="table-responsive">
                <el-table v-if="localTableData" :data="localTableData" border height="400" style="width: 100%"
                  @row-click="handleRowClick" :default-sort="{ prop: 'Autor', order: 'ascending' }"
                  :highlight-current-row="true" @sort-change="handleSortChange">
                  <el-table-column type="expand">
                    <template #default="scope">
                      <div class="bg-gray-50 p-4 rounded-md">
                        <p><strong>IdOriginal:</strong> {{ scope.row.IdOriginal }}</p>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column v-for="column in tableColumns" :key="column.label" :label="column.label"
                    :prop="column.prop" :column-key="column.prop" :min-width="column.minWidth"
                    :sortable="column.sortable" :align="column.align" :header-align="column.align"
                    :fixed="column.fixed || null" :formatter="column.formatter || null">
                  </el-table-column>
                  <el-table-column label="Acciones" align="center" width="120" fixed="right">
                    <template #default="scope">
                      <div class="flex justify-around">
                        <EditarButton @editar="editar(scope.row)" />
                        <EliminarButton @eliminar="borrarDatos(scope.row.IdBibliografia)" />
                      </div>
                    </template>
                  </el-table-column>

                </el-table>
                <div v-if="total > 0" class="pagination-container-wrapper">
                  <el-pagination @current-change="handlePageChange" :current-page="currentPage" :page-size="pageSize"
                    layout="prev, pager, next, total" :total="total" @size-change="handleSizeChange" background
                    class="main-pagination-style">
                  </el-pagination>
                </div>
              </div>
            </el-card>
          </div>
        </div>

        <DialogForm v-model="dialogFormVisible" style="width:1250px" :botCerrar="true" :pressEsc="false">
          <FormBibliografia v-if="dialogFormVisible" :accion="accBiblio" :biblio-edit="rowEdit" @cerrar="cerrarDialogo"
            @formSubmited="handleFormSubmited">
          </FormBibliografia>
        </DialogForm>
      </div>

      <Teleport to="body">
        <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
          :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
          @close="cerrarNotificacion" />
      </Teleport>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, h } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import NuevoButton from '@/Components/Biotica/NuevoButton.vue';
import EditarButton from '@/Components/Biotica/EditarButton.vue';
import EliminarButton from '@/Components/Biotica/EliminarButton.vue';
import FormBibliografia from '@/Pages/Socat/Bibliografia/FormBibliografia.vue';
import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
import { ElMessageBox, ElMessage } from 'element-plus';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';


const props = defineProps({
  bibliografiaData: {
    type: Object,
    required: true
  }
});

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

const handleSortChange = ({ prop, order }) => {
    sorting.value.column = prop;
    sorting.value.order = order === 'ascending' ? 'asc' : 'desc';
    if (!order) {
        sorting.value.column = null;
        sorting.value.order = null;
    }

    list(1); 
};

const cerrarDialogo = () => {
  dialogFormVisible.value = false;
};

const localTableData = ref(props.bibliografiaData.data);
const total = ref(props.bibliografiaData.totalItems);
const currentPage = ref(props.bibliografiaData.currentPage);
const lastPage = ref(props.bibliografiaData.lastPage);
const nextPageUrl = ref(props.bibliografiaData.nextPageUrl);
const prevPageUrl = ref(props.bibliografiaData.prevPageUrl);

const handlePageChange = (newPage) => {
  list(newPage);
};

const handleSizeChange = (newSize) => {
  pageSize.value = newSize;
  list(1);
};

const opcionesBuscar = ['Autor(es)', 'Año(s)', 'Titulo de la publicación', 'Cita bibliográfica',
  'Titulo de la subpublicación', 'ISBN/ISSN'];

const paginas = ref(1);
const checkAll = ref(false);
const opciones = ref(opcionesBuscar);
const isIndeterminate = ref(true);
const opcionesElejidas = ref([]);
const pageSize = ref(100);
const cita = ref('');
const idBiblio = ref('');
const accBiblio = ref('');
const filterText = ref('');
const dialogFormVisible = ref(false);
const dialogFormVisibleCat = ref(false);
const filgrupoScat = ref([]);
const rowEdit = ref({});
const sorting = ref({ column: null, order: null });
const tableColumns = ref([
  { prop: "Autor", label: "Autor(es)", minWidth: 150, sortable: 'custom', hidden: false, align: 'left', fixed: true },
  { prop: "Anio", label: "Año(s)", minWidth: 80, sortable: 'custom', hidden: false, align: 'left' },
  { prop: "TituloPublicacion", label: "Titulo de la publicación", minWidth: 250, sortable: 'custom', hidden: false, align: 'left' },
  { prop: "TituloSubPublicacion", label: "Titulo de la subpublicación", minWidth: 200, sortable: 'custom', hidden: false, align: 'left' },
  { prop: "EditorialPaisPagina", label: "Editorial, país, lugar, páginas", minWidth: 200, sortable: 'custom', hidden: false, align: 'left' },
  { prop: "NumeroVolumenAnio", label: "Número, volumen, año, mes(es)", minWidth: 200, sortable: 'custom', hidden: false, align: 'left' },
  { prop: "EditoresCompiladores", label: "Editor(es)/compílador(es)", minWidth: 200, sortable: 'custom', hidden: false, align: 'left' },
  { prop: "ISBNISSN", label: "ISBN / ISSN", minWidth: 150, sortable: 'custom', hidden: false, align: 'left' }
]);

const handleCheckAllChange = (val) => {
  opcionesElejidas.value = val ? [...opciones] : [];
  updateIndeterminateState();
};

const handleCheckboxChange = (opc, event) => {
  if (event.target.checked) {
    opcionesElejidas.value.push(opc);
  } else {
    opcionesElejidas.value = opcionesElejidas.value.filter(item => item !== opc);
  }
  updateIndeterminateState();
};

const updateIndeterminateState = () => {
  const checkedCount = opcionesElejidas.value.length;
  checkAll.value = checkedCount === opciones.value.length;
  isIndeterminate.value = checkedCount > 0 && checkedCount < opciones.value.length;
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

const handleRowClick = (row) => {
  citaCompleta(row);
  idBiblio.value = row.IdBibliografia;
  cargaGrupos();
};

const citaCompleta = (row) => {
  let orden = '';
  let citaComp = '';
  const campos = ['', 'Autor', 'Anio', 'TituloSubPublicacion', 'TituloPublicacion', 'EditoresCompiladores', 'NumeroVolumenAnio', 'ISBNISSN'];
  
  
  if (row && row.OrdenCitaCompleta) { 
    orden = row.OrdenCitaCompleta;
  } else {
    
    orden = '1243765';
  }

  const myArray = orden.split("");
  for (let i = 0; i < myArray.length; i++) {
    const campoActual = campos[myArray[i]];
    if (row[campoActual]) { 
        if (citaComp != '') {
            citaComp += ' ' + row[campoActual];
        } else {
            citaComp = row[campoActual];
        }
    }
  }
  cita.value = citaComp;
};

const cargaGrupos = async () => {
  try {
  } catch (error) {
    console.error('Error al cargar grupos:', error);
  }
};

const borrarGrp = (index, row) => {
  cargaGrupos();
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
      await list();
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
  const cancelarGuardado = () => { ElMessageBox.close();};
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
      await list();
      mostrarNotificacion("¡Eliminación Exitosa!", `El registro ${nombreItem} fue eliminado.`, "success");
    } catch (apiError) {
      mostrarNotificacion("Error al Eliminar", apiError.response?.data?.message || 'Ocurrió un error.', "error");
    }
  };
  const cancelarEliminacion = () => { ElMessageBox.close();  };
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

const querySearch = () => {
  list(1);
};

const list = async (page = 1) => {
  currentPage.value = page;
  try {
    const params = {
      buscado: filterText.value,
      page: currentPage.value,
      perPage: pageSize.value,
      sortBy: sorting.value.column,
      sortOrder: sorting.value.order,
      ...(opcionesElejidas.value.length > 0 && { opciones: opcionesElejidas.value })
    };
    const response = await axios.get('/bibliografias-api', { params });
    localTableData.value = response.data?.bibliografiaData?.data || [];
    total.value = response.data.bibliografiaData.totalItems;
    paginas.value = response.data.bibliografiaData.lastPage;
    nextPageUrl.value = response.data.bibliografiaData.nextPageUrl;
    prevPageUrl.value = response.data.bibliografiaData.prevPageUrl;

  } catch (error) {
    console.error('Error al cargar bibliografías:', error);
    localTableData.value = [];
    total.value = 0;
  }
};

const filtroCatalogos = () => {
  if (idBiblio.value != '') {
    dialogFormVisibleCat.value = true;
  } else {
    ElMessageBox.alert('Primero se debe seleccionar una bibliografía', 'SOCAT', { type: 'warning' });
  }
};

onMounted(() => {
  list();
});
</script>


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
.page-title-header {
  background-color: #d9e1eb;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  margin-bottom: 20px;
}
.page-title-header .page-main-title-class {
  color: #1f1e1e;
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
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
:deep(.el-card__header) {
    padding: 20px 24px !important;
    border-bottom: 1px solid #e4e7ed !important;
}
.card-header-content {
    width: 100%;
}
.filter-form :deep(.el-form-item) {
    margin-bottom: 0; 
}
.filter-form :deep(.el-form-item__label) {
    font-size: 0.85em;
    padding-bottom: 2px;
}
.action-buttons-col {
    display: flex;
    justify-content: flex-end;
    align-items: flex-end;
}
.action-group {
    display: flex;
    gap: 10px;
}
.table-filters {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #f0f2f5;
}

.table-responsive {
  overflow-x: auto;
}
.pagination-container-wrapper {
  display: flex;
  justify-content: flex-start;
  padding-top: 20px;
  margin-top: 0;
}

.page-main-title-class {
  font-size: 22px !important;
  font-weight: 600 !important;
  color: #303133 !important;
  margin-bottom: 5px !important;
  width: 1550px;
}

</style>