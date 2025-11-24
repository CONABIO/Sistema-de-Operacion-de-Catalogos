<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CuerpoCaracteristicas from '@/Pages/Socat/Caracteristicas/CuerpoCaracteristicas.vue'; 

const props = defineProps({
  treeDataProp: {
    type: Array,
    required: true,
  },
  flatTreeDataProp: {
    type: Array,
    required: true,
  },
});
</script>

<template>
  <AppLayout title="Características Taxonómicas">
    <CuerpoCaracteristicas
      :treeDataProp="props.treeDataProp"
      :flatTreeDataProp="props.flatTreeDataProp"
    />
  </AppLayout>
</template>