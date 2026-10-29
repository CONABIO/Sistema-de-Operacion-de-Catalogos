<script setup>
import { ref, h, nextTick, watch, computed, defineProps, defineEmits } from 'vue';
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import axios from 'axios';
import { ElMessageBox, ElTableColumn, ElButton } from 'element-plus';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import FormObjetoExterno from './FormObjetoExterno.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import NuevoButton from "@/Components/Biotica/NuevoButton.vue";

const props = defineProps({
    isModal: {
        type: Boolean,
        default: false
    },
    traspaso: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['asociar', 'cerrar']);

const selectedRowId = ref(null);
const modalKey = ref(Date.now());

const selectedRow = computed(() => {
    return currentData.value.find(d => d.IdObjetoExterno === selectedRowId.value);
});

const asociarObjeto = () => {
    if (!selectedRow.value) return;
    emit('asociar', selectedRow.value);
};

const cerrarVentana = () => {
    emit('cerrar');
};

const manejarClickFila = (row) => {
    selectedRowId.value = row ? row.IdObjetoExterno : null;
    if (tablaRef.value) {
        tablaRef.value.selectedRow = row;
    }
    document.querySelectorAll('.fila-seleccionada-verde').forEach(tr => {
        tr.classList.remove('fila-seleccionada-verde');
    });
};



const seleccionarYResaltarFila = (itemOId) => {
    if (!itemOId) return;
    const id = typeof itemOId === 'object' ? itemOId.IdObjetoExterno : itemOId;

    setTimeout(() => {
        const fila = currentData.value.find(d => d.IdObjetoExterno === id);
        if (!fila) return;
        selectedRowId.value = id;
        if (tablaRef.value) {
            tablaRef.value.selectedRow = fila;
            tablaRef.value.setCurrentRow?.(fila);
            tablaRef.value.forzarFocoFilaVerde?.();
        }
        nextTick(() => {
            document.querySelectorAll('.fila-seleccionada-verde').forEach(tr => {
                tr.classList.remove('fila-seleccionada-verde');
            });
            const index = currentData.value.findIndex(d => d.IdObjetoExterno === id);
            const filasTr = document.querySelectorAll('.el-table__body tbody tr.el-table__row');
            let filaTr = null;
            if (index >= 0 && filasTr[index]) {
                filaTr = filasTr[index];
            } else {
                filaTr = Array.from(filasTr).find(tr => {
                    const txt = tr.textContent.toLowerCase();
                    return (fila.NombreObjeto && txt.includes(fila.NombreObjeto.toLowerCase())) ||
                           (fila.NombreSitio && txt.includes(fila.NombreSitio.toLowerCase()));
                });
            }
            if (filaTr) {
                const celda = filaTr.querySelector('td.el-table__cell') || filaTr.querySelector('td') || filaTr;
                celda.click();
                filaTr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }, 150);
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
  modalKey.value = Date.now();
  modalVisible.value = true;
};

const editarObjetoExterno = async (item) => {
    modalKey.value = Date.now();
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
    const esEdicion = objetoExternoEditado.value !== null;
    const idParaEditar = datosDelFormulario.IdObjetoExterno|| objetoExternoEditado.value?.IdObjetoExtern || selectedRowId.value;
    const nNuevo = (datosDelFormulario.NombreObjeto || "").trim().toLowerCase();
    const sNuevo = (datosDelFormulario.NombreSitio || "").trim().toLowerCase();
    const mensajeDuplicado = esEdicion? "El objeto externo que desea modificar ya existe, las modificaciones no se realizaron."  : "El objeto externo que desea ingresar ya existe.";
    const registroExistenteLocal = currentData.value.find(item => {
        const nItem = (item.NombreObjeto || "").trim().toLowerCase();
        const sItem = (item.NombreSitio || "").trim().toLowerCase();
        const esMismo = (nItem === nNuevo && sItem === sNuevo);
        return esEdicion ? (esMismo && item.IdObjetoExterno !== idParaEditar) : esMismo;
    });

     if (registroExistenteLocal) {
        cerrarModal();
        mostrarNotificacion("Aviso", mensajeDuplicado, "warning");
        seleccionarYResaltarFila(registroExistenteLocal);
        return;
    }

    const procederConGuardado = async () => {
        try {
            if (!esEdicion) {
                cerrarModal();
                const response = await axios.post('/objetos-externos', datosDelFormulario);
                mostrarNotificacion("Ingreso", "El objeto externo ha sido ingresado correctamente.", "success");
                const nuevoId = response.data.data?.IdObjetoExterno || response.data.IdObjetoExterno;
                if (nuevoId) await irAlRegistroEspecifico(nuevoId);
                else if (tablaRef.value) await tablaRef.value.fetchData();
            } else {
                cerrarModal();
                await axios.put(`/objetos-externos/${idParaEditar}`, datosDelFormulario);
                mostrarNotificacion("Modificación", "El objeto externo ha sido modificado correctamente.", "success");
                if (tablaRef.value) await tablaRef.value.fetchData();
                await nextTick();
                if (idParaEditar) {
                    selectedRowId.value = idParaEditar;
                    const filaActualizada = currentData.value.find(d => d.IdObjetoExterno === idParaEditar);
                    if (filaActualizada && tablaRef.value) {
                        tablaRef.value.selectedRow = filaActualizada;
                    }
                    tablaRef.value?.forzarFocoFilaVerde();
                }
            }
        } catch (error) {
            if (error.response?.status === 400 && error.response.data.idExistente) {
                cerrarModal();
                mostrarNotificacion("Aviso", mensajeDuplicado, "warning");
                await irAlRegistroEspecifico(error.response.data.idExistente);
            } else if (error.response?.status === 422) {
                let errorMsg = "Error:<ul>" + Object.values(error.response.data.errors).flat().map(e => `<li>${e}</li>`).join("") + "</ul>";
                mostrarNotificacionError("Error", errorMsg, "error", 0);
            } else {
                mostrarNotificacionError("Error", error.response?.data?.message || "No se pudo procesar la solicitud.", "error");
            }
        }
    };

    if (!esEdicion) {
        procederConGuardado();
    } else {
        const mensajeConfirmacion = `¿Estás seguro de guardar cambios para el objeto seleccionado?`;
        ElMessageBox({
            title: 'Confirmar modificación',
            showConfirmButton: false,
            showCancelButton: false,
            customClass: 'message-box-diseno-limpio',
            message: h('div', { class: 'custom-message-content' }, [
                h('div', { class: 'body-content' }, [
                    h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
                    h('div', { class: 'text-container' }, [h('p', null, mensajeConfirmacion)])
                ]),
                h('div', { class: 'footer-buttons' }, [
                    h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                    h(BotonAceptar, {
                        onClick: () => {
                            ElMessageBox.close();
                            procederConGuardado();
                        }
                    }),
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
    <LayoutCuerpo v-if="!props.isModal" :usar-app-layout="false" tituloPag="Objetos Externos"
        tituloArea="Catálogo de objetos externos">
        <div class="h-full flex flex-col">
            <TablaFiltrable ref="tablaRef" class="flex-grow" :columnas="columnasDefinidas" v-model:datos="currentData" :highlight-current-row="true"
                v-model:total-items="totalItems" endpoint="/busca-objeto-externo"  id-key="IdObjetoExterno"  :moduloSocat="'MnuCatObjExt'"
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
    </LayoutCuerpo>

    <div v-else class="modal-view-container">
        <div class="area-title" style="background-color: #d9e1eb; color: black; border-radius: 8px; height: 60px;">
            <h2 style="margin-top: 14px; margin-left: 20px;">Catálogo de objetos externos</h2>
        </div>
        <div class="h-full flex flex-col flex-grow">
            <TablaFiltrable ref="tablaRef" class="flex-grow" :columnas="columnasDefinidas" v-model:datos="currentData"
                v-model:total-items="totalItems" endpoint="/busca-objeto-externo" id-key="IdObjetoExterno" :moduloSocat="'MnuCatObjExt'"
                @editar-item="editarObjetoExterno" @eliminar-item="eliminarObjetoExterno"
                @nuevo-item="nuevoObjetoExterno" @row-click="manejarClickFila"
                :mostrarTraspaso="props.traspaso" @traspasaBiblio="asociarObjeto"
                :botCerrar="props.isModal" @cerrar="cerrarVentana" :highlight-current-row="true">

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
    </div>

    <Teleport to="body">
        <FormObjetoExterno
            :visible="modalVisible"
            :objeto-externo-edit="objetoExternoEditado"
            :key="modalKey"
            :accion="objetoExternoEditado ? 'editar' : 'crear'"
            @cerrar="cerrarModal"
            @formSubmited="handleFormSubmited" />

        <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
            :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
            @close="cerrarNotificacion" />
    </Teleport>
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

.el-table .current-row .cell,
.el-table .current-row td,
.el-table .fila-seleccionada-verde .cell,
.el-table .fila-seleccionada-verde td {
  color: #007bff !important;
  font-weight: bold;
}
</style>

<style scoped>
:deep(.el-table__body tr.current-row > td.el-table__cell),
:deep(.fila-seleccionada-verde) {
  background-color: #ddf6dd !important;
  --el-table-tr-bg-color: #ddf6dd !important;
}

.expand-content-detail { padding: 10px 15px; background-color: #fdfdfd; font-size: 13px; }

.modal-view-container {
    padding: 20px;
    height: 100%;
    display: flex;
    flex-direction: column;
    background-color: #fff;
}

.area-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}
</style>
