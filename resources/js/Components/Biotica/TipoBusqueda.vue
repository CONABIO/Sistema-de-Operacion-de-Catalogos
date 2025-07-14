<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: 'inicia' 
  }
});

const emit = defineEmits(['update:modelValue']);

const seleccion = ref(props.modelValue);

watch(seleccion, (nuevoValor) => {
  emit('update:modelValue', nuevoValor);
});
</script>

<template>
  <el-radio-group v-model="seleccion" size="small">
    <el-radio label="inicia">Inicia con</el-radio>
    <el-radio label="contiene">Cualquier coincidencia</el-radio>
    <el-radio label="termina">Termina con</el-radio>
  </el-radio-group>
</template>

<style scoped>
</style>