<script setup>
import { ref, watchEffect, onMounted, h } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import NuevoButton from '@/Components/Biotica/NuevoButton.vue';
import EditarButton from '@/Components/Biotica/EditarButton.vue';
import EliminarButton from '@/Components/Biotica/EliminarButton.vue';
import FiltrarPor from '@/Components/Biotica/FiltrarPorInput.vue';
import FormNombreComun from '@/Pages/Socat/Nombres/FormNombreComun.vue';
import { ElMessageBox, ElMessage } from 'element-plus';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';

const { datosNombreComun } = usePage().props;

const currentData = ref([]);
const currentPage = ref(1);
const totalItems = ref(0);
const itemsPerPage = 100;
const prevPageUrl = ref(null);
const nextPageUrl = ref(null);
const nombreComun = ref('');
const modalVisible = ref(false);
const nombreComunEditado = ref(null);
const sorting = ref({ column: null, order: null });
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

const fetchFilteredData = async () => {
    try {
        const response = await axios.get('/busca-nombre-comun', {
            params: {
                nombreComun: nombreComun.value,
                page: currentPage.value,
                sortBy: sorting.value.column,
                sortOrder: sorting.value.order,
                perPage: itemsPerPage,
            }
        });
        currentData.value = response.data.data;
        totalItems.value = response.data.totalItems;
        prevPageUrl.value = response.data.prevPageUrl;
        nextPageUrl.value = response.data.nextPageUrl;
    } catch (error) {
        console.error('Error al obtener los datos:', error);
    }
};

const handleSortChange = ({ prop, order }) => {
    sorting.value.column = prop;
    sorting.value.order = order === 'ascending' ? 'asc' : 'desc';
    if (!order) {
        sorting.value.column = null;
        sorting.value.order = null;
    }
    currentPage.value = 1;
    fetchFilteredData();
};


const nuevoNombreComun = () => {
    nombreComunEditado.value = null;
    modalVisible.value = true;
};


const editarNombreComun = (item) => {
    nombreComunEditado.value = item;
    modalVisible.value = true;
};


const buscarPor = () => {
    currentPage.value = 1;
    fetchFilteredData();
};


const handlePageChange = (page) => {
    currentPage.value = page;
    fetchFilteredData();
};

const cerrarModal = () => {
    modalVisible.value = false;
};


const handleFormSubmited = (datosDelFormulario) => {
    cerrarModal();

    const procederConGuardado = async () => {
        ElMessageBox.close();
        try {
            const payload = {
                NomComun: datosDelFormulario.NomComun,
                Observaciones: datosDelFormulario.Observaciones,
                Lengua: datosDelFormulario.Lengua,
            };
            if (datosDelFormulario.accionOriginal === 'crear') {
                await axios.post('/nombres-comunes', payload);
            } else {
                await axios.put(`/nombres-comunes/${datosDelFormulario.idParaEditar}`, payload);
            }
            await fetchFilteredData();
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
    
    const cancelarGuardado = () => { ElMessageBox.close();  };

    const mensaje = `¿Estás seguro de que deseas guardar los cambios para "${datosDelFormulario.NomComun || "nuevo registro"}"?`;
    
    ElMessageBox({
        title: 'Confirmar Guardado', 
        showConfirmButton: false, 
        showCancelButton: false, 
        customClass: 'message-box-diseno-limpio',
        message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
                h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
                h('div', { class: 'text-container' }, [h('p', null, mensaje)])
            ]),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, { onClick: cancelarGuardado }), 
                h(BotonAceptar, { onClick: procederConGuardado }),
            ])
        ])
    }).catch(() => {  });
};

const eliminarNombreComun = (idNomComun) => {
    const procederConEliminacion = async () => {
        try {
            ElMessageBox.close();
            const itemAEliminar = currentData.value.find(item => item.IdNomComun === idNomComun);
            const nombreItem = itemAEliminar ? `"${itemAEliminar.NomComun}"` : 'el registro';
            await axios.delete(`/nombres-comunes/${idNomComun}`);
            await fetchFilteredData();
            mostrarNotificacion("¡Eliminación Exitosa!", `El registro ${nombreItem} fue eliminado.`, "success");
        } catch (apiError) {
            mostrarNotificacion("Error al Eliminar", apiError.response?.data?.message || 'Ocurrió un error.', "error");
        }
    };
    const cancelarEliminacion = () => { ElMessageBox.close();  };
    const itemAEliminar = currentData.value.find(item => item.IdNomComun === idNomComun);
    const mensaje = `¿Está seguro de eliminar el nombre común "${itemAEliminar?.NomComun || 'seleccionado'}"? Esta acción no se puede revertir.`;
    
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

const props = defineProps({
    datosNombreComun: { type: Object, required: false },
});

watchEffect(() => {
    if (props.datosNombreComun) {
        fetchFilteredData();
    }
});

onMounted(() => {
    currentData.value = datosNombreComun?.data || [];
    totalItems.value = datosNombreComun?.totalItems || 0;
    currentPage.value = datosNombreComun?.currentPage || 1;
    prevPageUrl.value = datosNombreComun?.prevPageUrl || null;
    nextPageUrl.value = datosNombreComun?.nextPageUrl || null;
    if (!currentData.value.length) {
        fetchFilteredData();
    }
});
</script>

<template>
    <AppLayout title="NombreComun">
        
        <div class="app-container">

            <div class="page-title-header">
                <h1 class="page-main-title-class">Catálogo de nombres comunes</h1>
            </div>

            <div class="content-wrapper2">
            <div class="content-wrapper">
                <div class="table-wrapper">
                    <el-card class="box-card">
                        <template #header>
                            <div class="header-container">

                                <div class="right">
                                    <FiltrarPor v-model:busca="nombreComun" @update:busca="buscarPor()" />
                                </div>
                                <div class="left">
                                    <NuevoButton @crear="nuevoNombreComun" />
                                </div>
                            </div>
                        </template>
                        <div class="table-responsive">
                            <el-table :data="currentData" :border="true" height="400" style="width: 100%"
                                :highlight-current-row="true" @sort-change="handleSortChange">
                                <el-table-column type="expand">
                                    <template #default="{ row }">
                                        <div class="bg-gray-50 p-4 rounded-md">
                                            <p><strong>Id Nom Comun:</strong> {{ row.IdNomComun }}</p>
                                            <p><strong>Fecha de Captura:</strong> {{ row.FechaCaptura }}</p>
                                            <p><strong>Fecha de Modificación:</strong> {{ row.FechaModificacion }}</p>
                                            <p><strong>Id Original:</strong> {{ row.IdOriginal }}</p>
                                            <p><strong>Catalogo:</strong> {{ row.Catalogo }}</p>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="NomComun" label="Nombre Común" min-width="120" sortable="custom"
                                    align="center"></el-table-column>
                                <el-table-column prop="Observaciones" label="Observaciones" min-width="150"
                                    sortable="custom" align="center"></el-table-column>
                                <el-table-column prop="Lengua" label="Lengua" min-width="150" sortable="custom"
                                    align="center"></el-table-column>
                                <el-table-column label="Acciones" width="120">
                                    <template #default="{ row }">
                                        <div class="flex justify-around">
                                            <EditarButton @editar="editarNombreComun(row)" />
                                            <EliminarButton @eliminar="eliminarNombreComun(row.IdNomComun)" />
                                        </div>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </el-card>
                    <div v-if="totalItems > 0" class="pagination-container-wrapper">
                        <el-pagination :current-page="currentPage" :page-size="itemsPerPage" :total="totalItems"
                            @current-change="handlePageChange" layout="prev, pager, next, total" background
                            class="main-pagination-style">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </div>

            <FormNombreComun :visible="modalVisible" :nomComEdit="nombreComunEditado"
                :accion="nombreComunEditado ? 'editar' : 'crear'" @cerrar="cerrarModal"
                @formSubmited="handleFormSubmited" />

            <Teleport to="body">
                <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                    :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
                    @close="cerrarNotificacion" />
            </Teleport>
        </div>
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