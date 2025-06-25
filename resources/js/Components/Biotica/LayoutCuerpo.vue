<script setup>
import { ref, onMounted, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Titulo from '@/Components/Biotica/LayoutTitulo.vue';

const props = defineProps({
    tituloArea: String,
    tituloPag: String
});

const escala = ref(1);
const contenedorEscalable = ref(null);

const ajustarEscala = () => {
    nextTick(() => {
        const contenedor = contenedorEscalable.value;
        if (contenedor) {
            const maxAltura = window.innerHeight - 120; // Ajusta según altura del nav y paddings
            const alturaContenido = contenedor.scrollHeight;
            escala.value = alturaContenido > maxAltura
                ? maxAltura / alturaContenido
                : 1;
        }
    });
};

onMounted(() => {
    ajustarEscala();
    window.addEventListener('resize', ajustarEscala);
});
</script>

<template>
    <AppLayout :title="tituloPag">
        <div  class="max-w-7xl mx-auto h-full flex flex-col">
            <Titulo :titulo="tituloArea" />
            <br/>   
            <div class="mt-4 flex-1 overflow-auto bg-white shadow-md sm:rounded-lg p-4">
                <el-card class="box-card w-full h-full flex flex-col">
                    <div  class="h-full overflow-auto">
                        <slot></slot>
                    </div>
                </el-card>
            </div>
        </div>
    </AppLayout>
</template>