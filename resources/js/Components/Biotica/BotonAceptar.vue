<script setup>
import { defineProps, defineEmits } from 'vue';

// No necesitamos importar el icono de Element Plus
// const { Check } = await import('@element-plus/icons-vue'); // Quita o comenta esta línea

defineProps({
  disabled: { type: Boolean, default: false },
  // Tamaño del icono, puedes ajustarlo si lo necesitas
  iconSize: { type: [String, Number], default: 18 } 
});

const emit = defineEmits(['click']);
const handleClick = (event) => { emit('click', event); };
</script>

<template>
  <el-tooltip class="item" effect="dark" content="Aceptar" :placement= "toolPosicion">
  <el-button 
    type="success" 
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
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
      </svg>
    </el-icon>
  </el-button>
  </el-tooltip>
</template>

<style scoped>
/* Estilos para que el botón tenga un tamaño fijo y el icono quepa bien */
.custom-circle-button.el-button.is-circle {
  width: 32px;
  height: 32px;
}
</style>