<script setup>
  import { processIcon, getSafeIconPath } from '@/Composables/iconos';

    const props = defineProps({
                    rutaCategoria: {
                      type: String,
                      required: true,
                    },
                    textoInfo:{
                      type:String,
                      required:false
                    }
                  });
</script>

<template>
    <i class = "icon">
      <el-tooltip v-if = "props.textoInfo" :content="props.textoInfo" placement="left-start">
        <img :src = "processIcon(rutaCategoria)" style="width: 25px; height: 25px;">
      </el-tooltip>
      <img v-else :src = "processIcon(rutaCategoria)" style="width: 25px; height: 25px;">
    </i>
</template>