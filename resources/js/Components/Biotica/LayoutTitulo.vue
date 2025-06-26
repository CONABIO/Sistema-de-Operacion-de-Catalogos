<script setup>
const props = defineProps({
    titulo: {
        type: String,
        required: true,
    },
});
</script>
<template>

    <div class=" shadow-md sm:rounded-lg w-full py-4" style="background-color: #d9e1eb; color: white; border-radius: 8px;">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight pl-8">
            {{ titulo }}
        </h2>
    </div>
</template>