<script setup>
import { ref, h } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { ElMessageBox } from 'element-plus';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import FormTipoDistribucion from '@/Pages/Socat/TiposDistribucion/FormTipoDistribucion.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import NuevoButton from '@/Components/Biotica/NuevoButton.vue';

const tablaRef = ref(null);
const currentData = ref([]);
const totalItems = ref(0);
const modalVisible = ref(false);
const tipoDistribucionEditado = ref(null);

const columnasDefinidas = ref([
    { prop: 'Descripcion', label: 'Descripción', minWidth: '120', sortable: true, filtrable: true, align: 'left' }
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

const nuevoTipoDistribucion = () => {
    tipoDistribucionEditado.value = null;
    modalVisible.value = true;
};
const editarTipoDistribucion = (item) => {
    tipoDistribucionEditado.value = item;
    modalVisible.value = true;
};
const cerrarModal = () => {
    modalVisible.value = false;
};

const handleFormSubmited = (datosDelFormulario) => {
    cerrarModal();
    const procederConGuardado = async () => {
        ElMessageBox.close();
        try {
            const payload = { Descripcion: datosDelFormulario.Descripcion };
            if (datosDelFormulario.accionOriginal === 'crear') {
                await axios.post('/tipos-distribucion', payload);
            } else {
                await axios.put(`/tipos-distribucion/${datosDelFormulario.idParaEditar}`, payload);
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
    const mensaje = `¿Estás seguro de que deseas guardar los cambios para "${datosDelFormulario.Descripcion || "nuevo registro"}"?`;
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

const eliminarTipoDistribucion = (idTipoDistribucion) => {
    const procederConEliminacion = async () => {
        try {
            ElMessageBox.close();
            const itemAEliminar = currentData.value.find(item => item.IdTipoDistribucion === idTipoDistribucion);
            const nombreItem = itemAEliminar ? `"${itemAEliminar.Descripcion}"` : 'el registro';
            await axios.delete(`/tipos-distribucion/${idTipoDistribucion}`);
            if (tablaRef.value) {
                tablaRef.value.fetchData();
            }
            mostrarNotificacion("¡Eliminación Exitosa!", `El registro ${nombreItem} fue eliminado.`, "success");
        } catch (apiError) {
            mostrarNotificacion("Error al Eliminar", apiError.response?.data?.message || 'Ocurrió un error.', "error");
        }
    };
    const cancelarEliminacion = () => { ElMessageBox.close(); };
    const itemAEliminar = currentData.value.find(item => item.IdTipoDistribucion === idTipoDistribucion);
    const mensaje = `¿Está seguro de eliminar el tipo de distribución "${itemAEliminar?.Descripcion || 'seleccionado'}"? Esta acción no se puede revertir.`;
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
    <AppLayout title="Catálogo de tipos de distribución">
        <div class="app-container">
            <div class="page-title-header">
                <h1 class="page-main-title-class"> Catálogo de tipos de distribución</h1>
            </div>
            <div class="content-wrapper2">
                <div class="content-wrapper">
                    <div class="table-wrapper">
                        <TablaFiltrable
                            ref="tablaRef"
                            :columnas="columnasDefinidas"
                            v-model:datos="currentData"
                            v-model:total-items="totalItems"
                            endpoint="/busca-tipo-distribucion"
                            id-key="IdTipoDistribucion"
                            @editar-item="editarTipoDistribucion"
                            @eliminar-item="eliminarTipoDistribucion"
                            @nuevo-item="nuevoTipoDistribucion"
                        >
                            
                            <template #expand-column>
                                <el-table-column type="expand">
                                    <template #default="{ row }">
                                        <div class="expand-content-detail">
                                            <p><strong>Id del tipo de distribución:</strong> {{ row.IdTipoDistribucion }}</p>
                                            <p><strong>Fecha de captura:</strong> {{ row.FechaCaptura }}</p>
                                            <p><strong>Fecha de modificación:</strong> {{ row.FechaModificacion }}</p>
                                        </div>
                                    </template>
                                </el-table-column>
                            </template>
                        </TablaFiltrable>
                    </div>
                </div>
            </div>

            <FormTipoDistribucion :visible="modalVisible" :tipoDistEdit="tipoDistribucionEditado"
                :accion="tipoDistribucionEditado ? 'editar' : 'crear'" @cerrar="cerrarModal"
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