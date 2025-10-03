<script setup>
    const props = defineProps({
                    rutaCategoria: {
                      type: String,
                      required: true,
                    },
                  });
</script>

<template>
    <i class = "icon">
        <img :src = "rutaCategoria" style="width: 25px; height: 25px;">
    </i>
</template>