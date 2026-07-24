<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Titulo from '@/Components/Biotica/LayoutTitulo.vue';
import { Head } from '@inertiajs/vue3';


defineProps({
    tituloArea: {
        type: String,
        required: true,
    },
    tituloPag: {
        type: String,
        required: true,
    },
    usarAppLayout: {
        type: Boolean,
        default: true,
    }
});
</script>

<style>
:deep(.p-4) {
    padding: 8px !important;
}

:deep(.min-h-\[90vh\]) {
    min-height: auto !important;
}

:deep(.el-card__body) {
    padding: 10px !important;
}

.main-section {
    margin-top: 0 !important;
}

:deep(.el-collapse-item__content) {
    padding-bottom: 5px !important;
}
</style>

<template>
    <AppLayout v-if="usarAppLayout" :title="tituloPag">
        <div class="flex justify-start min-h-[90vh] p-4">
            <div class="w-full max-w-[1600px]  mx-auto h-full">
                <Titulo :titulo="tituloArea" />
                <br>
                <div class="bg-white shadow-md sm:rounded-lg w-full flex flex-col" style="">
                    <div class="p-1 w-full h-full overflow=auto">
                        <el-card class="box-card w-full h-full flex flex-col">
                            <div class="w-full h-full flex flex-col flex-grow">
                                <slot></slot>
                            </div>
                        </el-card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <template v-else>

        <Head :title="tituloPag" />
        <div class="flex justify-start min-h-[50vh] p-4">
            <div class="w-full max-w-[1600px] mx-auto h-full ">
                <Titulo :titulo="tituloArea" />
                <br>
                <div class="bg-white shadow-md sm:rounded-lg w-full flex flex-col">
                    <div class="p-1 w-full h-full overflow=auto">
                        <el-card class="box-card w-full h-full flex flex-col">
                            <div class="w-full h-full flex flex-col flex-grow">
                                <slot></slot>
                            </div>
                        </el-card>
                    </div>
                </div>
            </div>
        </div>
    </template>
</template>
