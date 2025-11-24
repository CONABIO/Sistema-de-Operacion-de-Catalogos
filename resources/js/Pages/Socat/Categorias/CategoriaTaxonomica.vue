<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CuerpoCategorias from '@/Pages/Socat/Categorias/CuerpoCategoriasTaxonomicas.vue';

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
  <AppLayout title="Categorías Taxonómicas">
    <CuerpoCategorias
      :treeDataProp="props.treeDataProp"
      :flatTreeDataProp="props.flatTreeDataProp"
    />
  </AppLayout>
</template>