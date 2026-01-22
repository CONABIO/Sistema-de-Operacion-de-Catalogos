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
  <el-tooltip class="item" effect="dark" content="Aceptar" placement= "bottom"  :z-index="2101">
  <el-button 
    type="success" 
    circle 
    :disabled="disabled" 
    @click="handleClick"
    class="custom-circle-button"
  >
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
.custom-circle-button.el-button.is-circle {
  width: 32px;
  height: 32px;
}
</style>