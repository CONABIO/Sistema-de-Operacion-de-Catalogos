
<!--Elemento para boton de tras paso de informacion-->
<script setup>
    import traspasoInfo from '@/Components/Biotica/Icons/TraspasoInfo.vue';
    import traspasoInfoArr from '@/Components/Biotica/Icons/TraspasoInfoArriba.vue';

    // Declaramos los eventos que este componente puede emitir
    const emit = defineEmits(['traspasa']);

    // Definimos la función `editar`
    const traspasaDatos = () =>{
        emit('traspasa');
    } 

    const props = defineProps({
        toolPosicion: {
            type: String,
            default: "top"
        },
        icono:{
            type: String, 
            default: "izq"
        }
    });
</script>

<template>
        <el-tooltip class="item" effect="dark" content="Asociar" :placement= "toolPosicion">
        <el-button  @click.prevent="traspasaDatos"
                    circle
                    icon="el-icon-d-arrow-right"
                    type="primary">
            <el-icon v-if="icono === 'izq'">
                <traspasoInfo />
            </el-icon>
            <el-icon v-else>
                <traspasoInfoArr />
            </el-icon>
        </el-button>
    </el-tooltip>
</template>