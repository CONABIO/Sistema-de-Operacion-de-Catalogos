<script setup>
import { ref, watchEffect, onMounted } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CuerpoGrupo from '@/Pages/Socat/Grupos/CuerpoGrupoTaxon.vue';

const props = defineProps({
    isModal: Boolean,
    datosGrupo: Object, // También recibimos los datos para pasarlos
});

</script>

<template>
    <AppLayout v-if="!props.isModal" title="Catálogo de grupos taxonómicos">
        <CuerpoGrupo :isModal="props.isModal" :datosGrupo="props.datosGrupo" />
    </AppLayout>
    <CuerpoGrupo v-else :isModal="props.isModal" :datosGrupo="props.datosGrupo" />
</template>
