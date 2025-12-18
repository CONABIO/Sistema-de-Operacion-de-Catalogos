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
                    <div class="header-content">
                        <h1 class="titulo">Catálogo de grupos taxonómicos</h1>
                    </div>
                </el-header>
                <el-main class="contenido">
                    <div>
                        <div v-show="!checkAll" class="header-content">
                            <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="marcar">
                                Marcar todos
                            </el-checkbox> 
                            <div class="header-button">
                                <btnTraspaso @traspasa="recuperaMarcados()" />
                            </div>
                        </div>
                        <div v-show="checkAll">
                            <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="desmarcar">
                                Desmarcar todos
                            </el-checkbox>
                        </div>
                    </div>
                    <div class="arbol-wrapper">
                        <arbolCheck :datosArbol="datosOrdenados" :defaultProps="propiedades" 
                                    ref="arbolRef" default-expand-all
                            @regresaMarcados="recibeGrupos" />
                    </div>
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
        height: auto;
    }

    .card-container {
        display: flex;
        flex-direction: column;
        border-radius: 12px;
        overflow: hidden;
    }

    .header {
        background-color: #d9e1eb;
        padding: 15px;
        border-bottom: 1px solid #e0e0e0;
        height: auto !important;
        min-height: auto !important;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 8px;
        color: white;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .header-button {
        margin-left: 15px; /* Espacio entre título y botón */
        flex-shrink: 0; /* Evita que el botón se reduzca */
    }

    .titulo {
        font-size: 1.25rem;
        font-weight: bold;
        color: #333;
        margin: 0;
        text-align: center;
    }

    .contenido {
        padding: 15px;
        display: flex;
        flex-direction: column;
        height: 100%;
        /* QUITAR overflow-y: auto de aquí */
    }

    /*Contenedor específico para el árbol*/
    .arbol-wrapper {
        flex: 1;
        margin-top: 10px;
        overflow: hidden; /* Sin scroll aquí */
    }

    /* Estilos profundos para el árbol */
    :deep(.el-tree) {
        max-height: 400px !important;
        height: 400px !important;
        overflow-y: auto !important;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 10px;
        background-color: #fafafa;
    }

    @media (max-width: 480px) {
        .common-layout {
            max-width: 95vw;
        }
        
        :deep(.el-tree) {
            max-height: 300px !important;
            height: 300px !important;
        }
    }
</style>