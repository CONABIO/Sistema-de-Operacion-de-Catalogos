<script setup>
import { ref, h } from 'vue';
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import axios from 'axios';
import { ElMessageBox, ElTableColumn } from 'element-plus';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import FormNombreComun from '@/Pages/Socat/Nombres/FormNombreComun.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';


const tablaRef = ref(null);
const currentData = ref([]);
const totalItems = ref(0);
const modalVisible = ref(false);
const nombreComunEditado = ref(null);

const columnasDefinidas = ref([
    { prop: 'NomComun', label: 'Nombre común', minWidth: '120', sortable: true, filtrable: true, align: 'left' },
    { prop: 'Lengua', label: 'Lengua', minWidth: '150', sortable: true, filtrable: true, align: 'left' },
    { prop: 'Observaciones', label: 'Observaciones', minWidth: '150', sortable: true, filtrable: true, align: 'left' },
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

const nuevoNombreComun = () => {
    nombreComunEditado.value = null;
    modalVisible.value = true;
};
const editarNombreComun = (item) => {
    nombreComunEditado.value = item;
    modalVisible.value = true;
};
const cerrarModal = () => {
    modalVisible.value = false;
};

const handleFormSubmited = (datosDelFormulario) => {
    cerrarModal();

    const nombreNormalizado = datosDelFormulario.NomComun.trim().toLowerCase();
    const lenguaNormalizada = datosDelFormulario.Lengua.trim().toLowerCase();
    const esEdicion = datosDelFormulario.accionOriginal === 'editar';
    const registroExistente = currentData.value.find(item => {  
    const mismoNombre = item.NomComun.trim().toLowerCase() === nombreNormalizado;
    const mismaLengua = item.Lengua.trim().toLowerCase() === lenguaNormalizada;
        return esEdicion ? (mismoNombre && mismaLengua && item.IdNomComun !== datosDelFormulario.idParaEditar) : (mismoNombre && mismaLengua);
    });

    if (registroExistente) {
        mostrarNotificacionError(
            "Aviso",
            `Ya existe un nombre común registrado con el mismo nombre, no se realizarán los cambios solicitados.`,
            "error"
        );
        return; 
    }



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
                mostrarNotificacion("Ingreso", "La información ha sido ingresada correctamente.", "success");
            } else {
                await axios.put(`/nombres-comunes/${datosDelFormulario.idParaEditar}`, payload);
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
        const mensaje = `¿Estás seguro de que deseas guardar los cambios para "${datosDelFormulario.NomComun || "nuevo registro"}"?`;
        ElMessageBox({
            title: 'Confirmar modificación', showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
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
        }).catch(() => { });
    }

};

const eliminarNombreComun = (idNomComun) => {
    const procederConEliminacion = async () => {
        const nombreItem = itemAEliminar ? `"${itemAEliminar.NomComun}"` : 'el registro';
        try {
            ElMessageBox.close();
            const itemAEliminar = currentData.value.find(item => item.IdNomComun === idNomComun);
            await axios.delete(`/nombres-comunes/${idNomComun}`);
            if (tablaRef.value) {
                tablaRef.value.fetchData();
            }
            mostrarNotificacion("Eliminación exitosa", `El nombre común ${nombreItem} fue eliminado correctamente.`, "success");
        } catch (apiError) {
            mostrarNotificacionError('Aviso', `El nombre común ${nombreItem} no se puede eliminar. Este nombre común esta asociado.`, 'success');

        }
    };
    const cancelarEliminacion = () => { ElMessageBox.close(); };
    const itemAEliminar = currentData.value.find(item => item.IdNomComun === idNomComun);
    const mensaje = `¿Está seguro de eliminar el nombre común "${itemAEliminar?.NomComun || 'seleccionado'}"? Esta acción no se puede revertir.`;
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
</script>

<template>
    <LayoutCuerpo :usar-app-layout="false" tituloPag="Nombres Comunes" tituloArea="Catálogo de nombres comunes">
        <div class="h-full flex flex-col">
            <TablaFiltrable ref="tablaRef" class="flex-grow" :columnas="columnasDefinidas" v-model:datos="currentData"
                v-model:total-items="totalItems" endpoint="/busca-nombre-comun" id-key="IdNomComun"
                @editar-item="editarNombreComun" @eliminar-item="eliminarNombreComun" @nuevo-item="nuevoNombreComun">

                <template #expand-column>
                    <el-table-column type="expand">
                        <template #default="{ row }">
                            <div class="expand-content-detail">
                                <p><strong>IdNomComun:</strong> {{ row.IdNomComun }}</p>
                                <p><strong>IdOriginal:</strong> {{ row.IdOriginal }}</p>
                                <p><strong>Catalogo:</strong> {{ row.Catalogo }}</p>
                                <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                                <p><strong>FechaModificacion:</strong> {{ row.FechaModificacion }}</p>
                            </div>
                        </template>
                    </el-table-column>
                </template>
            </TablaFiltrable>
        </div>

        <FormNombreComun :visible="modalVisible" :nomComEdit="nombreComunEditado"
            :accion="nombreComunEditado ? 'editar' : 'crear'" @cerrar="cerrarModal"
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