<template>
    <Teleport to="body">
        <div v-if="localVisible" class="notificacion-overlay" @click.self="handleClickOverlay">
            <div class="notificacion-container" :class="`tipo-${tipo}`">
                
                <h3 class="notificacion-titulo">{{ titulo }}</h3>

                <div class="notificacion-contenido-principal">
                    <div class="notificacion-header">
                       
                    </div>
                    <div class="notificacion-body">
                        <p v-html="mensaje"></p> 
                    </div>
                    <div class="notificacion-footer">
                        <BotonAceptar @click="closeModal"></BotonAceptar>
                    </div>
                </div>

                <div v-if="localDuration > 0" class="notificacion-progress-bar-container">
                    <div class="notificacion-progress-bar" ref="progressBarRef"
                        :class="{ 'animate-progress': animateBar }"
                        :style="{ '--progress-duration': localDuration + 'ms' }">
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, watch, onUnmounted, nextTick } from 'vue';
import { ElButton } from 'element-plus';
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';


const props = defineProps({
    visible: { type: Boolean, default: false },
    titulo: { type: String, default: 'Notificación' },
    mensaje: { type: String, required: true },
    tipo: { type: String, default: 'info' },
    duracion: { type: Number, default: 5000 },
    closeOnOverlayClick: { type: Boolean, default: false }
});

const emit = defineEmits(['close']);

const localVisible = ref(props.visible);
const localDuration = ref(props.duracion);
let timerId = null;
const progressBarRef = ref(null);
const animateBar = ref(false); 

watch(() => props.visible, (newVal) => {
    localVisible.value = newVal;
    if (newVal) {
        localDuration.value = props.duracion;
        startTimer();

        animateBar.value = false; 
        nextTick(() => { 
            requestAnimationFrame(() => { 
                animateBar.value = true; 
                console.log("NotificacionModal: Animación de barra iniciada con clase. Duración:", localDuration.value);
            });
        });

    } else {
        clearTimer();
        animateBar.value = false; 
    }
});

watch(() => props.duracion, (newVal) => {
    localDuration.value = newVal; 
    if (localVisible.value && newVal > 0) {
        startTimer();
        animateBar.value = false;
        nextTick(() => {
            requestAnimationFrame(() => {
                animateBar.value = true;
            });
        });
    } else if (newVal === 0) {
        animateBar.value = false; 
    }
});

const closeModal = () => { clearTimer(); emit('close'); };
const handleClickOverlay = () => { if (props.closeOnOverlayClick) closeModal(); };

const startTimer = () => {
    clearTimer();
    if (localDuration.value > 0) {
        timerId = setTimeout(() => { closeModal(); }, localDuration.value);
    }
};
const clearTimer = () => { if (timerId) { clearTimeout(timerId); timerId = null; } };

onUnmounted(clearTimer);

if (props.visible && props.duracion > 0) {
    startTimer();
}
</script>

<style scoped>
.notificacion-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2100;
    padding: 15px;
    box-sizing: border-box;
}

.notificacion-container {
    background-color: #f9fafb; 
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.25);
    width: 420px;
    max-width: 90%;
    padding: 20px; 
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.notificacion-titulo {
    background-color: #d9e1eb;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    color: #334155;
    padding: 12px 20px;
    font-size: 1.1em;
    font-weight: 600;
    text-align: left;
    margin: 0; 
}

.notificacion-contenido-principal {
    /* background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05); */
    padding: 25px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    margin-top: 16px;
}


.notificacion-header {
    display: flex;
    justify-content: center;
}

.icono-notificacion {
    font-size: 2.8em;
    line-height: 1;
}

.icono-exito { color: #67c23a; }
.icono-error { color: #f56c6c; }

.notificacion-body p {
    font-size: 1em;
    color: #606266;
    line-height: 1.6;
    margin: 0;
    text-align: left;
}

.notificacion-footer {
    margin-top: 10px;
    display: flex;
    justify-content: center;
}

.notificacion-progress-bar-container {
    height: 6px;
    background-color: #ebeef5;
    margin: 20px -20px -20px -20px; 
}

.notificacion-progress-bar {
    height: 100%;
    width: 100%;
}

.notificacion-progress-bar.animate-progress {
    animation-name: decreaseWidthModal;
    animation-timing-function: linear;
    animation-fill-mode: forwards;
    animation-duration: var(--progress-duration);
}

.notificacion-container.tipo-success .notificacion-progress-bar { background-color: #67c23a; }
.notificacion-container.tipo-error .notificacion-progress-bar { background-color: #f56c6c; }
.notificacion-container.tipo-warning .notificacion-progress-bar { background-color: #e6a23c; }
.notificacion-container.tipo-info .notificacion-progress-bar { background-color: #409eff; }

@keyframes decreaseWidthModal {
    from { width: 100%; }
    to { width: 0%; }
}
</style>