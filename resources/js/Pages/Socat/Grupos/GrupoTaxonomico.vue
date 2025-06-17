<script setup>
import { ref, watchEffect, onMounted } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CuerpoGrupo from '@/Pages/Socat/Grupos/CuerpoGrupoTaxon.vue';

</script>

<template>
    <AppLayout title="GrupoTaxonomico">
        
        
        <CuerpoGrupo />

    </AppLayout>
</template>

