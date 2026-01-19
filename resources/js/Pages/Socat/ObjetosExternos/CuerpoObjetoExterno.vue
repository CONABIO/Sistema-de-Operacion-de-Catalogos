<script setup>
import { ref, h, watch } from 'vue';
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import axios from 'axios';
import { ElMessageBox, ElTableColumn } from 'element-plus';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import FormObjetoExterno from './FormObjetoExterno.vue';

const tablaRef = ref(null);
const currentData = ref([]);
const totalItems = ref(0);
const modalVisible = ref(false);
const objetoExternoEditado = ref(null);


const props = defineProps({
    modal: Boolean 
});

const columnasDefinidas = ref([
    {
        prop: 'NombreObjeto',
        label: 'Nombre del archivo',
        minWidth: '200',
        sortable: true,
        filtrable: true,
        align: 'left'
    },
    {
        prop: 'NombreSitio',
        label: 'Nombre del sitio',
        minWidth: '200',
        sortable: true,
        filtrable: true,
        align: 'left'
    },
    {
        prop: 'extension',
        label: 'Extensión',
        minWidth: '100',
    },
    {
        prop: 'tipo',
        label: 'Tipo',
        minWidth: '200',
    }
]);


const seleccionarObjetoParaAsociar = (objeto) => {
    if (props.modal) {
        window.parent.postMessage({
            type: 'objetoExternoSeleccionado',
            payload: objeto
        }, '*');
    }
};


const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);

const mostrarNotificacion = (titulo, mensaje, tipo = "info") => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = 5000;
    notificacionVisible.value = true;
};
const cerrarNotificacion = () => notificacionVisible.value = false;

const nuevoObjetoExterno = () => {
    objetoExternoEditado.value = null;
    modalVisible.value = true;
};
const editarObjetoExterno = (item) => {
    objetoExternoEditado.value = item;
    modalVisible.value = true;
};
const cerrarModal = () => modalVisible.value = false;

const handleFormSubmited = async (datosDelFormulario) => {
    cerrarModal();
    const esEdicion = !!datosDelFormulario.IdObjetoExterno;
    const url = esEdicion ? `/objetos-externos/${datosDelFormulario.IdObjetoExterno}` : '/objetos-externos';
    const method = esEdicion ? 'put' : 'post';

    try {
        await axios[method](url, datosDelFormulario);
        mostrarNotificacion("Éxito", `El objeto externo ha sido ${esEdicion ? 'actualizado' : 'creado'} correctamente.`, "success");
        tablaRef.value?.fetchData();
    } catch (error) {
        const mensajeError = error.response?.data?.message || 'Ocurrió un error inesperado.';
        mostrarNotificacion("Error", mensajeError, "error");
    }
};

const eliminarObjetoExterno = (idObjeto) => {
    const itemAEliminar = currentData.value.find(item => item.IdObjetoExterno === idObjeto);
    const mensaje = `¿Está seguro de eliminar el objeto "${itemAEliminar?.NombreObjeto || 'seleccionado'}"? Esta acción no se puede revertir.`;

    ElMessageBox({
        title: 'Confirmar eliminación', showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
        message: h('div', null, [
            h('p', null, mensaje),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                h(BotonAceptar, {
                    onClick: async () => {
                        try {
                            ElMessageBox.close();
                            await axios.delete(`/objetos-externos/${idObjeto}`);
                            mostrarNotificacion("Eliminación exitosa", `El objeto externo fue eliminado.`, "success");
                            tablaRef.value?.fetchData();
                        } catch (apiError) {
                            mostrarNotificacion("Error al Eliminar", apiError.response?.data?.message || 'Ocurrió un error.', "error");
                        }
                    }
                }),
            ])
        ])
    }).catch(() => { });
};


watch(currentData, (newData) => {
    if (newData && newData.length > 0) {
        newData.forEach(item => {
            item.extension = item.mime?.Extension || 'N/A';
            item.tipo = item.mime?.MIME || 'N/A';
        });
    }
}, { deep: true, immediate: true });

</script>

<template>
    <LayoutCuerpo :usar-app-layout="false" tituloPag="Objetos Externos" tituloArea="Catálogo de objetos externos">
        <div class="h-full flex flex-col">
            <TablaFiltrable ref="tablaRef" class="flex-grow"  @row-click="seleccionarObjetoParaAsociar" :columnas="columnasDefinidas" v-model:datos="currentData"
                v-model:total-items="totalItems" endpoint="/api/objetos-externos" id-key="IdObjetoExterno"
                @editar-item="editarObjetoExterno" @eliminar-item="eliminarObjetoExterno"
                @nuevo-item="nuevoObjetoExterno" :filtros-principales="['NombreObjeto']"
                placeholder-filtro-principal="Buscar por nombre de objeto...">
                <template #extra-columns>
                    <el-table-column label="Extensión" min-width="100">
                        <template #default="{ row }">
                            <span>{{ row.mime?.Extension || 'N/A' }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="Tipo" min-width="200">
                        <template #default="{ row }">
                            <span>{{ row.mime?.MIME || 'N/A' }}</span>
                        </template>
                    </el-table-column>

                </template>

                <template #expand-column>
                    <el-table-column type="expand">
                        <template #default="{ row }">
                            <div class="expand-content-detail">
                                <p><strong>ID:</strong> {{ row.IdObjetoExterno }}</p>
                                <p><strong>Ruta:</strong> {{ row.Ruta }}</p>
                                <p><strong>Titulo:</strong> {{ row.Titulo }}</p>
                                <p><strong>Autor:</strong> {{ row.Autor }}</p>
                                <p><strong>Observaciones:</strong> {{ row.Observaciones }}</p>
                                <p><strong>FechaModificacion:</strong> {{ row.FechaModificacion }}</p>
                                <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                                <p><strong>NombreSitio:</strong> {{ row.NombreSitio }}</p>
                                <p><strong>Protocolo:</strong> {{ row.Protocolo }}</p>
                                <p><strong>Usuario:</strong> {{ row.Usuario }}</p>
                                <p><strong>UnidadLogica:</strong> {{ row.UnidadLogica }}</p>
                                <p><strong>Institucion:</strong> {{ row.Institucion }}</p>
                                <p><strong>Fecha:</strong> {{ row.Fecha }}</p>
                                <p><strong>IdOriginal:</strong> {{ row.IdOriginal }}</p>
                                <p><strong>Catalogo:</strong> {{ row.Catalogo }}</p>
                            </div>
                        </template>
                    </el-table-column>
                </template>
            </TablaFiltrable>
        </div>

        <Teleport to="body">
            <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
                @close="cerrarNotificacion" />

            <FormObjetoExterno :visible="modalVisible" :accion="objetoExternoEditado ? 'editar' : 'crear'"
                :objeto-externo-edit="objetoExternoEditado" @cerrar="cerrarModal" @form-submited="handleFormSubmited" />
        </Teleport>
    </LayoutCuerpo>
</template>

<style scoped>
.expand-content-detail {
    padding: 10px 15px;
    background-color: #fdfdfd;
    font-size: 13px;
}

.footer-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}
</style>