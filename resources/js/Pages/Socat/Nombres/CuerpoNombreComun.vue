<script setup>
import { ref, h, nextTick } from 'vue';
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import axios from 'axios';
import { ElMessageBox, ElTableColumn } from 'element-plus';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import FormNombreComun from '@/Pages/Socat/Nombres/FormNombreComun.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';


const selectedRowId = ref(null);

const manejarClickFila = (row) => {
    selectedRowId.value = row ? row.IdNomComun : null;
};

const tableRowClassName = ({ row }) => {
    if (row.IdNomComun === selectedRowId.value) {
        return 'fila-seleccionada-verde';
    }
    return '';
};

const irAlRegistroEspecifico = async (idEncontrado) => {
    try {
        selectedRowId.value = null;
        if (tablaRef.value) {
            tablaRef.value.selectedRow = null;
            tablaRef.value.limpiarTodosLosFiltros();
        }
        const currentSort = tablaRef.value?.sorting || { prop: 'NomComun', order: 'asc' };
        const resPagina = await axios.post('/nombres-comunes/obtener-pagina', {
            id: idEncontrado, 
            perPage: 100,
            sortBy: currentSort.prop || 'NomComun',
            sortOrder: currentSort.order || 'asc'
        });
        const paginaDestino = resPagina.data.page;
        if (tablaRef.value) {
            await tablaRef.value.irAPagina(paginaDestino);
            await nextTick();
            const fila = currentData.value.find(d => d.IdNomComun === idEncontrado);
            if (fila) {
                selectedRowId.value = idEncontrado; 
                tablaRef.value.selectedRow = fila;
                setTimeout(() => {
                    tablaRef.value.forzarFocoFilaVerde();
                }, 200);
            }
        }
    } catch (err) {
        console.error("Error al redirigir:", err);
    }
};

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
    const esEdicion = datosDelFormulario.accionOriginal === 'editar';
    const mensajeDuplicado = esEdicion 
        ? "El nombre común que desea modificar ya existe, las modificaciones no se realizaron." 
        : "El nombre común que desea ingresar ya existe.";

    const registroExistenteLocal = currentData.value.find(item => {  
        const mismoNombre = item.NomComun.trim().toLowerCase() === datosDelFormulario.NomComun.trim().toLowerCase();
        const mismaLengua = item.Lengua.trim().toLowerCase() === datosDelFormulario.Lengua.trim().toLowerCase();
        
        return esEdicion 
            ? (mismoNombre && mismaLengua && item.IdNomComun !== datosDelFormulario.idParaEditar) 
            : (mismoNombre && mismaLengua);
    });

    if (registroExistenteLocal) {
        selectedRowId.value = registroExistenteLocal.IdNomComun;
        if (tablaRef.value) {
            tablaRef.value.selectedRow = registroExistenteLocal;
            tablaRef.value.forzarFocoFilaVerde();
        }
        mostrarNotificacion("Aviso", mensajeDuplicado, "warning");
        return; 
    }

    const procederConGuardado = async () => {
        try {
            const payload = {
                NomComun: datosDelFormulario.NomComun,
                Observaciones: datosDelFormulario.Observaciones,
                Lengua: datosDelFormulario.Lengua,
            };

            if (datosDelFormulario.accionOriginal === 'crear') {
                const response = await axios.post('/nombres-comunes', payload);
                mostrarNotificacion("Ingreso", "El nombre común ha sido ingresado correctamente.", "success");
                const nuevoId = response.data.data?.IdNomComun;
                if (nuevoId) await irAlRegistroEspecifico(nuevoId);
            } else {
                await axios.put(`/nombres-comunes/${datosDelFormulario.idParaEditar}`, payload);
                mostrarNotificacion("Modificación", "El nombre común ha sido modificado correctamente.", "success");
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
                mostrarNotificacion("Error", errorMsg, "error", 0);
            } else {
                mostrarNotificacion("Error", "No se pudo procesar la solicitud.", "error");
            }
        }
    };

    if (datosDelFormulario.accionOriginal === 'crear') {
        procederConGuardado();
    } else {
        const mensajeConfirmacion = `¿Estás seguro de guardar cambios para "${datosDelFormulario.NomComun}"?`;
        ElMessageBox({
            title: 'Confirmar modificación', showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
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
            mostrarNotificacion("Eliminación", `El nombre común ${nombreItem} fue eliminado correctamente.`, "success");
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
            <TablaFiltrable ref="tablaRef" class="flex-grow" :columnas="columnasDefinidas" v-model:datos="currentData" :row-class-name="tableRowClassName"
                v-model:total-items="totalItems" endpoint="/busca-nombre-comun" id-key="IdNomComun" @row-click="manejarClickFila"  :highlight-current-row="false"  
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

.el-table .fila-seleccionada-verde {
    background-color: #ddf6dd !important;
}


.el-table .fila-seleccionada-verde {
    --el-table-tr-bg-color: #ddf6dd !important;
}

.el-table .fila-seleccionada-verde:hover>td.el-table__cell {
    background-color: #a3e4d7 !important;
}
</style>

<style scoped>
.expand-content-detail {
    padding: 10px 15px;
    background-color: #fdfdfd;
    font-size: 13px;
}


el-table .fila-seleccionada-verde {
    --el-table-tr-bg-color: #ddf6dd !important;
}

.el-table .fila-seleccionada-verde:hover>td.el-table__cell {
    background-color: #cce8cc !important;
}


.tabla-grupos {
    --el-table-current-row-bg-color: #ddf6dd !important;
    --el-table-row-hover-bg-color: #cbf0cb !important;
}

:deep(.el-table__body tr.current-row > td.el-table__cell) {
    background-color: #ddf6dd !important;
}
</style>