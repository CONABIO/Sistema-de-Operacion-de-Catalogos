<template>
  <el-tooltip class="item" effect="dark" content="Modificar" :placement = toolPosicion>
    <el-button circle type="success" class="float-right" @click="$emit('editar')" :disabled = habActTax>

       <el-icon>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path fill="currentColor" d="m199.04 672.64 193.984 112 224-387.968-193.92-112-224 388.032zm-23.872 60.16 32.896 148.288 144.896-45.696zM455.04 229.248l193.92 112 56.704-98.112-193.984-112-56.64 98.112zM104.32 708.8l384-665.024 304.768 175.936L409.152 884.8h.064l-248.448 78.336zm384 254.272v-64h448v64h-448z"></path></svg>
      </el-icon>
    </el-button>
  </el-tooltip>
</template>
  
  <script>
  export default {
    name: 'EditarButton',

  props: {
    toolPosicion: {
        type: String,
        required: false,
    }, 
    habActTax:{
      type: Boolean,
      required: false,
    },
    tamaño:{
      type:String,
      default: 'default',
      validator: (value) => ['large', 'default', 'small', 'mini'].includes(value),
    }
  },

    methods: {
      editar() {
        this.$emit('editar');
      }
    }
  }
  </script>