<script setup>
import { ref, watch, onMounted, computed, nextTick } from 'vue';
import axios from 'axios';
import { ElTable, ElTableColumn, ElPagination, ElCard, ElIcon, ElButton, ElDropdown, ElDropdownMenu, ElDropdownItem, ElInput } from 'element-plus';
import { Search, CircleClose } from '@element-plus/icons-vue';
import NuevoButton from '@/Components/Biotica/NuevoButton.vue';
import EditarButton from '@/Components/Biotica/EditarButton.vue';
import EliminarButton from '@/Components/Biotica/EliminarButton.vue';
import TipoBusqueda from '@/Components/Biotica/TipoBusqueda.vue';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';
import BotonTraspaso from '@/Components/Biotica/BtnTraspaso.vue';

const inputsFiltro = ref({});

const props = defineProps({
  columnas: { type: Array, required: true },
  datos: { type: Array, required: true },
  totalItems: { type: Number, required: true },
  itemsPerPage: { type: Number, default: 100 },
  endpoint: { type: String, required: true },
  idKey: { type: String, required: true },
  botCerrar: { type: Boolean, default: false },
  mostrarTraspaso: { type: Boolean, default: false },
  rowClassName: { type: Function, default: null },
  highlightCurrentRow: {
    type: Boolean,
    default: false
  }
});


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
  if (selectedRow.value) {
    emit('eliminar-item', selectedRow.value[props.idKey]);
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
  'cerrar'
]);

const accionModal = computed(() => props.botCerrar ? "cerrar" : "salir ")
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


const tableKey = ref(0);


const fetchData = async () => {
  try {
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
  } catch (error) {
    console.error(`Error en fetchData:`, error);
  }
};



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
          <div class="form-actions">
            <BotonTraspaso v-if="props.mostrarTraspaso" @traspasa="onRecuperaMarcado" />
            <NuevoButton @crear="onNuevo" />
            <EditarButton :disabled="!selectedRow" @editar="onEditarInterno" />
            <EliminarButton :disabled="!selectedRow" @eliminar="onEliminarInterno" />
            <BotonSalir />
          </div>
        </div>
      </div>
    </template>

    <div class="table-responsive ">
      <el-table :key="tableKey" ref="tableRefInterna" :highlight-current-row="props.highlightCurrentRow"
        :data="props.datos" :row-key="props.idKey" :row-class-name="props.rowClassName || rowClassNameInterno"
        @row-click="handleRowClickInterno" :border="true" height="550" @sort-change="handleSortChange">
        <slot name="expand-column"></slot>

        <el-table-column v-for="col in props.columnas" :key="col.prop" :prop="col.prop"
          :min-width="col.minWidth || '150'" :sortable="col.sortable ? 'custom' : false" :align="col.align || 'left'">
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
</style>