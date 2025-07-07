<script setup>
import { ref, onMounted, watch, nextTick, h } from "vue";
import axios from "axios";
import { ElMessage, ElMessageBox } from "element-plus";
import NuevoButton from "@/Components/Biotica/NuevoButton.vue";
import EditarButton from "@/Components/Biotica/EditarButton.vue";
import EliminarButton from "@/Components/Biotica/EliminarButton.vue";
import FiltrarPor from "@/Components/Biotica/FiltrarPorInput.vue";
import FormAutorTaxon from "@/Pages/Socat/Autores/FormAutorTaxon.vue";
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import { DArrowRight, ArrowUp, ArrowDown, Switch } from '@element-plus/icons-vue';
const emit = defineEmits(['traspasoAutores']);
const props = defineProps({
  autorRel: {
    type: Object,
    required: false,
  },
  nombre: {
    type: Boolean,
    required: false,
    default: false
  }
});
const autoresRel = ref([]);
const activeNames = ref(['1']);
const autoridadTax = ref('');
const allowedChars = new Set([
  '(', ')', '[', ']', '&', 'e', 'x', 'i', 'n', 'o', 'c', 's', 'u',
  'y', 'u', 't', 'a', 'f', 'p', '1', '2', '3', '4', '5', '6', '7',
  '8', '9', '0', ',', '.', '-', ':', ';', ' '
]);
const currentData = ref([]);
const currentPage = ref(1);
const totalItems = ref(0);
const itemsPerPage = ref(100);
const filtro = ref("");
const sorting = ref({ prop: null, order: null });
const modalFormVisible = ref(false);
const itemEditado = ref(null);
const accionModalForm = ref("crear");
const guardandoDatosServer = ref(false);
const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);
const traspasaDatos = () => {
  if (autoridadTax.value === '') {
    ElMessageBox.alert('Se debe de armar la autoridad taxonomica para continuar.', 'Error', { type: 'error' });
  } else {
    emit('traspasoAutores', autoresRel, autoridadTax);
  }
}
const agregarAutor = async (row) => {
  autoridadTax.value = "";
  if (props.nombre) {
    const existe = autoresRel.value.some(autor => autor.IdAutorTaxon === row.IdAutorTaxon);
    if (!existe) {
      autoresRel.value.push({
        IdAutorTaxon: row.IdAutorTaxon,
        NombreAutoridad: row.NombreAutoridad,
        CadInicio: '',
        CadFinal: ''
      });
    }
  }
}
const armaAutoridad = () => {
  let autorCompleto = "";
  autoresRel.value.forEach((autor, index) => {
    const ultimo = index === autoresRel.value.length - 1;
    autorCompleto += (autor.CadInicio || '') + autor.NombreAutoridad;
    if (ultimo) {
      autorCompleto += autor.CadFinal;
      if (autor.CadFinal === '') {
        ElMessageBox.alert('El ultimo registro de autoridad no contiene fecha', 'Alerta', { type: 'warning' });
      }
    } else {
      autorCompleto += (autor.CadFinal || '') + " ";
    }
  });
  autoridadTax.value = autorCompleto;
}
const subirRow = (index) => {
  autoridadTax.value = "";
  if (index > 0) {
    const item = autoresRel.value[index];
    autoresRel.value.splice(index, 1);
    autoresRel.value.splice(index - 1, 0, item);
  }
}
const bajarRow = (index) => {
  autoridadTax.value = "";
  if (index < autoresRel.value.length - 1) {
    const item = autoresRel.value[index];
    autoresRel.value.splice(index, 1);
    autoresRel.value.splice(index + 1, 0, item);
  }
}
const deleteRow = (index) => {
  autoridadTax.value = "";
  autoresRel.value.splice(index, 1)
}
const handleInput = (value, scope, campo) => {
  autoridadTax.value = "";
  let filteredValue = '';
  for (const char of value) {
    if (allowedChars.has(char.toLowerCase())) {
      filteredValue += char;
    }
  }
  filteredValue = filteredValue.slice(0, 15);
  scope.row[campo] = filteredValue;
};
const onKeyDown = (event) => {
  if (event.ctrlKey || event.altKey || event.metaKey ||
    [8, 9, 13, 16, 17, 18, 19, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 45, 46].includes(event.keyCode)) {
    return;
  }
  if (!allowedChars.has(event.key.toLowerCase())) {
    event.preventDefault();
  }
};
const onPaste = (event, scope) => {
  const clipboardData = event.clipboardData || window.clipboardData;
  const pastedText = clipboardData.getData('text');
  let filteredText = '';
  for (const char of pastedText) {
    if (allowedChars.has(char.toLowerCase())) {
      filteredText += char;
    }
  }
  scope.row.CadFinal = filteredText;
};
const cargaListAutor = async (idsAutor) => {
  try {
    autoridadTax.value = '';
    const response = await axios.get('/busca-autoresRel', {
      params: {
        ids: idsAutor
      }
    });
    if (response.data.status === 200) {
      props.autorRel.forEach(autor => {
        const autorEncontrado = response.data.autores.find(
          autoridad => autoridad.IdAutorTaxon === autor.IdAutorTaxon)
        autoresRel.value.push({
          IdAutorTaxon: autor.IdAutorTaxon,
          NombreAutoridad: autorEncontrado.NombreAutoridad,
          CadInicio: autor.CadInicio,
          CadFinal: autor.CadFinal
        })
      });
    }
  } catch (error) {
    console.error('Error al obtener los datos:', error);
  }
}

watch(() => [props.autorRel, props.nombre], async () => {
  if (props.autorRel?.length > 0) {
    const idsAutor = props.autorRel.map(autor => autor.IdAutorTaxon).join(",");
    await cargaListAutor(idsAutor);
  }
  else {
    autoresRel.value = [];
  }
}, { immediate: true, deep: true });

const fetchFilteredData = async () => {
  console.log(
    "CuerpoAutorTaxon: Fetching data - Page:",
    currentPage.value,
    "Filter:",
    filtro.value,
    "Sort:", 
    sorting.value
  );
  try {
    const response = await axios.get("/busca-autor", {
      params: {
        autor: filtro.value,
        page: currentPage.value,
        perPage: itemsPerPage.value,
        sortBy: sorting.value.prop, 
        sortOrder: sorting.value.order,
      },
    });
    
    currentData.value = response.data.data || [];
    totalItems.value = response.data.total || response.data.totalItems || 0;
     mostrarNotificacion(
      "Error de Carga",
      "Ocurrió un error al cargar los autores.",
      "error",
      7000
    );
  } catch (error) {
    console.error("CuerpoAutorTaxon: Error al obtener los datos:", error);
    currentData.value = [];
    totalItems.value = 0;
    mostrarNotificacion(
      "Error de Carga",
      "Ocurrió un error al cargar los autores.",
      "error",
      7000
    );
  }
};

const handleSortChange = ({ prop, order }) => {
  sorting.value.prop = prop;
  sorting.value.order = order === 'ascending' ? 'asc' : 'desc';
  if (!order) {
    sorting.value.prop = null;
    sorting.value.order = null;
  }
  currentPage.value = 1;
  fetchFilteredData();
};


const nuevoItem = () => {
  itemEditado.value = null;
  accionModalForm.value = "crear";
  modalFormVisible.value = true;
};
const editarItem = (item) => {
  itemEditado.value = { ...item };
  accionModalForm.value = "editar";
  modalFormVisible.value = true;
};
const cerrarFormModal = () => {
  console.log(
    "CuerpoAutorTaxon: Evento @cerrar recibido de FormAutorTaxon. Cerrando modal."
  );
  modalFormVisible.value = false;
};
const handleFormAutorSubmited = (datosDelFormulario) => {
  cerrarFormModal();
  const procederConGuardado = async () => {
    ElMessageBox.close();
    try {
      guardandoDatosServer.value = true;
      let response;
      const payload = {
        nombreAutoridad: datosDelFormulario.nombreAutoridad,
        nombreCompleto: datosDelFormulario.nombreCompleto,
        grupoTaxonomico: datosDelFormulario.grupoTaxonomico,
      };
      if (datosDelFormulario.accionOriginal === 'crear') {
        response = await axios.post("/autores", payload);
      } else if (datosDelFormulario.accionOriginal === 'editar' && datosDelFormulario.idParaEditar) {
        response = await axios.put(`/autores/${datosDelFormulario.idParaEditar}`, payload);
      } else {
        throw new Error("Acción de formulario no válida o falta ID.");
      }
      await fetchFilteredData();
      mostrarNotificacion("¡Operación Exitosa!", "Los cambios se guardaron correctamente.", "success");
    } catch (error) {
      if (error.response) {
        console.error("Error de Axios durante guardado:", error.response);
        if (error.response.status === 422) {
          const errors = error.response.data.errors;
          let errorMsg = "Error de validación del servidor:<ul>" + Object.values(errors).flat().map(e => `<li>${e}</li>`).join("") + "</ul>";
          mostrarNotificacion("Error de Validación", errorMsg, "error", 0, true);
        } else {
          mostrarNotificacion("Error del Servidor", error.response.data?.message || "Ocurrió un error.", "error");
        }
      } else {
        console.error("Error inesperado:", error);
        mostrarNotificacion("Error Inesperado", "Ocurrió un error.", "error");
      }
    } finally {
      guardandoDatosServer.value = false;
    }
  };
  const cancelarGuardado = () => {
    ElMessageBox.close();
   
  };
  const mensaje = `¿Estás seguro de que deseas guardar los cambios para "${datosDelFormulario.nombreAutoridad || "nuevo autor"}"?`;
  ElMessageBox({
    title: 'Confirmar Guardado',
    showConfirmButton: false,
    showCancelButton: false,
    customClass: 'message-box-diseno-limpio',
    message: h('div', { class: 'custom-message-content' }, [
      h('div', { class: 'body-content' }, [
        h('div', { class: 'custom-warning-icon-container' }, [
          h('div', { class: 'custom-warning-circle' }, '!')
        ]),
        h('div', { class: 'text-container' }, [h('p', null, mensaje)])
      ]),
      h('div', { class: 'footer-buttons' }, [
        h(BotonCancelar, {
          onClick: cancelarGuardado,
        }),
        h(BotonAceptar, {
          onClick: procederConGuardado
        }),
      ])
    ])
  }).catch(() => {
    
  });
};
const eliminarItem = (itemId) => {
  const procederConEliminacion = async () => {
    try {
      ElMessageBox.close();
      console.log("Aqui mando el mensaje?");
      return;
      const autorAEliminar = currentData.value.find(aut => aut.IdAutorTaxon === itemId);
      const nombreAutorEliminado = autorAEliminar ? `"${autorAEliminar.NombreCompleto}"` : 'el registro seleccionado';
      await axios.delete(`/autores/${itemId}`);
      await fetchFilteredData();
      mostrarNotificacion('¡Eliminación Exitosa!', `Autor ${nombreAutorEliminado} eliminado correctamente.`, 'success');
    } catch (apiError) {
      console.error("Error en la API al eliminar:", apiError);
      ElMessageBox.alert('Ocurrió un error al intentar eliminar el registro.', 'Error', { type: 'error' });
    }
  };
  const cancelarEliminacion = () => {
    ElMessageBox.close();
  };
  const autorAEliminar = currentData.value.find(aut => aut.IdAutorTaxon === itemId);
  const nombreAutorEliminado = autorAEliminar ? `"${autorAEliminar.NombreCompleto}"` : 'el registro seleccionado';
  const mensaje = `¿Está seguro de eliminar el autor ${nombreAutorEliminado}? Esta acción no se puede revertir.`;
  ElMessageBox({
    title: 'Confirmar eliminación',
    showConfirmButton: false,
    showCancelButton: false,
    customClass: 'message-box-diseno-limpio',
    message: h('div', { class: 'custom-message-content' }, [
      h('div', { class: 'body-content' }, [
        h('div', { class: 'custom-warning-icon-container' }, [
          h('div', { class: 'custom-warning-circle' }, '!')
        ]),
        h('div', { class: 'text-container' }, [h('p', null, mensaje)])
      ]),
      h('div', { class: 'footer-buttons' }, [
        h(BotonCancelar, { onClick: cancelarEliminacion }),
        h(BotonAceptar, { onClick: procederConEliminacion }),
      ])
    ])
  }).catch(() => {
  });
};
const buscarPorFiltro = () => {
  currentPage.value = 1;
  fetchFilteredData();
};
const handlePageChange = (page) => {
  currentPage.value = page;
  fetchFilteredData();
};
const mostrarNotificacion = (
  titulo,
  mensaje,
  tipo = "info",
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
onMounted(fetchFilteredData);
</script>

<template>
  <div class="app-container">
    <div class="page-title-header">
      <h1 class="page-main-title-class">Catálogo de autores taxonómicos</h1>
    </div>

    <div class="content-wrapper2">


      <div class="content-wrapper">
        <div class="table-wrapper">

          <div class="main-section" v-if="props.nombre">
            <el-card class="box-card-inner-table">
              <div v-show="Object.keys(autoresRel).length > 0 || props.nombre === true" class="demo-collapse"
                style="margin-bottom: 20px;">
                <el-collapse v-model="activeNames">
                  <el-collapse-item title="Autores Relacionados" name="1">
                    <el-scrollbar max-height="400px">
                      <el-input v-model="autoridadTax" :rows="2" disabled type="textarea"
                        placeholder="Autoridad Taxonomica">
                      </el-input>
                      <el-table :data="autoresRel" style="width: 100%" max-height="250" :show-header="false">
                        <el-table-column prop="IdAutorTaxon" label="Id Autor Taxon" width="80"
                          v-if="false"></el-table-column>
                        <el-table-column label="Texto Inicio" width="120">
                          <template #default="scope">
                            <el-input v-model="scope.row.CadInicio" placeholder="Texto Autor" maxlength="15"
                              @input="(value) => handleInput(value, scope, 'CadInicio')"
                              @keydown.native.prevent="onKeyDown($event)"
                              @paste.native.prevent="onPaste($event, scope)" />
                          </template>
                        </el-table-column>
                        <el-table-column prop="NombreAutoridad" label="Nombre Autoridad"
                          min-width="180"></el-table-column>
                        <el-table-column label="Texto Final" width="120">
                          <template #default="scope">
                            <el-input v-model="scope.row.CadFinal" placeholder="Texto Autor" maxlength="15"
                              @input="(value) => handleInput(value, scope, 'CadFinal')"
                              @keydown.native.prevent="onKeyDown($event)"
                              @paste.native.prevent="onPaste($event, scope)" />
                          </template>
                        </el-table-column>
                        <el-table-column label="Mover" width="100">
                          <template #default="scope">
                            <div style="display: flex; justify-content: space-around;">
                              <el-button circle type="warning" size="small" :disabled="scope.$index === 0"
                                @click="subirRow(scope.$index)">
                                <el-icon>
                                  <ArrowUp />
                                </el-icon>
                              </el-button>
                              <el-button circle type="warning" size="small"
                                :disabled="scope.$index === autoresRel.length - 1" @click="bajarRow(scope.$index)">
                                <el-icon>
                                  <ArrowDown />
                                </el-icon>
                              </el-button>
                            </div>
                          </template>
                        </el-table-column>
                        <el-table-column label="Acciones" width="100">
                          <template #default="scope">
                            <div style="display: flex; justify-content: space-around;">
                              <el-button circle type="danger" class="float-right"
                                @click.prevent="deleteRow(scope.$index)">
                                <el-icon>
                                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                                    <path fill="currentColor"
                                      d="M160 256H96a32 32 0 0 1 0-64h256V95.936a32 32 0 0 1 32-32h256a32 32 0 0 1 32 32V192h256a32 32 0 1 1 0 64h-64v672a32 32 0 0 1-32 32H192a32 32 0 0 1-32-32zm448-64v-64H416v64zM224 896h576V256H224zm192-128a32 32 0 0 1-32-32V416a32 32 0 0 1 64 0v320a32 32 0 0 1-32 32m192 0a32 32 0 0 1-32-32V416a32 32 0 0 1 64 0v320a32 32 0 0 1-32 32">
                                    </path>
                                  </svg>
                                </el-icon>
                              </el-button>
                            </div>
                          </template>
                        </el-table-column>
                      </el-table>
                    </el-scrollbar>
                    <br>
                    <div style="display: flex; justify-content: space-between;">
                      <el-tooltip class="item" effect="dark" content="Genera Autoridad Taxonomica"
                        placement="right-start">
                        <el-button @click="armaAutoridad" circle type="primary">
                          <el-icon>
                            <Switch />
                          </el-icon>
                        </el-button>
                      </el-tooltip>
                      <el-tooltip class="item" effect="dark" content="Asociar Autores" placement="right-start">
                        <el-button @click="traspasaDatos" circle type="primary">
                          <el-icon>
                            <DArrowRight />
                          </el-icon>
                        </el-button>
                      </el-tooltip>
                    </div>
                  </el-collapse-item>
                </el-collapse>
              </div>
            </el-card>
          </div>

          <div class="main-section">
            <el-card class="box-card-inner-table">
              <template #header>
                <div class="header-container">
                  <div class="right">
                    <FiltrarPor v-model:busca="filtro" @update:busca="buscarPorFiltro"
                      placeholder="Escribe para filtrar..." />
                  </div>
                  <div class="left">
                    <NuevoButton @crear="nuevoItem" />
                  </div>
                </div>
              </template>
              <div class="table-responsive">
                <el-table :data="currentData" :border="true" height="400" @sort-change="handleSortChange"
                  @row-dblclick="agregarAutor">
                  <el-table-column type="expand">
                    <template #default="{ row }">
                      <div class="expand-content-detail">
                        <p>
                          <strong>Id Autor Taxon:</strong> {{ row.IdAutorTaxon }}
                        </p>
                        <p><strong>Catálogo:</strong> {{ row.Catalogo }}</p>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column prop="NombreAutoridad" label="Abreviado" min-width="180" sortable="custom"
                    align="center"></el-table-column>
                  <el-table-column prop="NombreCompleto" label="Nombre Completo" min-width="250" sortable="custom"
                    align="left"></el-table-column>
                  <el-table-column prop="GrupoTaxonomico" label="Grupo Taxonómico" min-width="180" sortable="custom"
                    align="center"></el-table-column>
                  <el-table-column label="Acciones" width="120" align="center">
                    <template #default="{ row }">
                      <div class="action-buttons-container">
                        <EditarButton @editar="editarItem(row)" />
                        <EliminarButton @eliminar="eliminarItem(row.IdAutorTaxon)" />
                      </div>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </el-card>
          </div>

          <div class="main-section">
            <div v-if="totalItems > 0" class="pagination-container-wrapper">
              <el-pagination :current-page="currentPage" :page-size="itemsPerPage" :total="totalItems"
                @current-change="handlePageChange" layout="prev, pager, next, total" background
                class="main-pagination-style">
              </el-pagination>
            </div>
          </div>

        </div>
      </div>
    </div>

    <Teleport to="body">
      <FormAutorTaxon :visible="modalFormVisible" :autTaxEdit="itemEditado" :accion="accionModalForm"
        @cerrar="cerrarFormModal" @formSubmited="handleFormAutorSubmited" />
    </Teleport>

    <Teleport to="body">
      <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
        :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
        @close="cerrarNotificacion" />
    </Teleport>
  </div>
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

.content-wrapper2 {
    width: 100%;
    max-width: 1600px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
    padding: 25px;
    display: flex;
    flex-direction: column;
    align-items: stretch;
}


.page-main-title-class {
    font-size: 22px !important;
    font-weight: 600 !important;
    color: #303133 !important;
    margin-bottom: 20px !important;
    width: 1550px;
}

.app-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background-color: #f0f2f5;
    align-items: center;
    padding: 20px;
    box-sizing: border-box;
}

.content-wrapper {
    width: 100%;
    max-width: 1600px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
    padding: 25px;
    display: flex;
    flex-direction: column;
    align-items: stretch;
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

.el-card.box-card-inner-table {
    border: 1px solid #e4e7ed !important;
    box-shadow: none !important;
    border-radius: 6px !important;
    overflow: hidden;
}

:deep(.el-card__header) {
    padding: 15px 20px !important;
    border-bottom: 1px solid #e4e7ed !important;
    background-color: #fff;
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-responsive {
    overflow-x: auto;
}

:deep(.el-table) {
    border-radius: 0 !important;
    border-top: none !important;
    border-left: none !important;
    border-right: none !important;
    border-bottom: 1px solid #ebeef5 !important;
    box-shadow: none !important;
}

:deep(.el-table th.el-table__cell) {
    background-color: #fafafa !important;
    color: #606266 !important;
    font-weight: 500 !important;
    text-align: center !important;
    padding: 10px 10px !important;
    font-size: 13px !important;
    border-bottom: 1px solid #ebeef5 !important;
}

:deep(.el-table td.el-table__cell) {
    padding: 10px 10px !important;
    font-size: 13px !important;
    color: #303133;
    border-bottom: 1px solid #ebeef5 !important;
}

:deep(.el-table .el-table__row:hover > td.el-table__cell) {
    background-color: #f5f7fa !important;
}

.expand-content-detail {
    padding: 10px 15px;
    background-color: #fdfdfd;
    font-size: 13px;
}

.action-buttons-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.pagination-container-wrapper {
    display: flex;
    justify-content: flex-start;
    padding-top: 20px;
    margin-top: 0;
}

:deep(.main-pagination-style button),
:deep(.main-pagination-style .el-pager li) {
    background-color: #fff !important;
    border: 1px solid #dcdfe6 !important;
    border-radius: 4px !important;
    font-size: 13px !important;
    min-width: 30px !important;
    height: 30px !important;
    line-height: 28px !important;
}

:deep(.main-pagination-style .el-pager li.is-active) {
    background-color: #409eff !important;
    color: white !important;
    border-color: #409eff !important;
}
</style>