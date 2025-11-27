<script setup>
import { ref, onMounted, defineEmits } from 'vue';
import arbolCheck from "@/Components/Biotica/ArbCheck.vue";
import { ElMessageBox } from 'element-plus';
import btnTraspaso from "@/Components/Biotica/BtnTraspaso.vue";
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";

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

const datos = props.grupos["original"];
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
                    <arbolCheck :datosArbol="datos" :defaultProps="propiedades" ref="arbolRef" default-expand-all
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
    display: flex;
    flex-direction: column;
    min-height: 1000px;
}

.header {
    text-align: center;
    padding: 0.5rem;
    background-color: #f5f5f5;
    border-bottom: 1px solid #ddd;
    border-radius: 10px;
    margin-bottom: 10px;
}

.titulo {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.contenido {
    max-width: 100%;
    padding: 0.5rem;
    margin-top: 1rem;
}

.contenido>div {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
}

@media (min-width: 768px) {
    .titulo {
        font-size: 2rem;
    }
}

@media (max-width: 480px) {
    .titulo {
        font-size: 1rem;
    }

    .header {
        padding: 0.25rem;
    }

    .contenido {
        padding: 0.25rem;
        margin-top: 0.5rem;
    }
}
</style>