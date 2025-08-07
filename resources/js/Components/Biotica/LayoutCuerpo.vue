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

<template>
    <AppLayout v-if="usarAppLayout" :title="tituloPag">
        <div class="flex justify-start min-h-[90vh] p-4">
            <div class="w-full max-w-[1600px]  mx-auto h-full">
                <el-card>
                    <Titulo :titulo="tituloArea" />
                    <br>
                    <div class="bg-white shadow-md sm:rounded-lg w-full flex flex-col" style="">
                        <div class="p-4 w-full h-full overflow=auto">
                            <el-card class="box-card w-full h-full flex flex-col">
                                <div class="w-full h-full flex flex-col flex-grow">
                                    <slot></slot>
                                </div>
                            </el-card>
                        </div>
                    </div>
                </el-card>
            </div>
        </div>
    </AppLayout>

    <template v-else>
        <Head :title="tituloPag" />
        <div class="flex justify-start min-h-[90vh] p-4">
            <div class="w-full max-w-[1600px] mx-auto h-full">
                <Titulo :titulo="tituloArea" />
                <br>
                <div class="bg-white shadow-md sm:rounded-lg w-full flex flex-col">
                    <div class="p-4 w-full h-full overflow=auto">
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