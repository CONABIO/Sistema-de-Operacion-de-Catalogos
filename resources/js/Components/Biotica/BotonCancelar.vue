<script setup>
import { defineProps, defineEmits } from 'vue';

// No necesitamos importar el icono de Element Plus
// const { Close } = await import('@element-plus/icons-vue'); // Quita o comenta esta línea

defineProps({
  disabled: { type: Boolean, default: false },
  // Tamaño del icono, consistente con el otro botón
  iconSize: { type: [String, Number], default: 18 }
});

const emit = defineEmits(['click']);
const handleClick = (event) => { emit('click', event); };
</script>

<template>
  <el-button 
    type="danger" 
    circle 
    :disabled="disabled" 
    @click="handleClick"
    class="custom-circle-button"
  >
    <!-- Renderizamos nuestro propio icono SVG grueso aquí dentro -->
    <el-icon :size="iconSize">
      <svg 
        xmlns="http://www.w3.org/2000/svg" 
        fill="none" 
        viewBox="0 0 24 24" 
        stroke-width="3.5" 
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </el-icon>
  </el-button>
</template>

<style scoped>
/* Mismos estilos para consistencia visual */
.custom-circle-button.el-button.is-circle {
  width: 32px;
  height: 32px;
}
</style>