<template>
    <el-tooltip content="Cambiar ícono" :placement="toolPosicion">
        <el-button
            type="warning"
            circle
            :disabled="disabled"
            @click="$emit('cambiar-icono')"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                class="custom-svg-icon"
            >
                <path fill="currentColor" d="M21.25 21.25H2.75V2.75h18.5v18.5M22 2H2v20h20V2M6.5 16.5l3.25-4.25l2.5 3.25l2.25-3l4 5.25H6.5Z"></path>
            </svg>
        </el-button>
    </el-tooltip>
</template>

<script setup>
import { ElButton, ElTooltip } from 'element-plus';

defineProps({
    disabled: { type: Boolean, default: false },
    toolPosicion: { type: String, default: 'top' },
});

defineEmits(['cambiar-icono']);
</script>

<style scoped>
.el-button.is-circle {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.custom-svg-icon {
    width: 16px;
    height: 16px;
}
</style>