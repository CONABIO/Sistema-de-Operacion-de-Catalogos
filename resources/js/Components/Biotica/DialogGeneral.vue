<script setup>
import { defineModel } from 'vue';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';
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
        <el-dialog v-model="dialogFormVisible" :draggable="draggable" :z-index="currentZIndex"
            :close-on-click-modal="false" :show-close="botCerrar" :destroy-on-close="false"
            :close-on-press-escape="pressEsc" class="my-responsive-dialog">
            <div class="my-dialog-content">
                <slot></slot>
            </div>
        </el-dialog>
    </div>
</template>

<style scoped>
.my-dialog-content {
    max-height: 70vh;
    overflow-y: auto;
    padding: 10px;
}

.my-responsive-dialog {
    width: 90%;
    max-width: 500px;
}

@media (max-width: 768px) {
    .my-responsive-dialog {
        width: 95%;
        margin: 0 auto;
        top: 2%;
    }

    .my-dialog-content {
        max-height: 60vh;
    }
}
</style>