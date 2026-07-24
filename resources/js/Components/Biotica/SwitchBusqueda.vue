<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: 'inicia'
  }
});

const emit = defineEmits(['update:modelValue']);

const handleSwitch = (val, tipo) => {
  if (val) {
    emit('update:modelValue', tipo);
  }
};
</script>

<template>
  <div class="switches-group">
    <el-switch
      :model-value="props.modelValue === 'inicia'"
      @update:model-value="(val) => handleSwitch(val, 'inicia')"
      inline-prompt
      style="--el-switch-on-color: #13ce66;"
      active-text="Inicia con "
      inactive-text="Inicia con"
      class="custom-sw"
    />

    <el-switch
      :model-value="props.modelValue === 'contiene'"
      @update:model-value="(val) => handleSwitch(val, 'contiene')"
      inline-prompt
      style="--el-switch-on-color: #13ce66;"
      active-text="Contiene "
      inactive-text="Contiene"
      class="custom-sw"
    />

    <el-switch
      :model-value="props.modelValue === 'termina'"
      @update:model-value="(val) => handleSwitch(val, 'termina')"
      inline-prompt
      style="--el-switch-on-color: #13ce66;"
      active-text=" Termina con "
      inactive-text=" Termina con "
      class="custom-sw"
    />
  </div>
</template>

<style scoped>
.switches-group {
  display: flex;
  align-items: center;
  gap: 4px;
}

:deep(.custom-sw) {
  --el-switch-off-color: #0099ff;
  height: 24px;
  min-width: 100px !important;
}

:deep(.custom-sw .el-switch__inner) {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}

:deep(.custom-sw.is-checked .el-switch__inner) {
  padding-left: 8px;
  padding-right: 25px;
}

:deep(.custom-sw:not(.is-checked) .el-switch__inner) {
  padding-left: 22px;
  padding-right: 8px;
}

:deep(.custom-sw .el-switch__inner .is-text) {
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
}

:deep(.custom-sw .el-switch__action) {
  width: 13px;
  height: 13px;
}
</style>
