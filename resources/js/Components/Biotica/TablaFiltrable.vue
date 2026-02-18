<script setup>
import { ref, watch, onMounted, computed, nextTick } from 'vue';
import axios from 'axios';
import { ElTable, ElTableColumn, ElPagination, ElCard, ElIcon, ElButton, ElDropdown, ElDropdownMenu, ElDropdownItem, ElInput } from 'element-plus';
import { Search, CircleClose, Management } from '@element-plus/icons-vue';
import NuevoButton from '@/Components/Biotica/NuevoButton.vue';
import EditarButton from '@/Components/Biotica/EditarButton.vue';
import EliminarButton from '@/Components/Biotica/EliminarButton.vue';
import TipoBusqueda from '@/Components/Biotica/TipoBusqueda.vue';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';
import BotonTraspaso from '@/Components/Biotica/BtnTraspaso.vue';

const inputsFiltro = ref({});
const datosTabla = ref([]);

/*Juan Carlos 27/01/2026 - https://ecoinformatica.atlassian.net/browse/SOCAT-6
  Se agregan las propiedades para que los botones de editar, nuevo y borrar se oculten*/
const props = defineProps({
  columnas: { type: Array, required: true },
  datos: { type: Array, required: true, default:[]},
  totalItems: { type: Number, required: true },
  itemsPerPage: { type: Number, default: 100 },
  endpoint: { type: String, required: false, default: ""},
  idKey: { type: String, required: false },
  botCerrar: { type: Boolean, default: false },
  mostrarTraspaso: { type: Boolean, default: false },
  mostrarNuevo: { type: Boolean, default: true },
  mostrarEditar: { type: Boolean, default: true },
  mostrarBorrar: { type: Boolean, default: true },
  rowClassName: { type: Function, default: null },
  mostrarBiblio: { type:Boolean, default: false }, 

  highlightCurrentRow: {
    type: Boolean,
    default: false
  }, 
  asignaTrasp: { 
    type: String, 
    required: false,
    default: "izq"
  },
  mostrarSalir: {
    type: Boolean,
    required: false,
    default: true
  }
});

const onBiblio = () => emit('abrir-Biblio'); 

const handleVisibleChange = (visible, prop) => {
  if (visible) {
    nextTick(() => {
      const inputRef = inputsFiltro.value[prop];
      if (inputRef) {
        inputRef.focus();
      }
    });
  }
};


const limpiarTodosLosFiltros = () => {
  Object.keys(filtros.value).forEach(key => {
    filtros.value[key] = '';
  });
};

const irAPagina = async (numeroPagina) => {
  currentPage.value = numeroPagina;
  await fetchData();
};

const selectedRow = ref(null);


const handleRowClickInterno = (row) => {
  selectedRow.value = row;
  emit('row-click', row);
};

const onEditarInterno = () => {
  if (selectedRow.value) {
    emit('editar-item', selectedRow.value);
  }
};

const onEliminarInterno = () => {
  if (selectedRow.value[props.idKey]) {
    console.log("Entre a la funcion de eliminar de emit: ", selectedRow.value[props.idKey]);
    emit('eliminar-item', selectedRow.value[props.idKey]);
  }else{
     emit('eliminar-item', selectedRow.value);
  }
};


const rowClassNameInterno = ({ row }) => {
  if (props.rowClassName) return props.rowClassName({ row });
  const idFila = row[props.idKey];
  const idSeleccionado = selectedRow.value ? selectedRow.value[props.idKey] : null;
  if (idFila == null || idSeleccionado == null) return '';
  return String(idFila) === String(idSeleccionado) ? 'fila-seleccionada-verde' : '';
};

const tableRefInterna = ref(null);

const forzarFocoFilaVerde = async () => {
  await nextTick();
  setTimeout(() => {
    if (!tableRefInterna.value) return;

    const filaVerde = tableRefInterna.value.$el.querySelector('.fila-seleccionada-verde');

    if (filaVerde) {
      filaVerde.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
      console.warn("Se intentó enfocar, pero la fila verde no está visible en el DOM actual.");
    }
  }, 300);
};

const setFiltroExterno = (campo, valor) => {
  filtros.value[campo] = valor;
  onFiltroInput();
};



const emit = defineEmits([
  'update:datos',
  'update:totalItems',
  'editar-item',
  'eliminar-item',
  'nuevo-item',
  'row-dblclick',
  'row-click',
  'traspasaBiblio',
  'traspasaSeleccionado',
  'cerrar',
  'abrir-Biblio'
]);

const onExpandChange = (row) => {
  selectedRow.value = row
  tableRefInterna.value?.setCurrentRow(row)
  emit('row-click', row)
}

const accionModal = computed(() => {
  return props.botCerrar ? "cerrar" : "salida"}
);

const paginatedDatos = computed(() => {
  const start = (currentPage.value - 1) * props.itemsPerPage;
  const end = start + props.itemsPerPage;
  return datosTabla.value.slice(start, end);
});

const currentPage = ref(1);
const filtros = ref({});
const sorting = ref({ prop: null, order: null });
const tipoDeBusqueda = ref('inicia');


watch(() => props.columnas, (nuevasColumnas) => {
  const nuevosFiltros = {};
  if (nuevasColumnas) {
    nuevasColumnas.forEach(col => {
      if (col.filtrable) {
        nuevosFiltros[col.prop] = '';
      }
    });
  }
  filtros.value = nuevosFiltros;
}, { immediate: true, deep: true });

watch(tipoDeBusqueda, () => {
  onFiltroInput();
});

watch(
  () => props.datos,
  (newVal) => {
    if (newVal && newVal.length > 0) {
      datosTabla.value = props.datos;
    }
    else{
      fetchData();
    }
  },
)

const tableKey = ref(0);


const fetchData = async () => {
  try {
    
    if(props.endpoint === "")
    {
      return;
    }

    const idPreviamenteSeleccionado = selectedRow.value ? selectedRow.value[props.idKey] : null;

    const response = await axios.get(props.endpoint, {
      params: {
        filtros: filtros.value,
        tipo_busqueda: tipoDeBusqueda.value,
        page: currentPage.value,
        perPage: props.itemsPerPage,
        sortBy: sorting.value.prop,
        sortOrder: sorting.value.order,
      }
    });

    const resultados = response.data.data || [];
    const total = response.data.total !== undefined ? response.data.total : (response.data.totalItems || 0);

    emit('update:datos', resultados);
    emit('update:totalItems', total);

    await nextTick();

    if (resultados.length > 0) {
      const coincidencia = resultados.find(r => String(r[props.idKey]) === String(idPreviamenteSeleccionado));

      if (coincidencia) {
        selectedRow.value = coincidencia;
        tableRefInterna.value?.setCurrentRow(coincidencia);
        emit('row-click', coincidencia);
      } else {
        selectedRow.value = resultados[0];
        tableRefInterna.value?.setCurrentRow(resultados[0]);
        emit('row-click', resultados[0]);
      }
    } else {
      selectedRow.value = null;
      emit('row-click', null);
    }
    console.log("pase por todas las validaciones.");
  } catch (error) {
    console.error(`Error en fetchData:`, error);
  }
};

/*Juan carlos 13022026 
Estos watch se colocaron para que sirmpre se muestre seleccionada la primera fila de la tabla sin importar como se carguen los datos por end-point o por paso de valores
*/
// Watch para cuando cambian los datos (desde el padre o desde fetch)
watch(() => props.datos, (newDatos) => {
  if (newDatos && newDatos.length > 0) {
    datosTabla.value = newDatos;
    
    // Seleccionar la primera fila después de actualizar
    nextTick(() => {
      selectedRow.value = newDatos[0];
      if (tableRefInterna.value) {
        tableRefInterna.value.setCurrentRow(newDatos[0]);
      }
      emit('row-click', newDatos[0]);
    });
  } else {
    fetchData();
  }
}, { immediate: true, deep: true });

// Watch para cuando cambian los datos paginados (después de fetch interno)
watch(paginatedDatos, (newPaginated) => {
  if (newPaginated && newPaginated.length > 0) {
    // Verificar si la fila seleccionada actual ya no está en los datos paginados
    const currentSelectedId = selectedRow.value ? selectedRow.value[props.idKey] : null;
    const existsInPaginated = newPaginated.some(row => 
      String(row[props.idKey]) === String(currentSelectedId)
    );
    
    // Si no existe o no hay selección, seleccionar la primera
    if (!existsInPaginated || !selectedRow.value) {
      nextTick(() => {
        selectedRow.value = newPaginated[0];
        if (tableRefInterna.value) {
          tableRefInterna.value.setCurrentRow(newPaginated[0]);
        }
        emit('row-click', newPaginated[0]);
      });
    }
  }
}, { immediate: true });
/* hasta aqui se agrego juan carlos 13/02/2026*/

let debounceTimer;

const onFiltroInput = () => {
  currentPage.value = 1;
  fetchData();
};

const limpiarFiltro = (campo) => {
  if (filtros.value[campo]) {
    filtros.value[campo] = '';
    currentPage.value = 1;
    fetchData();
  }
};

const handleSortChange = ({ prop, order }) => {
  sorting.value.prop = prop;
  sorting.value.order = order === 'ascending' ? 'asc' : 'desc';
  currentPage.value = 1;
  fetchData();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  console.log("Este es el valor de page: ", page);
  fetchData();
};

const onEditar = (item) => emit('editar-item', item);
const onEliminar = (id) => emit('eliminar-item', id);
const onNuevo = () => emit('nuevo-item');
const onRecuperaMarcado = () => emit('traspasaBiblio');

const cerrarModal = () => {
  emit('cerrar');
};

onMounted(fetchData);

defineExpose({
  fetchData,
  forzarFocoFilaVerde,
  setFiltroExterno,
  irAPagina,
  limpiarTodosLosFiltros,
  sorting,
  selectedRow,
});
</script>

<template>
  <el-card class="box-card-inner-table">
    <template #header>
      <div class="header-container" style="flex-grow: 1;">
        <div class="right">
          <slot name="header-title">
            <TipoBusqueda v-model="tipoDeBusqueda" />
          </slot>
        </div>
        <div class="left">
          <div class="botonera-biotica">
             <!--Juan Carlos - 27/01/2026 https://ecoinformatica.atlassian.net/browse/SOCAT-6
                Se agrega la funcionalidad para mostrar o ocultar los botones de acciones-->
            <BotonTraspaso :icono="props.asignaTrasp" 
                            v-if="props.mostrarTraspaso" @traspasa="onRecuperaMarcado" />
            <NuevoButton @crear="onNuevo"  v-if="props.mostrarNuevo" />
            <EditarButton :disabled="!selectedRow" @editar="onEditarInterno" 
                          v-if="props.mostrarEditar" />
            <EliminarButton :disabled="!selectedRow" @eliminar="onEliminarInterno" 
                            v-if="props.mostrarBorrar" />
            <!-- Juan Carlos - 26/01/2026 - https://ecoinformatica.atlassian.net/browse/SOCAT-6
              Se agrego la propiedad accion -->
            <BotonSalir v-if="props.mostrarSalir" :accion = "accionModal" @salir="cerrarModal"/>
            <!--Juan Carlos - 26/01/2026 - https://ecoinformatica.atlassian.net/browse/SOCAT-6
              se agrega el boton de acceso a bibliografia para la tabla filtrable-->
            
            
            <!--NuevoButton @crear="onNuevo" /-->
            <div v-if = "props.mostrarBiblio">
              <el-tooltip class="item" effect="dark" content="Bibliografia">
                <el-button @click="onBiblio" circle style="flex-shrink: 0; 
                            background-color: #509165; color: white;">
                    <el-icon><Management /></el-icon>
                </el-button>
              </el-tooltip>
            </div>
            
          </div>
        </div>
      </div>
    </template>
    <div class="table-responsive ">
      <!--Juan carlos 09/02/2026
          Se agrega la funcion @expand-change ="onExpandChange" para que detecte cuando se expande la columna y por lo tanto se seleccione-->
      <el-table :key="tableKey" 
                ref="tableRefInterna" 
                :highlight-current-row="props.highlightCurrentRow"
                :data="paginatedDatos" 
                :row-key="props.idKey" 
                :row-class-name="props.rowClassName || rowClassNameInterno"
                @row-click="handleRowClickInterno" 
                @expand-change ="onExpandChange"
                :border="true" 
                height="550" 
                @sort-change="handleSortChange">
        <slot name="expand-column"></slot>

        <el-table-column 
                v-for="col in props.columnas" 
                :key="col.prop" 
                :prop="col.prop"
                :min-width="col.minWidth || '150'" 
                :sortable="col.sortable ? 'custom' : false" 
                :align="col.align || 'left'">
          <template #header>
            <div class="custom-header">
              <span>{{ col.label }}</span>
              <el-dropdown v-if="col.filtrable" trigger="click" :hide-on-click="false"
                @visible-change="(visible) => handleVisibleChange(visible, col.prop)">
                <el-button @click.stop circle size="small" class="header-filter-button"
                  :type="filtros[col.prop] ? 'primary' : ''" style="margin-left: 10px;">
                  <el-icon>
                    <Search />
                  </el-icon>
                </el-button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item class="filter-dropdown-item">
                      <div @click.stop>
                        <el-input :ref="el => { if (el) inputsFiltro[col.prop] = el }" v-model="filtros[col.prop]"
                          :placeholder="`Filtrar por ${col.label}`" @keyup.enter="onFiltroInput" clearable
                          @clear="onFiltroInput">
                        </el-input>
                      </div>
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>
          </template>

          <template #default="{ row }">
            <template v-if="col.tipo === 'imagenTexto'">
              <div style="display: flex; align-items: center; gap: 6px;">                
                <span v-if="row[col.prop]?.svg" v-html="row[col.prop].svg" style="height: 25px; width: 25px;"></span>
                <img
                          v-else-if="row[col.prop]?.url"
                          :src="row[col.prop].url"
                          alt="icono"
                          style="height: 25px; width: 25px;"
                      />
                <span>{{ row[col.prop]?.texto }}</span>
              </div>
            </template>

            <template v-else-if="col.tipo === 'textarea'">
              <el-input
                      v-model="row[col.prop]"
                      style="width: 100%;"
                      :rows="2"
                      type="textarea"
                      readonly
                    />
            </template>

            <template v-else>
              <span>{{ row[col.prop] }}</span>
            </template>

          </template>

        </el-table-column>
      </el-table>
    </div>

    <div v-if="totalItems > 0" class="pagination-container-wrapper">
      <el-pagination :current-page="currentPage" :page-size="props.itemsPerPage" :total="props.totalItems"
        @current-change="handlePageChange" layout="prev, pager, next, total" background class="main-pagination-style">
      </el-pagination>
    </div>
  </el-card>
</template>

<style>
.filter-dropdown-item.el-dropdown-menu__item {
  padding: 5px 10px !important;
  background-color: transparent !important;
  cursor: default !important;
}

.filter-dropdown-item.el-dropdown-menu__item:hover {
  background-color: transparent !important;
}

.el-input-group__append .el-button {
  border-radius: 0 var(--el-border-radius-base) var(--el-border-radius-base) 0;
}
</style>

<style scoped>
.box-card-inner-table {
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

.card-header-title {
  font-weight: 500;
  color: #303133;
}

.table-responsive {
  overflow-x: auto;
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

:deep(.el-table .fila-seleccionada-verde:hover > td.el-table__cell) {
  background-color: #ddf6dd !important;
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

:deep(.el-table th.is-sortable .cell) {
  position: relative;
  padding-left: 20px;
}

:deep(.el-table th.is-sortable .caret-wrapper) {
  position: absolute;
  left: 2px;
  top: 50%;
  transform: translateY(-50%);
}

.custom-header {
  align-items: center;
  width: 100%;
  gap: 8px;
  margin-left: 20px;
}

.custom-header>span {
  flex-grow: 1;
  text-align: left;
}

.header-filter-button {
  flex-shrink: 0;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 4px;
  margin-right: 35px;
  gap: 4px;
}

.botonera-biotica {
  display: flex;
  gap: 12px; 
  align-items: center; 
}

.right-header-content {
  display: flex;
  justify-content: flex-end;
}

.form-actions {
  display: flex;
  gap: 30px; 
  justify-content: flex-end;
  margin-bottom: 15px; 
}
</style>