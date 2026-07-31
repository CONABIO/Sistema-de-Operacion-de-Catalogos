<script setup>
import { defineProps, defineEmits } from 'vue';


defineProps({
  disabled: { type: Boolean, default: false },
  iconSize: { type: [String, Number], default: 18 }
});

const emit = defineEmits(['click']);
const handleClick = (event) => { emit('click', event); };
</script>

<template>
  <el-tooltip class="item" effect="dark" content="Tipo de distribución">
  <el-button 
    color="#80C2C2"
    circle 
    :disabled="disabled" 
    @click="handleClick"
    class="custom-circle-button"
  >
    <el-icon :size="25" class="icono-nombre-comun">
      <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round">
        <circle cx="12" cy="5" r="2"/>
        <circle cx="6" cy="18" r="2"/>
        <circle cx="12" cy="18" r="2"/>
        <circle cx="18" cy="18" r="2"/>
        <path d="M12 7v5"/>
        <path d="M6 16v-2h12v2"/>
        <path d="M12 12H6"/>
        <path d="M12 12H18"/>
    </svg>
    </el-icon>
  </el-button>
  </el-tooltip>
</template>

<style scoped>
  .icono-nombre-comun {
    color: white;
    width: 32px;
    height: 32px;
  }

  .custom-circle-button.el-button.is-circle {
    width: 32px;
    height: 32px;
  }
</style>