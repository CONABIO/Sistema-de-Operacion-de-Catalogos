<script setup>
    import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
    import CuerpoTipoRelacion from "@/Pages/Socat/TipoRelacion/CuerpoTipoRelación.vue";

    /*Se crearron dos nuevas propiedaes una para determinar si se muestra el menu y la otra para 
      determinar si se muestra como modal y se puede mostrar y cerrar en una pestaña*/
    const props = defineProps({
        treeDataProp: { type: Array, default: () => [] },
        flatTreeDataProp: { type: Array, default: () => [] },
        accionSalida: { type: String, default: "salida" },
        layout: { type: Boolean, default: true }
    });

    /*Se agrego la funcion emit para que retorne el cerrar al componente padre  */
    const emit = defineEmits(['cerrar']);

    const cerrarDialogo = () => {        
        emit('cerrar');
    };
</script>
<template>    
    <LayoutCuerpo :usar-app-layout="props.layout" titulo-pag="Tipos de Relación"
        titulo-area="Catálogo de tipos de relaciones taxonómicas">
        <CuerpoTipoRelacion 
            :treeDataProp="props.treeDataProp" 
            :flatTreeDataProp="props.flatTreeDataProp" 
            :accion = "props.accionSalida"
            @cerrar="cerrarDialogo"
        />
    </LayoutCuerpo>
</template>