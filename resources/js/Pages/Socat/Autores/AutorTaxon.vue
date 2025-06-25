<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CuerpoAutor from '@/Pages/Socat/Autores/CuerpoAutorTaxon.vue'; 
import CuerpoGen from '@/Components/Biotica/LayoutCuerpo.vue';

</script>

<template>
    <CuerpoGen :tituloPag="'Nombre_Taxón'" :tituloArea="'Catálogo de autoridades taxonómicas'">
    <AppLayout title="Catálogo de autores"> 
        
        <CuerpoAutor /> 

    </AppLayout>
    
    </CuerpoGen>
</template>

<style scoped>

</style>