<template>
  <div class="d-flex flex-row-reverse">
    <el-row :gutter="10">

      <div v-if="accion === 'editar'">
        <el-tooltip class="item" effect="dark" :content="tooltipContent" placement="bottom">
          

          <el-button :size="size" :type="type" :circle="circle" @click="handleClick">
            <slot name="icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-usb-drive"
                viewBox="0 0 16 16">
                <path
                  d="M6 .5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4H6v-4ZM7 1v1h1V1H7Zm2 0v1h1V1H9ZM6 5a1 1 0 0 0-1 1v8.5A1.5 1.5 0 0 0 6.5 16h4a1.5 1.5 0 0 0 1.5-1.5V6a1 1 0 0 0-1-1H6Zm0 1h5v8.5a.5.5 0 0 1-.5.5h-4a.5.5 0 0 1-.5-.5V6Z" />
              </svg>
            </slot>
          </el-button>
        </el-tooltip>
      </div>
      <div v-else>
        <el-tooltip class="item" effect="dark" :content="tooltipContent" placement="bottom">
          <el-button :size="size" :type="type" :circle="circle" @click="handleClick">
            <slot name="icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-usb-drive"
                viewBox="0 0 16 16">
                <path
                  d="M6 .5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4H6v-4ZM7 1v1h1V1H7Zm2 0v1h1V1H9ZM6 5a1 1 0 0 0-1 1v8.5A1.5 1.5 0 0 0 6.5 16h4a1.5 1.5 0 0 0 1.5-1.5V6a1 1 0 0 0-1-1H6Zm0 1h5v8.5a.5.5 0 0 1-.5.5h-4a.5.5 0 0 1-.5-.5V6Z" />
              </svg>
            </slot>
          </el-button>
        </el-tooltip>
      </div>
    </el-row>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';


const props = defineProps({
  accion: {
    type: String,
    required: true, // '
  },
  tooltipContent: {
    type: String,
    default: 'Guardar',
  },
  size: {
    type: String,
    default: 'default',
  },
  type: {
    type: String,
    default: 'warning',
  },
  circle: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['confirmar']);

function handleClick() {
  emit('confirmar');
}
</script>

<style scoped></style>