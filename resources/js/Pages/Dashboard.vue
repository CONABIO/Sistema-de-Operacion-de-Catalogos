<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="py-12" style="max-height: 2400px;">
            <div class="max-w-[89%] mx-auto sm:px-4 lg:px-12">
                <div class="bg-white overflow-hidden shadow-md sm:rounded-lg">
                    <Welcome />
                </div>
            </div>
        </div>
    </AppLayout>
</template>