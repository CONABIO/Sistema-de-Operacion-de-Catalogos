<script setup>
import { defineModel } from 'vue';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';
import { router } from '@inertiajs/vue3';

//Definición de variables 
const props = defineProps({
    botCerrar: {
        type: Boolean,
        required: true,
    },
    
    pressEsc: {
        type: Boolean,
        required: true,
    },

    width:{
        type: String,
        default: "80%",
    }
});

function handleLogout() {
  router.get('/dashboard'); 
}

const currentZIndex = 1000;

const dialogFormVisible = defineModel();
</script>

<template>
    <div>
        <el-dialog 
            v-model="dialogFormVisible" 
            :z-index="currentZIndex" 
            :close-on-click-modal="false" 
            :show-close="botCerrar"
            :destroy-on-close="false" 
            :close-on-press-escape="pressEsc" 
            class="my-responsive-dialog"
            :fullscreen="false"
            :style=" { width: width } "
        >
            <template #header="{ close, titleId, titleClass }">
                <div class="my-dialog-header">
                    <slot name="header"></slot>
                    <BotonSalir @salir="handleLogout" /> 
                </div>
            </template>

            <div class="my-dialog-content">
                <slot></slot>
            </div>
        </el-dialog>
    </div>
</template>

<style scoped>
/* Contenido del diálogo */
.my-dialog-content {
    max-height: 80vh;
    overflow-y: auto;
    padding: 20px;
}

/* Estilos principales del diálogo */
:deep(.el-dialog) {
    max-width: none !important;   /* Elimina cualquier max-width previo */
    margin: 5vh auto !important;  /* Centrado vertical y horizontal */
}

/* Ajustes responsivos */
@media (max-width: 1500px) {
    :deep(.el-dialog) {
        width: 85% !important;    /* Un poco más ancho en pantallas grandes pero no enormes */
    }
}

@media (max-width: 768px) {
    :deep(.el-dialog) {
        width: 90% !important;    /* Más ancho en móviles para mejor uso del espacio */
    }
    .my-dialog-content {
        max-height: 75vh;
        padding: 15px;
    }
}
</style>