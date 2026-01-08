<script setup>
import { ref, h, computed } from 'vue';
import axios from 'axios';
import { ElMessageBox, ElButton } from 'element-plus';
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import FormGrupoTaxonomico from '@/Pages/Socat/Grupos/FormGrupoTaxonomico.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';

const props = defineProps({
    isModal: Boolean,
    datosGrupo: Object
});


const selectedRowId = ref(null);

const manejarClickFila = (row) => {
    selectedRowId.value = row.IdGrupoSCAT;
};

const asociarSeleccionado = () => {
    if (!selectedRowId.value) {
        mostrarNotificacion("Aviso", "Por favor, seleccione un grupo de la lista para asociar.", "warning");
        return;
    }
    const row = currentData.value.find(r => r.IdGrupoSCAT === selectedRowId.value);
    if (row) {
        seleccionarGrupo(row);
    }
};

const cerrarVentana = () => {
    window.parent.postMessage({
        type: 'cerrarModal'
    }, '*');
};

const tablaRef = ref(null);
const currentData = ref([]);
const totalItems = ref(0);
const modalVisible = ref(false);
const grupoEditado = ref(null);
const guardandoDatosServer = ref(false);

const columnasDefinidas = ref([
    { prop: 'GrupoSCAT', label: 'Grupo SCAT', minWidth: '120', sortable: true, filtrable: true, align: 'left' },
    { prop: 'GrupoAbreviado', label: 'Grupo abreviado', minWidth: '150', sortable: true, filtrable: true, align: 'left' },
    { prop: 'GrupoSNIB', label: 'Grupo SNIB', minWidth: '150', sortable: true, filtrable: true, align: 'left' }
]);

const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);

const mostrarNotificacion = (titulo, mensaje, tipo = "info", duracion = 5000, esHtml = false) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = duracion;
    notificacionVisible.value = true;
};



function seleccionarGrupo(row) {
    const payload = {
        id: row.IdGrupoSCAT,
        nombre: row.GrupoSCAT
    };
    window.parent.postMessage({
        type: 'grupoTaxonomicoSeleccionado',
        payload: payload
    }, '*');
}


const mostrarNotificacionError = (titulo, mensaje, tipo = "error", duracion = 5000) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = duracion;
    notificacionVisible.value = true;
};
const cerrarNotificacion = () => {
    notificacionVisible.value = false;
};

const nuevoGrupo = () => {
    grupoEditado.value = null;
    modalVisible.value = true;
};
const editarGrupo = (grupo) => {
    grupoEditado.value = { ...grupo };
    modalVisible.value = true;
};
const cerrarModal = () => {
    modalVisible.value = false;
};

const handleFormGrupoSubmited = (datosDelFormulario) => {
    cerrarModal();
    const esEdicion = grupoEditado.value !== null;
    const nombreGrupoNormalizado = datosDelFormulario.GrupoSCAT.trim().toLowerCase();
    const registroExistente = currentData.value.find(grupo => {
        const mismoNombre = grupo.GrupoSCAT.trim().toLowerCase() === nombreGrupoNormalizado;
        return esEdicion
            ? (mismoNombre && grupo.IdGrupoSCAT !== grupoEditado.value.IdGrupoSCAT)
            : mismoNombre;
    });

    if (registroExistente) {
        mostrarNotificacionError(
            "Aviso",
            `Ya existe un grupo taxonómico registrado con el mismo nombre (Grupo SCAT), no se realizarán los cambios solicitados.`,
            "error"
        );
        return;
    }

    const procederConGuardado = async () => {
        ElMessageBox.close();
        try {
            guardandoDatosServer.value = true;
            const payload = {
                GrupoSCAT: datosDelFormulario.GrupoSCAT,
                GrupoAbreviado: datosDelFormulario.GrupoAbreviado,
                GrupoSNIB: datosDelFormulario.GrupoSNIB,
            };

            if (!esEdicion) {
                await axios.post("/grupos-taxonomicos", payload);
                mostrarNotificacion(
                    "Ingreso",
                    `El grupo "${datosDelFormulario.GrupoSCAT}" ha sido ingresado correctamente.`,
                    "success"
                );

            } else {
                const idParaEditar = grupoEditado.value.IdGrupoSCAT;
                await axios.put(`/grupos-taxonomicos/${idParaEditar}`, payload);
                mostrarNotificacion(
                    "Modificación",
                    `El grupo "${datosDelFormulario.GrupoSCAT}" ha sido modificado correctamente.`,
                    "success"
                );
            }

            if (tablaRef.value) {
                tablaRef.value.fetchData();
            }
        } catch (error) {
            if (error.response) {
                if (error.response.status === 422) {
                    const errors = error.response.data.errors;
                    let errorMsg = "Error de validación del servidor:<ul>" + Object.values(errors).flat().map(e => `<li>${e}</li>`).join("") + "</ul>";
                    mostrarNotificacion("Error de Validación", errorMsg, "error", 0, true);
                } else {
                    mostrarNotificacionError("Aviso", `El grupo taxonómico '${datosDelFormulario.GrupoSCAT}' ya existe en la base de datos.`);
                }
            } else {
                mostrarNotificacion("Error Inesperado", "Ocurrió un error al contactar al servidor.", "error");
            }
        } finally {
            guardandoDatosServer.value = false;
        }
    };

    const cancelarGuardado = () => { ElMessageBox.close(); };

    if (!esEdicion) {
        procederConGuardado();
    } else {
        const mensaje = `¿Estás seguro de que deseas guardar los cambios para el grupo "${datosDelFormulario.GrupoSCAT || "nuevo grupo"}"?`;
        ElMessageBox({
            title: 'Confirmar modificación', showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
            message: h('div', { class: 'custom-message-content' }, [
                h('div', { class: 'body-content' }, [
                    h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
                    h('div', { class: 'text-container' }, [h('p', null, mensaje)])
                ]),
                h('div', { class: 'footer-buttons' }, [
                    h(BotonCancelar, { onClick: cancelarGuardado }),
                    h(BotonAceptar, { onClick: procederConGuardado, cargando: guardandoDatosServer.value }),
                ])
            ])
        }).catch(() => { });
    }
};

const eliminarGrupo = (IdGrupoSCAT) => {
    const grupoAEliminar = currentData.value.find(g => g.IdGrupoSCAT === IdGrupoSCAT);
    const nombreGrupoEliminado = grupoAEliminar ? `"${grupoAEliminar.GrupoSCAT}"` : 'el registro seleccionado';

    const procederConEliminacion = async () => {
        try {
            ElMessageBox.close();
            await axios.delete(`/grupos-taxonomicos/${IdGrupoSCAT}`);
            if (tablaRef.value) {
                tablaRef.value.fetchData();
            }
            mostrarNotificacion("Eliminación exitosa", `El grupo taxonómico  ${nombreGrupoEliminado} ha sido eliminado correctamente.`, "success");
        } catch (apiError) {
            mostrarNotificacionError('Aviso', `El grupo ${nombreGrupoEliminado} no se puede eliminar. Es posible que esté asociado a un taxón o a una referencia bibliográfica.`, 'error');
        }
    };

    const cancelarEliminacion = () => { ElMessageBox.close(); };

    const mensaje = `¿Está seguro de eliminar el grupo ${nombreGrupoEliminado}? Esta acción no se puede revertir.`;
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
    <LayoutCuerpo v-if="!props.isModal" :usar-app-layout="false" tituloPag="Grupos Taxonómicos"
        tituloArea="Catálogo de grupos taxonómicos">
        <div class="h-full flex flex-col">
            <TablaFiltrable @row-click="manejarClickFila" 
                @row-dblclick="seleccionarGrupo" ref="tablaRef" class="flex-grow" :columnas="columnasDefinidas"
                v-model:datos="currentData" v-model:total-items="totalItems" endpoint="/busca-grupo"
                id-key="IdGrupoSCAT" @editar-item="editarGrupo" @eliminar-item="eliminarGrupo" @nuevo-item="nuevoGrupo" :highlight-current-row="true">

                <template #actions>
                    <el-button type="primary" @click="asociarSeleccionado">
                        Asociar
                    </el-button>
                </template>

            </TablaFiltrable>
        </div>
    </LayoutCuerpo>


    <div v-else class="modal-view-container">
        <div class="area-title" style="background-color: #d9e1eb; color: black; border-radius: 8px; height: 60px ;">
            <h2 style="margin-top: 14px; margin-left: 20px;">Catálogo de grupos taxonómicos</h2>
        </div>
        <div class="h-full flex flex-col flex-grow">

            <TablaFiltrable @row-click="manejarClickFila" 
                @row-dblclick="seleccionarGrupo" ref="tablaRef" class="flex-grow" :columnas="columnasDefinidas"
                v-model:datos="currentData" v-model:total-items="totalItems" endpoint="/busca-grupo"
                id-key="IdGrupoSCAT" @editar-item="editarGrupo" @eliminar-item="eliminarGrupo" @nuevo-item="nuevoGrupo"
                :mostrarTraspaso="true" @traspasaBiblio="asociarSeleccionado" :botCerrar="true" @cerrar="cerrarVentana" :highlight-current-row="true" >
            </TablaFiltrable>
        </div>
    </div>

    <FormGrupoTaxonomico :visible="modalVisible" :gpoTaxEdit="grupoEditado" :accion="grupoEditado ? 'editar' : 'crear'"
        @cerrar="cerrarModal" @formSubmited="handleFormGrupoSubmited" />

    <Teleport to="body">
        <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
            :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion" @close="cerrarNotificacion" />
    </Teleport>
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