<script setup>
import { ref, h } from 'vue';
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import axios from 'axios';
import { ElMessageBox, ElTableColumn } from 'element-plus';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import FormTipoDistribucion from '@/Pages/Socat/TiposDistribucion/FormTipoDistribucion.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';

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

    const descNormalizada = datosDelFormulario.Descripcion.trim().toLowerCase();
    const esEdicion = datosDelFormulario.accionOriginal === 'editar';
    const registroExistente = currentData.value.find(item => {
        const mismaDescripcion = item.Descripcion.trim().toLowerCase() === descNormalizada;
        return esEdicion ? (mismaDescripcion && item.IdTipoDistribucion !== datosDelFormulario.idParaEditar) : mismaDescripcion;
    });

    if (registroExistente) {
        mostrarNotificacionError(
            "Aviso",
            `Ya existe un tipo de distribución registrado con la misma descripción, no se realizarán los cambios solicitados.`,
            "error"
        );
        return; 
    }


    const procederConGuardado = async () => {
        ElMessageBox.close();
        try {
            const payload = { Descripcion: datosDelFormulario.Descripcion };
            if (datosDelFormulario.accionOriginal === 'crear') {
                await axios.post('/tipos-distribucion', payload);
                mostrarNotificacion("Ingreso", "La información ha sido ingresada correctamente.", "success");
            } else {
                await axios.put(`/tipos-distribucion/${datosDelFormulario.idParaEditar}`, payload);
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
        const mensaje = `¿Estás seguro de que deseas guardar los cambios para "${datosDelFormulario.Descripcion || "nuevo registro"}"?`;
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

const eliminarTipoDistribucion = (idTipoDistribucion) => {
    const procederConEliminacion = async () => {
        const nombreItem = itemAEliminar ? `"${itemAEliminar.Descripcion}"` : 'el registro';
        try {
            ElMessageBox.close();
            const itemAEliminar = currentData.value.find(item => item.IdTipoDistribucion === idTipoDistribucion);
            await axios.delete(`/tipos-distribucion/${idTipoDistribucion}`);
            if (tablaRef.value) {
                tablaRef.value.fetchData();
            }
            mostrarNotificacion("Eliminación exitosa", `El tipo de distribución ${nombreItem} fue eliminado correctamente.`, "success");
        } catch (apiError) {
            mostrarNotificacionError('Aviso', `El tipo de distribución ${nombreItem} no se puede eliminar. Este tipo de distribución esta asociado.`, 'success');

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
    <LayoutCuerpo :usar-app-layout="false" tituloPag="Tipos de Distribución"
        tituloArea="Catálogo de tipos de distribución">
        <div class="h-full flex flex-col">
            <TablaFiltrable ref="tablaRef" class="flex-grow" :columnas="columnasDefinidas" v-model:datos="currentData"
                v-model:total-items="totalItems" endpoint="/busca-tipo-distribucion" id-key="IdTipoDistribucion"
                @editar-item="editarTipoDistribucion" @eliminar-item="eliminarTipoDistribucion"
                @nuevo-item="nuevoTipoDistribucion">
                <template #expand-column>
                    <el-table-column type="expand">
                        <template #default="{ row }">
                            <div class="expand-content-detail">
                                <p><strong>IdTipoDistribucion:</strong> {{ row.IdTipoDistribucion }}</p>
                                <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                                <p><strong>FechaModificacion:</strong> {{ row.FechaModificacion }}</p>
                            </div>
                        </template>
                    </el-table-column>
                </template>
            </TablaFiltrable>
        </div>

        <FormTipoDistribucion :visible="modalVisible" :tipoDistEdit="tipoDistribucionEditado"
            :accion="tipoDistribucionEditado ? 'editar' : 'crear'" @cerrar="cerrarModal"
            @formSubmited="handleFormSubmited" />

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
.expand-content-detail {
    padding: 10px 15px;
    background-color: #fdfdfd;
    font-size: 13px;
}
</style>