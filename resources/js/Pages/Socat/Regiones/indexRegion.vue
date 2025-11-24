<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CuerpoRegiones from '@/Pages/Socat/Regiones/CuerpoRegion.vue'; 

const props = defineProps({
    treeDataProp: { type: Array, required: true },
    tiposDeRegionProp: { type: Array, required: true },
    tiposDeRegionTreeProp: { type: Array, required: true },
});
</script>

<template>
  <AppLayout title="Regiones">
    <CuerpoRegiones
      :treeDataProp="props.treeDataProp"
      :tiposDeRegionProp="props.tiposDeRegionProp"
      :tiposDeRegionTreeProp="props.tiposDeRegionTreeProp"
    />
  </AppLayout>
</template>