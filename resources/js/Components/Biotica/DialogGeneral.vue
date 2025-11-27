<script setup>
import { defineModel } from 'vue';
import { router } from '@inertiajs/vue3';


const props = defineProps({
    botCerrar: {
        type: Boolean,
        required: true,
    },

    pressEsc: {
        type: Boolean,
        required: true,
    },


    width: {
        type: String,
        default: "80%",
    },

    draggable: {
        type: Boolean,
        default: false
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

        <el-dialog v-model="dialogFormVisible" :z-index="currentZIndex" :draggable="draggable"
            :close-on-click-modal="false" :show-close="botCerrar" :destroy-on-close="false"
            :close-on-press-escape="pressEsc" class="my-responsive-dialog" :fullscreen="false"
            :style="{ width: width }">
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
.my-dialog-content {
    max-height: 80vh;
    overflow-y: auto;
    padding: 20px;
}


:deep(.el-dialog) {
    max-width: none !important;
    margin: 5vh auto !important;
}

/* @media (max-width: 1500px) {
    :deep(.el-dialog) {
        width: 85% !important;
    }

    .my-responsive-dialog {
        width: 90%;
        max-width: 500px;

    }
}
 */

@media (max-width: 768px) {
    :deep(.el-dialog) {
        width: 90% !important;
    }

    .my-dialog-content {
        max-height: 75vh;
        padding: 15px;
    }
}
</style>