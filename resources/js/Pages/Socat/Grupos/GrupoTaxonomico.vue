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
    <!-- 2. Si NO es un modal, usamos el AppLayout como siempre -->
    <AppLayout v-if="!props.isModal" title="Catálogo de grupos taxonómicos">
        <!-- Pasamos los props al componente hijo -->
        <CuerpoGrupo :isModal="props.isModal" :datosGrupo="props.datosGrupo" />
    </AppLayout>

    <!-- 3. Si SÍ es un modal, renderizamos el componente de cuerpo DIRECTAMENTE, sin layout -->
    <CuerpoGrupo v-else :isModal="props.isModal" :datosGrupo="props.datosGrupo" />
</template>
