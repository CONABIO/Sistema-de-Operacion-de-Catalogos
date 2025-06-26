<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CuerpoAutor from '@/Pages/Socat/Autores/CuerpoAutorTaxon.vue'; 
import CuerpoGen from '@/Components/Biotica/LayoutCuerpo.vue';

</script>

<template>
    <CuerpoGen :tituloPag="'Nombre_Taxón'" :tituloArea="'Catálogo de autoridades taxonómicas'">

        
        <CuerpoAutor /> 


    
    </CuerpoGen>
</template>

<style scoped>

</style>