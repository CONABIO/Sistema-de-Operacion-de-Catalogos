<script setup>
  import { processIcon, getSafeIconPath } from '@/Composables/iconos';

    const props = defineProps({
                    rutaCategoria: {
                      type: String,
                      required: true,
                    },
                  });
</script>

<template>
    <i class = "icon">
        <img :src = "processIcon(rutaCategoria)" style="width: 25px; height: 25px;">
    </i>
</template>