<script> 

export default {
  name: 'NuevoButton',
  props: {
    toolPosicion: {
        type: String,
        required: false,
    },
    habActTax: {
      type: Boolean,
      required: false,
    }
  },

  
  methods: {
    crear() {
      // Implementa la lógica para el método crear
      this.$emit('crear'); // Si quieres emitir un evento al componente padre
    }
  }
}
</script>

<template>
    <el-tooltip class="item" effect="dark" content="Insertar" :placement= "toolPosicion">
      <el-button circle type="primary" class="float-right" @click.prevent="crear" :disabled = habActTax>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-down" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1h-2z"/>
          <path fill-rule="evenodd" d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
        </svg>
      </el-button>
    </el-tooltip>
  </template>