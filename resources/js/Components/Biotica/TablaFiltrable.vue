<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { ElTable, ElTableColumn, ElPagination, ElCard, ElIcon, ElButton, ElDropdown, ElDropdownMenu, ElDropdownItem, ElInput } from 'element-plus';
import { Search, CircleClose } from '@element-plus/icons-vue';
import NuevoButton from '@/Components/Biotica/NuevoButton.vue';
import EditarButton from '@/Components/Biotica/EditarButton.vue';
import EliminarButton from '@/Components/Biotica/EliminarButton.vue';
import TipoBusqueda from '@/Components/Biotica/TipoBusqueda.vue';

const props = defineProps({
  columnas: { type: Array, required: true },
  datos: { type: Array, required: true },
  totalItems: { type: Number, required: true },
  itemsPerPage: { type: Number, default: 100 },
  endpoint: { type: String, required: true },
  idKey: { type: String, required: true }
});

const emit = defineEmits([
  'update:datos',
  'update:totalItems',
  'editar-item',
  'eliminar-item',
  'nuevo-item',
  'row-dblclick'
]);

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

const fetchData = async () => {
  try {
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
    emit('update:datos', response.data.data || []);
    emit('update:totalItems', response.data.total || response.data.totalItems || 0);
  } catch (error) {
    console.error(`Error en TablaFiltrable (${props.endpoint}):`, error);
    emit('update:datos', []);
    emit('update:totalItems', 0);
  }
};

let debounceTimer;
const onFiltroInput = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    currentPage.value = 1;
    fetchData();
  }, 500);
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
const onRowDblClick = (row) => emit('row-dblclick', row);
const onNuevo = () => emit('nuevo-item');

watch(tipoDeBusqueda, () => {
    onFiltroInput(); 
});

onMounted(fetchData);
defineExpose({ fetchData });
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
          <slot name="header-actions">
            <NuevoButton @crear="onNuevo" />
          </slot>
        </div>
      </div>
    </template>

    <div class="table-responsive " >
      <el-table :data="props.datos" :border="true" height="550" @sort-change="handleSortChange" @row-dblclick="onRowDblClick">
        <slot name="expand-column"></slot>
        
        <el-table-column
          v-for="col in props.columnas"
          :key="col.prop"
          :prop="col.prop"
          :min-width="col.minWidth || '150'"
          :sortable="col.sortable ? 'custom' : false"
          :align="col.align || 'left'"
        >
          <template #header>
            <div class="custom-header">
              <span>{{ col.label }}</span>
              <el-dropdown v-if="col.filtrable" trigger="click" :hide-on-click="false">
                <el-button @click.stop circle size="small" class="header-filter-button" :type="filtros[col.prop] ? 'primary' : ''" style="margin-left: 10px;">
                  <el-icon><Search /></el-icon>
                </el-button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item class="filter-dropdown-item">
                      <div @click.stop>
                        <el-input
                          v-model="filtros[col.prop]"
                          :placeholder="`Filtrar por ${col.label}`"
                          @input="onFiltroInput"
                          clearable
                          @clear="onFiltroInput"
                        >
                        </el-input>
                      </div>
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Acciones" width="120" align="center">
          <template #default="{ row }">
            <div class="action-buttons-container">
              <slot name="acciones" :fila="row">
                <EditarButton @editar="onEditar(row)" />
                <EliminarButton @eliminar="onEliminar(row[props.idKey])" />
              </slot>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <div v-if="totalItems > 0" class="pagination-container-wrapper">
      <el-pagination
        :current-page="currentPage"
        :page-size="props.itemsPerPage"
        :total="props.totalItems"
        @current-change="handlePageChange"
        layout="prev, pager, next, total"
        background
        class="main-pagination-style"
      >
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
:deep(.el-table .el-table__row:hover > td.el-table__cell) {
    background-color: #f5f7fa !important;
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

.custom-header > span {
  flex-grow: 1;
  text-align: left;
}
.header-filter-button {
  flex-shrink: 0;
}
</style>