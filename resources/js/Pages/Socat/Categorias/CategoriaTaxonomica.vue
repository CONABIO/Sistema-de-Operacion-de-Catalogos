<template>
  <AppLayout title="CategoriaTaxonomica">
      <template #header> </template>
      <div>
          
      </div>
  </AppLayout>
</template>

<script setup>
import { defineProps, onMounted } from 'vue';
import BarChart from '@/Components/Biotica/BarChart.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Tree from '@/Components/Biotica/Tree.vue'; // Importa el componente Tree
const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    treeData: {
        type: Object,
        required: true,
    },
});

onMounted(() => {
    console.log('Data recibida en CategoriaTaxonomica:', props.data); // Agrega esto
});
</script>

<style>
.box-card {
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
  border-radius: 10px;
  padding: 20px;
}

.shadow-lg {
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}

.rounded-lg {
  border-radius: 0.5rem;
}
</style>