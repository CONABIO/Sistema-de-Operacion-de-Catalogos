<script setup>
import { ref, onMounted, watch, h, nextTick } from "vue";
import axios from "axios";
import { ElMessage, ElMessageBox, ElDropdown, ElDropdownMenu, ElDropdownItem, ElInput, ElCard, ElCollapse, ElCollapseItem, ElScrollbar, ElTable, ElTableColumn, ElTooltip, ElButton, ElIcon, ElPagination, ElRadioGroup, ElRadioButton } from "element-plus";
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import NuevoButton from "@/Components/Biotica/NuevoButton.vue";
import EditarButton from "@/Components/Biotica/EditarButton.vue";
import EliminarButton from "@/Components/Biotica/EliminarButton.vue";
import FormAutorTaxon from "@/Pages/Socat/Autores/FormAutorTaxon.vue";
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import { DArrowRight, ArrowUp, ArrowDown, Switch, Search, CircleClose } from '@element-plus/icons-vue';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";



const selectedRowId = ref(null);

const manejarClickFila = (row) => {
  selectedRowId.value = row ? row.IdAutorTaxon : null;
  if (tablaRef.value) {
    tablaRef.value.selectedRow = row;
  }
};

const tableRowClassName = ({ row }) => {
  if (selectedRowId.value && String(row.IdAutorTaxon) === String(selectedRowId.value)) {
    return 'fila-seleccionada-verde';
  }
  return '';
};

const emit = defineEmits(['traspasoAutores']);
const props = defineProps({
  autorRel: { type: Object, required: false },
  nombre: { type: Boolean, required: false, default: false }
});

const tablaRef = ref(null);
const datosDeAutores = ref([]);
const totalAutores = ref(0);
const columnasDefinidas = ref([
  { prop: 'NombreAutoridad', label: 'Nombre de la autoridad', minWidth: '180', sortable: true, align: 'left', filtrable: true },
  { prop: 'NombreCompleto', label: 'Nombre completo', minWidth: '250', sortable: true, align: 'left', filtrable: true },
  { prop: 'GrupoTaxonomico', label: 'Grupo taxonomico', minWidth: '180', sortable: true, align: 'left', filtrable: true }
]);

const opcionesFiltroAutores = ref([
  { label: 'NombreAutoridad', value: 'NombreAutoridad' },
  { label: 'NombreCompleto', value: 'NombreCompleto' },
  { label: 'GrupoTaxonomico', value: 'GrupoTaxonomico' }
]);

const autoresRel = ref([]);
const activeNames = ref(['1']);
const autoridadTax = ref('');
const allowedChars = new Set([
  '(', ')', '[', ']', '&', 'e', 'x', 'i', 'n', 'o', 'c', 's', 'u',
  'y', 'u', 't', 'a', 'f', 'p', '1', '2', '3', '4', '5', '6', '7',
  '8', '9', '0', ',', '.', '-', ':', ';', ' '
]);
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


const manejarNuevoItem = () => {
  itemEditado.value = null;
  accionModalForm.value = "crear";
  modalFormVisible.value = true;
};


const manejarEditarItem = (item) => {
  itemEditado.value = { ...item };
  accionModalForm.value = "editar";
  modalFormVisible.value = true;
};


const cerrarFormModal = () => {
  modalFormVisible.value = false;
};


const irAlRegistroEspecifico = async (idEncontrado) => {
  try {
    selectedRowId.value = null; 
    if (tablaRef.value) {
      tablaRef.value.selectedRow = null; 
      tablaRef.value.limpiarTodosLosFiltros(); 
    }

    const currentSort = tablaRef.value?.sorting || { prop: 'NombreAutoridad', order: 'asc' };
    const resPagina = await axios.post('/autores/obtener-pagina', {
      id: idEncontrado,
      perPage: 100,
      sortBy: currentSort.prop || 'NombreAutoridad',
      sortOrder: currentSort.order || 'asc'
    });

    const paginaDestino = resPagina.data.page;

    if (tablaRef.value) {
      await tablaRef.value.irAPagina(paginaDestino);
      await nextTick();
      const filaEncontrada = datosDeAutores.value.find(d => String(d.IdAutorTaxon) === String(idEncontrado));
      if (filaEncontrada) {
        selectedRowId.value = idEncontrado; 
        tablaRef.value.selectedRow = filaEncontrada; 
        setTimeout(() => {
          tablaRef.value.forzarFocoFilaVerde();
        }, 250); 
      }
    }
  } catch (err) {
    console.error("Error al redirigir al registro:", err);
  }
};


const handleFormAutorSubmited = async (datosDelFormulario) => {
  cerrarFormModal();

  const autorLocal = datosDeAutores.value.find(autor =>
    autor.NombreAutoridad.trim().toLowerCase() === datosDelFormulario.nombreAutoridad.trim().toLowerCase() &&
    autor.GrupoTaxonomico.trim().toLowerCase() === datosDelFormulario.grupoTaxonomico.trim().toLowerCase() &&
    autor.IdAutorTaxon !== datosDelFormulario.idParaEditar
  );

  if (autorLocal) {
    selectedRowId.value = autorLocal.IdAutorTaxon;
    if (tablaRef.value) {
      tablaRef.value.selectedRow = autorLocal;
      tablaRef.value.forzarFocoFilaVerde();
    }
    mostrarNotificacion("Aviso", "La autoridad taxonómica que ingresó ya existe", "warning");
    return;
  }

  const procederConGuardado = async () => {
    try {
      guardandoDatosServer.value = true;
      const payload = {
        nombreAutoridad: datosDelFormulario.nombreAutoridad,
        nombreCompleto: datosDelFormulario.nombreCompleto,
        grupoTaxonomico: datosDelFormulario.grupoTaxonomico,
      };

      if (datosDelFormulario.accionOriginal === 'crear') {
        const response = await axios.post("/autores", payload);
        const nuevoId = response.data.autorTaxon?.IdAutorTaxon || response.data.IdAutorTaxon || response.data.id;
        mostrarNotificacion("Ingreso", "La información ha sido ingresada correctamente.", "success");
        if (nuevoId) {
          await irAlRegistroEspecifico(nuevoId);
        }
      } else {
        await axios.put(`/autores/${datosDelFormulario.idParaEditar}`, payload);
        mostrarNotificacion("Modificación", "La información ha sido modificada correctamente.", "success");
        if (tablaRef.value) await tablaRef.value.fetchData();
        await nextTick();
        if (tablaRef.value) tablaRef.value.forzarFocoFilaVerde();
      }
    } catch (error) {
      if (error.response?.status === 400 && error.response.data.idExistente) {
        mostrarNotificacion("Aviso", "La autoridad taxonómica que ingresó ya existe", "warning");
        await irAlRegistroEspecifico(error.response.data.idExistente);
      } else if (error.response?.status === 422) {
        const errors = error.response.data.errors;
        let errorMsg = "Error de validación:<ul>" + Object.values(errors).flat().map(e => `<li>${e}</li>`).join("") + "</ul>";
        mostrarNotificacion("Error", errorMsg, "error", 0);
      } else {
        mostrarNotificacionError("Error", "No se pudo procesar la información.");
      }
    } finally {
      guardandoDatosServer.value = false;
    }
  };

  if (datosDelFormulario.accionOriginal === 'crear') {
    await procederConGuardado();
  } else {
    const mensaje = `¿Estás seguro de que deseas guardar los cambios para "${datosDelFormulario.nombreAutoridad}"?`;
    ElMessageBox({
      title: 'Confirmar modificación',
      showConfirmButton: false,
      showCancelButton: false,
      customClass: 'message-box-diseno-limpio',
      message: h('div', { class: 'custom-message-content' }, [
        h('div', { class: 'body-content' }, [
          h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
          h('div', { class: 'text-container' }, [h('p', null, mensaje)])
        ]),
        h('div', { class: 'footer-buttons' }, [
          h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
          h(BotonAceptar, {
            onClick: async () => {
              ElMessageBox.close();
              await procederConGuardado();
            }
          }),
        ])
      ])
    }).catch(() => { });
  }
};



const manejarEliminarItem = (itemId) => {
  const procederConEliminacion = async () => {
    try {
      ElMessageBox.close();


      const autorAEliminar = datosDeAutores.value.find(aut => aut.IdAutorTaxon === itemId);

      const nombreAutorEliminado = autorAEliminar ? `"${autorAEliminar.NombreCompleto}"` : 'el registro seleccionado';
      await axios.delete(`/autores/${itemId}`);
      if (tablaRef.value) {
        tablaRef.value.fetchData();
      }
      mostrarNotificacion('Eliminación exitosa', `Autor ${nombreAutorEliminado} eliminado correctamente.`, 'success');
    } catch (apiError) {
      mostrarNotificacionError('Aviso', `El autor ${nombreAutorEliminado} no se puede eliminar. Este autor esta asociado a un taxón.`, 'success');
    }
  };
  const cancelarEliminacion = () => {
    ElMessageBox.close();
  };
  const autorAEliminar = datosDeAutores.value.find(aut => aut.IdAutorTaxon === itemId);
  const nombreAutorEliminado = autorAEliminar ? `"${autorAEliminar.NombreCompleto}"` : 'el registro seleccionado';
  const mensaje = `¿Está seguro de eliminar el autor ${nombreAutorEliminado}? Esta acción no se puede revertir.`;
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



const cerrarNotificacion = () => {
  notificacionVisible.value = false;
};
</script>

<template>
  <LayoutCuerpo :usar-app-layout="false" tituloPag="Autoridades Taxonómicas"
    tituloArea="Catálogo de autoridades taxonómicas">
    <div class="h-full flex flex-col">
      <div v-if="props.nombre" class="main-section" style="margin-bottom: 20px;">
        <el-card class="box-card-inner-table">
          <el-collapse v-model="activeNames">
            <el-collapse-item title="Autores Relacionados" name="1">
              <el-scrollbar max-height="400px">
                <el-input v-model="autoridadTax" :rows="2" disabled type="textarea"
                  placeholder="Autoridad Taxonomica" />
                <el-table :data="autoresRel" style="width: 100%" max-height="250" :show-header="false">
                  <el-table-column prop="IdAutorTaxon" label="Id" width="80" v-if="false" />
                  <el-table-column label="Texto Inicio" width="120">
                    <template #default="scope"><el-input v-model="scope.row.CadInicio" placeholder="Texto"
                        maxlength="15" @input="val => handleInput(val, scope, 'CadInicio')" /></template>
                  </el-table-column>
                  <el-table-column prop="NombreAutoridad" label="Nombre" min-width="180" />
                  <el-table-column label="Texto Final" width="120">
                    <template #default="scope"><el-input v-model="scope.row.CadFinal" placeholder="Texto" maxlength="15"
                        @input="val => handleInput(val, scope, 'CadFinal')" @keydown.native.prevent="onKeyDown($event)"
                        @paste.native.prevent="onPaste($event, scope)" /></template>
                  </el-table-column>
                  <el-table-column label="Mover" width="100">
                    <template #default="scope">
                      <div style="display: flex; justify-content: space-around;"><el-button circle type="warning"
                          size="small" :disabled="scope.$index === 0" @click="subirRow(scope.$index)"><el-icon>
                            <ArrowUp />
                          </el-icon></el-button><el-button circle type="warning" size="small"
                          :disabled="scope.$index === autoresRel.length - 1" @click="bajarRow(scope.$index)"><el-icon>
                            <ArrowDown />
                          </el-icon></el-button></div>
                    </template>
                  </el-table-column>
                  <el-table-column label="Acciones" width="100">
                    <template #default="scope">
                      <div style="display: flex; justify-content: space-around;"><el-button circle type="danger"
                          @click.prevent="deleteRow(scope.$index)"><el-icon><svg xmlns="http://www.w3.org/2000/svg"
                              viewBox="0 0 1024 1024">
                              <path fill="currentColor"
                                d="M160 256H96a32 32 0 0 1 0-64h256V95.936a32 32 0 0 1 32-32h256a32 32 0 0 1 32 32V192h256a32 32 0 1 1 0 64h-64v672a32 32 0 0 1-32 32H192a32 32 0 0 1-32-32zm448-64v-64H416v64zM224 896h576V256H224zm192-128a32 32 0 0 1-32-32V416a32 32 0 0 1 64 0v320a32 32 0 0 1-32 32m192 0a32 32 0 0 1-32-32V416a32 32 0 0 1 64 0v320a32 32 0 0 1-32 32">
                              </path>
                            </svg></el-icon></el-button></div>
                    </template>
                  </el-table-column>
                </el-table>
              </el-scrollbar>
              <br>
              <div style="display: flex; justify-content: space-between;">
                <el-tooltip effect="dark" content="Genera Autoridad Taxonomica" placement="right-start"><el-button
                    @click="armaAutoridad" circle type="primary"><el-icon>
                      <Switch />
                    </el-icon></el-button></el-tooltip>
                <el-tooltip effect="dark" content="Asociar Autores" placement="right-start"><el-button
                    @click="traspasaDatos" circle type="primary"><el-icon>
                      <DArrowRight />
                    </el-icon></el-button></el-tooltip>
              </div>
            </el-collapse-item>
          </el-collapse>
        </el-card>
      </div>

      <TablaFiltrable ref="tablaRef" class="flex-grow" :container-class="'main-section'" :columnas="columnasDefinidas"
        v-model:datos="datosDeAutores" v-model:total-items="totalAutores" :opciones-filtro="opcionesFiltroAutores"
        endpoint="/busca-autor" id-key="IdAutorTaxon" @row-click="manejarClickFila" :row-class-name="tableRowClassName"
        @editar-item="manejarEditarItem" @eliminar-item="manejarEliminarItem" @nuevo-item="manejarNuevoItem"
        @row-dblclick="agregarAutor" :highlight-current-row="false">


        <template #header-actions>
          <NuevoButton @crear="manejarNuevoItem" />
        </template>
        <template #expand-column>
          <el-table-column type="expand">
            <template #default="{ row }">
              <div class="expand-content-detail">
                <p><strong>IdAutorTaxon:</strong> {{ row.IdAutorTaxon }}</p>
                <p><strong>IdOriginal:</strong> {{ row.IdOriginal }}</p>
                <p><strong>NombreAutoridadOriginal:</strong> {{ row.NombreAutoridadOriginal }}</p>
                <p><strong>Catalogo:</strong> {{ row.Catalogo }}</p>
                <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                <p><strong>FechaModificacion:</strong> {{ row.FechaModificacion }}</p>
              </div>
            </template>
          </el-table-column>
        </template>
      </TablaFiltrable>
    </div>

    <Teleport to="body">
      <FormAutorTaxon :visible="modalFormVisible" :autTaxEdit="itemEditado" :accion="accionModalForm"
        @cerrar="cerrarFormModal" @formSubmited="handleFormAutorSubmited" />
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

.el-table .fila-seleccionada-verde {
  background-color: #ddf6dd !important;
  --el-table-tr-bg-color: #ddf6dd !important; 
}


</style>

<style scoped>
.main-section {}

.expand-content-detail {
  padding: 10px 15px;
  background-color: #fdfdfd;
  font-size: 13px;
}
</style>