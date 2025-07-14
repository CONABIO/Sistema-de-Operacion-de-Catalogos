<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <AppLayout :title="tituloPag">
        <div class="flex justify-center items-center bg-gray-80 overflow-auto">
            <div class="p-6 lg:p-8 max-w-[70%] max-h-[70%] flex justify-center items-center">
                <ApplicationLogo />
            </div>
        </div>
    </AppLayout>
</template>