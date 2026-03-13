<script setup>
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
import CuerpoTipoRelacion from "@/Pages/Socat/TipoRelacion/CuerpoTipoRelación.vue";

const props = defineProps({
    treeDataProp: { type: Array, default: () => [] },
    flatTreeDataProp: { type: Array, default: () => [] },
});
</script>

<template>
    <LayoutCuerpo :usar-app-layout="true" titulo-pag="Tipos de Relación"
        titulo-area="Catálogo de tipos de relaciones taxonómicas">
        
        <CuerpoTipoRelacion 
            :treeDataProp="props.treeDataProp" 
            :flatTreeDataProp="props.flatTreeDataProp" 
        />
        
    </LayoutCuerpo>
</template>