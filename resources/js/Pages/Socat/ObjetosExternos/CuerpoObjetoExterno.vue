<script setup>
import { ref, h, nextTick, watch  } from 'vue';
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import axios from 'axios';
import { ElMessageBox, ElTableColumn } from 'element-plus';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import FormObjetoExterno from './FormObjetoExterno.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import NuevoButton from "@/Components/Biotica/NuevoButton.vue";

const selectedRowId = ref(null);
// VARIABLE NUEVA PARA FORZAR EL REFRESCO
const modalKey = ref(Date.now());

const manejarClickFila = (row) => {
    selectedRowId.value = row.IdObjetoExterno;
    if (tablaRef.value) {
        tablaRef.value.selectedRow = row;
    }
};

const tableRowClassName = ({ row }) => {
    if (row.IdObjetoExterno === selectedRowId.value) {
        return 'fila-seleccionada-verde';
    }
    return '';
};

const irAlRegistroEspecifico = async (idEncontrado) => {
    try {
        if (tablaRef.value) {
            tablaRef.value.limpiarTodosLosFiltros();
        }
        selectedRowId.value = null;
        if (tablaRef.value) tablaRef.value.selectedRow = null;
        const currentSort = tablaRef.value?.sorting || { prop: 'NombreObjeto', order: 'asc' };
        const resPagina = await axios.post('/objetos-externos/obtener-pagina', {
            id: idEncontrado,
            perPage: 100,
            sortBy: currentSort.prop || 'NombreObjeto',
            sortOrder: currentSort.order || 'asc'
        });
        const paginaDestino = resPagina.data.page;
        if (tablaRef.value) {
            await tablaRef.value.irAPagina(paginaDestino);
            await tablaRef.value.fetchData();
            await nextTick();
            setTimeout(() => {
                const fila = currentData.value.find(d => d.IdObjetoExterno === idEncontrado);
                if (fila) {
                    selectedRowId.value = idEncontrado;
                    tablaRef.value.selectedRow = fila;
                    nextTick(() => {
                        tablaRef.value.forzarFocoFilaVerde();
                    });
                } else {
                    console.warn("No se encontró la fila en la página actual tras el fetch.");
                }
            }, 250);
        }
    } catch (err) {
        console.error("Error al redirigir:", err);
    }
};


const tablaRef = ref(null);
const currentData = ref([]);
const totalItems = ref(0);
const modalVisible = ref(false);
const objetoExternoEditado = ref(null);

const columnasDefinidas = ref([
    { prop: 'NombreObjeto', label: 'Nombre del archivo', minWidth: '200', sortable: true, filtrable: true, align: 'left' },
    { prop: 'NombreSitio', label: 'Nombre del sitio', minWidth: '200', sortable: true, filtrable: true, align: 'left' },
    { prop: 'extension', label: 'Extensión', minWidth: '120', sortable: true, filtrable: true },
    { prop: 'tipo', label: 'Tipo', minWidth: '200', sortable: true, filtrable: true }
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
    notificacionDuracion.value = 5000;
    notificacionVisible.value = true;
};

const cerrarNotificacion = () => {
    notificacionVisible.value = false;
};

const nuevoObjetoExterno = () => {
  objetoExternoEditado.value = null;
  // CAMBIAMOS LA KEY PARA QUE VUE RE-RENDERICE EL MODAL
  modalKey.value = Date.now();
  modalVisible.value = true;
  console.log("Entrando")
};


const editarObjetoExterno = async (item) => {
    await nextTick();
    objetoExternoEditado.value = item;
    modalVisible.value = true;
};

const cerrarModal = () => {
    modalVisible.value = false;
};

watch(modalVisible, (newVal) => {
    if (!newVal) {
        setTimeout(() => {
            objetoExternoEditado.value = null;
        }, 300);
    }
});



const handleFormSubmited = (datosDelFormulario) => {
    cerrarModal();
    const esEdicion = objetoExternoEditado.value !== null;
    const idParaEditar = datosDelFormulario.IdObjetoExterno || (objetoExternoEditado.value?.IdObjetoExterno);

    const nNuevo = (datosDelFormulario.NombreObjeto || "").trim().toLowerCase();
    const sNuevo = (datosDelFormulario.NombreSitio || "").trim().toLowerCase();

    const mensajeDuplicado = esEdicion
        ? "El objeto externo que desea modificar ya existe, las modificaciones no se realizaron."
        : "El objeto externo que desea ingresar ya existe.";

    // Validación local: Solo bloquea si coinciden Nombre Y Sitio
    const registroExistenteLocal = currentData.value.find(item => {
        const nItem = (item.NombreObjeto || "").trim().toLowerCase();
        const sItem = (item.NombreSitio || "").trim().toLowerCase();
        const esMismo = (nItem === nNuevo && sItem === sNuevo);
        return esEdicion ? (esMismo && item.IdObjetoExterno !== idParaEditar) : esMismo;
    });

    if (registroExistenteLocal) {
        selectedRowId.value = registroExistenteLocal.IdObjetoExterno;
        if (tablaRef.value) {
            tablaRef.value.selectedRow = registroExistenteLocal;
            tablaRef.value.forzarFocoFilaVerde();
        }
        mostrarNotificacion("Aviso", mensajeDuplicado, "warning");
        return;
    }

    const procederConGuardado = async () => {
        try {
            if (!esEdicion) {
                const response = await axios.post('/objetos-externos', datosDelFormulario);
                mostrarNotificacion("Ingreso", "El objeto externo ha sido ingresado correctamente.", "success");
                const nuevoId = response.data.data?.IdObjetoExterno || response.data.IdObjetoExterno;
                if (nuevoId) await irAlRegistroEspecifico(nuevoId);
                else if (tablaRef.value) await tablaRef.value.fetchData();
            } else {
                await axios.put(`/objetos-externos/${idParaEditar}`, datosDelFormulario);
                mostrarNotificacion("Modificación", "El objeto externo ha sido modificado correctamente.", "success");
                if (tablaRef.value) await tablaRef.value.fetchData();
                await nextTick();
                tablaRef.value.forzarFocoFilaVerde();
            }
        } catch (error) {
            if (error.response?.status === 400 && error.response.data.idExistente) {
                mostrarNotificacion("Aviso", mensajeDuplicado, "warning");
                await irAlRegistroEspecifico(error.response.data.idExistente);
            } else if (error.response?.status === 422) {
                let errorMsg = "Error:<ul>" + Object.values(error.response.data.errors).flat().map(e => `<li>${e}</li>`).join("") + "</ul>";
                mostrarNotificacionError("Error", errorMsg, "error", 0);
            } else {
                mostrarNotificacion("Error", "No se pudo procesar la solicitud.", "error");
            }
        }
    };

    if (!esEdicion) {
        procederConGuardado();
    } else {
        const mensajeConfirmacion = `¿Estás seguro de guardar cambios para el objeto seleccionado?`;
        ElMessageBox({
            title: 'Confirmar modificación',
            showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
            message: h('div', { class: 'custom-message-content' }, [
                h('div', { class: 'body-content' }, [
                    h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
                    h('div', { class: 'text-container' }, [h('p', null, mensajeConfirmacion)])
                ]),
                h('div', { class: 'footer-buttons' }, [
                    h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                    h(BotonAceptar, { onClick: () => { ElMessageBox.close(); procederConGuardado(); } }),
                ])
            ])
        }).catch(() => { });
    }
};

const eliminarObjetoExterno = (idObjeto) => {
    const procederConEliminacion = async () => {
        try {
            ElMessageBox.close();
            const response = await axios.delete(`/objetos-externos/${idObjeto}`);
            if (tablaRef.value) tablaRef.value.fetchData();
            mostrarNotificacion("Eliminación", response.data.message, "success");
        } catch (error) {
            const mensajeError = error.response?.data?.message || 'No se pudo eliminar el registro.';
            const status = error.response?.status;

            if (status === 422) {
                mostrarNotificacion("Aviso", mensajeError, "warning");
            } else {
                mostrarNotificacionError("Error", mensajeError, "error");
            }
        }
    };

    const mensaje = `¿Está seguro de eliminar el objeto seleccionado? Esta acción no se puede revertir.`;

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
                h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                h(BotonAceptar, { onClick: procederConEliminacion }),
            ])
        ])
    }).catch(() => { });
};


</script>

<template>
    <LayoutCuerpo :usar-app-layout="false" tituloPag="Objetos Externos"
        tituloArea="Catálogo de objetos externos">
        <div class="h-full flex flex-col">
            <TablaFiltrable ref="tablaRef" class="flex-grow" :columnas="columnasDefinidas" v-model:datos="currentData"
                v-model:total-items="totalItems" endpoint="/busca-objeto-externo"  id-key="IdObjetoExterno"
                @editar-item="editarObjetoExterno" @eliminar-item="eliminarObjetoExterno"
                @nuevo-item="nuevoObjetoExterno"  @row-click="manejarClickFila">

                <template #header-actions>
                    <NuevoButton @crear="nuevoObjetoExterno" />
                </template>

                <template #extra-columns>
                    <el-table-column label="Extensión" min-width="100">
                        <template #default="{ row }"><span>{{ row.extension }}</span></template>
                    </el-table-column>
                    <el-table-column label="Tipo" min-width="200">
                        <template #default="{ row }"><span>{{ row.tipo }}</span></template>
                    </el-table-column>
                </template>

                <template #expand-column>
                    <el-table-column type="expand">
                        <template #default="{ row }">
                            <div class="expand-content-detail">
                                <p><strong>ID:</strong> {{ row.IdObjetoExterno }}</p>
                                <p><strong>Ruta:</strong> {{ row.Ruta }}</p>
                                <p><strong>Sitio:</strong> {{ row.NombreSitio }}</p>
                            </div>
                        </template>
                    </el-table-column>
                </template>
            </TablaFiltrable>
        </div>

        <Teleport to="body">
            <!-- LA KEY AHORA USA EL TIMESTAMP SI ES NUEVO PARA FORZAR EL RESET -->
            <FormObjetoExterno
                :visible="modalVisible"
                :objeto-externo-edit="objetoExternoEditado"
                :key="objetoExternoEditado ? objetoExternoEditado.IdObjetoExterno : modalKey"
                :accion="objetoExternoEditado ? 'editar' : 'crear'"
                @cerrar="cerrarModal"
                @formSubmited="handleFormSubmited" />

            <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
                @close="cerrarNotificacion" />
        </Teleport>
    </LayoutCuerpo>
</template>

<style>
.message-box-diseno-limpio .el-message-box__header { border-bottom: none; }
.message-box-diseno-limpio .el-message-box__content { padding: 10px 20px 20px 20px; }
.custom-message-content { display: flex; flex-direction: column; }
.body-content { display: flex; align-items: center; gap: 15px; }
.text-container p { margin: 0; line-height: 1.5; color: #606266; }
.custom-warning-icon-container { flex-shrink: 0; }
.custom-warning-circle {
    width: 30px; height: 30px; border-radius: 90%;
    background-color: #F56C6C; color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; font-weight: bold; line-height: 1;
}
.footer-buttons { display: flex; justify-content: flex-end; gap: 10px; margin-top: 35px; }

.el-table .fila-seleccionada-verde .cell,
.el-table .fila-seleccionada-verde td {
  color: #007bff !important;
  font-weight: bold;
}
</style>

<style scoped>
:deep(.fila-seleccionada-verde) {
  background-color: #ddf6dd !important;
  --el-table-tr-bg-color: #ddf6dd !important;
}
.expand-content-detail { padding: 10px 15px; background-color: #fdfdfd; font-size: 13px; }
</style>
