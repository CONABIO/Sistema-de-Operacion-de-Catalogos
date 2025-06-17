<template>
  <div v-if="visible" class="modal-overlay" @click.self="handleOverlayClick"> <!-- Añadido .self -->
    <div class="modal-container">
      <div class="modal-header">
        <slot name="header"></slot>
        <button @click="triggerClose" class="modal-close-button" aria-label="Cerrar modal"> <!-- Llama a triggerClose -->
          <span style="font-size: 24px; color: rgba(0, 0, 0, 0.5)">×</span>
        </button>
      </div>
      <div class="modal-body">
        <slot></slot>
      </div>
      <div class="modal-footer">
        <slot name="footer"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, onMounted, onUnmounted } from "vue"; // Añadido onMounted, onUnmounted

const props = defineProps({
  visible: {
    type: Boolean,
    required: true
  },
  onClose: { // Tu prop original
    type: Function,
    required: true
  },
  // Nuevas props opcionales para controlar el comportamiento de cierre
  closeOnOverlayClick: {
    type: Boolean,
    default: false // Por defecto, NO cerrar al hacer clic en overlay
  },
  closeOnEsc: {
    type: Boolean,
    default: true // Por defecto, SÍ cerrar con tecla ESC
  }
});

const emit = defineEmits(["close"]); // Tu emit original

// Esta función se llamará por el botón X, clic en overlay (si está habilitado), o tecla ESC
const triggerClose = () => {
  console.log("ModalGenerico: triggerClose llamado");
  props.onClose(); // Llama a la función pasada por la prop onClose
  emit("close");   // Emite el evento close
};

const handleOverlayClick = () => {
  if (props.closeOnOverlayClick) {
    console.log("ModalGenerico: Clic en overlay detectado y permitido.");
    triggerClose();
  } else {
    console.log("ModalGenerico: Clic en overlay detectado, pero closeOnOverlayClick es false.");
  }
};

const handleEscKey = (event) => {
  if (props.visible && props.closeOnEsc && event.key === 'Escape') {
    console.log("ModalGenerico: Tecla ESC detectada.");
    triggerClose();
  }
};

onMounted(() => {
  if (props.closeOnEsc) {
    window.addEventListener('keydown', handleEscKey);
  }
});

onUnmounted(() => {
  if (props.closeOnEsc) {
    window.removeEventListener('keydown', handleEscKey);
  }
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000; /* O un valor más alto si es necesario, ej: 2050 */
}

.modal-container {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  width: 600px; 
  max-width: 95%; 
  padding: 20px;
  position: relative;
  display: flex;
  flex-direction: column;
  max-height: 90vh; 
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
  flex-shrink: 0;
}

.modal-close-button {
  background-color: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  font-weight: bold;
  padding: 0;
  line-height: 1;
  color: #909399;
}
.modal-close-button:hover {
  color: #409EFF;
}

.modal-body {
  padding: 20px 0;
  overflow-y: auto;
  flex-grow: 1;
}

.modal-footer {
  padding-top: 15px;
  margin-top: auto;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  flex-shrink: 0;
  gap: 10px;
}
</style>