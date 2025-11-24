<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CuerpoTiposRegion from '@/Pages/Socat/TipoRegion/CuerpoTipoRegion.vue'; 

const props = defineProps({
  treeDataProp: {
    type: Array,
    required: true,
  },
  flatTreeDataProp: {
    type: Array,
    required: true,
  },
  isModal: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <AppLayout title="Tipos de Región">
    <CuerpoTiposRegion
      :treeDataProp="props.treeDataProp"
      :flatTreeDataProp="props.flatTreeDataProp"
      :isModal="props.isModal"
    />
  </AppLayout>
</template>