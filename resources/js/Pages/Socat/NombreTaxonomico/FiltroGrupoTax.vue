<script setup>
import { ref, onMounted, defineEmits, computed } from 'vue';
import arbolCheck from "@/Components/Biotica/ArbCheck.vue";
import { ElMessageBox } from 'element-plus';
import btnTraspaso from "@/Components/Biotica/BtnTraspaso.vue";
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";

//Definición de variables 
const props = defineProps({
    grupos: {
        type: Object,
        required: true,
    },
});

const propiedades = {
    children: 'children',
    label: 'label'
}

const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);
const datosOrdenados = computed(() => {
    const lista = [...props.grupos["original"]];
    return lista.sort((a, b) => {
        return a.label.localeCompare(b.label, 'es', { sensitivity: 'base' });
    });
});
const checkAll = ref(false);
const arbolRef = ref(null);
const isIndeterminate = ref(false);

const emit = defineEmits(['regresaGrupos', 'cerrar']);

onMounted(() => {
    mostrarNotificacion(
        "Grupos taxonómicos",
        "Se debe seleccionar al menos un grupo taxonómico",
        "info",
        7000
    );
});

//Función referenciada para ejecutar las funciones desde el componente padre hasta el componente hijo
const marcar = () => {
    if (arbolRef.value) {
        arbolRef.value.marcarTodos();
    }
}

const desmarcar = () => {
    if (arbolRef.value) {
        arbolRef.value.desmarcarTodos();
    }
}

const recuperaMarcados = () => {
    if (arbolRef.value) {
        arbolRef.value.recuperaMarcados();
    }
}

const recibeGrupos = (data) => {
    if (data['ids'] !== '') {
        emit('cerrar', false);
        emit('regresaGrupos', data);
    }
    else {
        mostrarNotificacion(
            "Grupos taxonómicos",
            "Se debe selccionar al menos un grupo taxonómico",
            "error",
            7000
        );
    }
}

const mostrarNotificacion = (
    titulo,
    mensaje,
    tipo = "warning",
    duracion = 5000,
    dangerouslyUseHTML = false
) => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionDuracion.value = duracion;
    notificacionVisible.value = true;
};

const cerrarNotificacion = () => {
    notificacionVisible.value = false;
};

</script>

<template>
    <div class="common-layout">
        <el-card>
            <el-container>
                <el-header class="header">
                    <h1 class="titulo">Catálogo de grupos taxonómicos</h1>
                </el-header>
                <el-main class="contenido">
                    <div>
                        <btnTraspaso @traspasa="recuperaMarcados()" />
                        <br />
                        <div v-show="!checkAll">
                            <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="marcar">
                                Marcar todos
                            </el-checkbox>
                        </div>
                        <div v-show="checkAll">
                            <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="desmarcar">
                                Desmarcar todos
                            </el-checkbox>
                        </div>
                    </div>
                    <arbolCheck :datosArbol="datosOrdenados" :defaultProps="propiedades" 
                                ref="arbolRef" default-expand-all
                        @regresaMarcados="recibeGrupos" />
                </el-main>
            </el-container>
        </el-card>
        <Teleport to="body">
            <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
                @close="cerrarNotificacion" />
        </Teleport>
    </div>
</template>

<style scoped>
.common-layout {
    width: 100%;
    max-width: 700px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
}

:deep(.el-card) {
    display: flex;
    flex-direction: column;
    max-height: 900vh;
    border-radius: 12px;
    overflow: hidden;
}

:deep(.el-card__body) {
    padding: 0 !important;
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

.header {
    background-color: #f5f5f5;
    padding: 15px;
    border-bottom: 1px solid #e0e0e0;
    height: auto !important;
    min-height: auto !important;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.titulo {
    font-size: 1.25rem;
    font-weight: bold;
    color: #333;
    margin: 0;
    text-align: center;
    line-height: 1.3;
    white-space: normal;
    word-wrap: break-word;
    word-break: break-word;
}

.contenido {
    padding: 15px;
    overflow-y: auto;
    flex: 1;
}

.contenido>div:first-child {
    margin-bottom: 10px;
    width: 100%;
}

@media (max-width: 480px) {
    .common-layout {
        max-width: 95vw;
    }

    .titulo {
        font-size: 1rem;
    }

    :deep(.el-card) {
        max-height: 70vh;
    }
}
</style>