<template>
    <Teleport to="body">
        <div v-if="localVisible" class="notificacion-overlay" @click.self="handleClickOverlay">
            <div class="notificacion-container" :class="`tipo-${tipo}`">
                <div class="notificacion-header">
                    
                    <span v-if="tipo === 'error'" class="icono-notificacion icono-error">
                        <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" width="32" height="32">
                            <path fill="currentColor"
                                d="M512 64a448 448 0 1 1 0 896a448 448 0 0 1 0-896zm0 832a384 384 0 0 0 0-768a384 384 0 0 0 0 768zm48-420.608l123.776-123.776a32 32 0 1 1 45.248 45.248L562.048 512l123.776 123.776a32 32 0 1 1-45.248 45.248L512 562.048l-123.776 123.776a32 32 0 1 1-45.248-45.248L461.504 512l-123.776-123.776a32 32 0 1 1 45.248-45.248L512 461.504z">
                            </path>
                        </svg>
                    </span>
                    <h3 class="notificacion-titulo">{{ titulo }}</h3>
                </div>
                <div class="notificacion-body">
                    <p>{{ mensaje }}</p>
                </div>
                <div class="notificacion-footer">
                    <BotonAceptar @click="closeModal" ></BotonAceptar>
                </div>
                <div v-if="localDuration > 0" class="notificacion-progress-bar-container">
                    <div class="notificacion-progress-bar" ref="progressBarRef"
                        :class="{ 'animate-progress': animateBar }"
                        :style="{ '--progress-duration': localDuration + 'ms' }" 
                        ></div>
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
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.25);
    width: 420px;
    max-width: 90%;
    padding: 25px;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 18px;
    overflow: hidden;
}

.notificacion-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.icono-notificacion {
    font-size: 2.8em;
    line-height: 1;
}

.icono-exito {
    color: #67c23a;
}

.icono-error {
    color: #f56c6c;
}

.notificacion-titulo {
    font-size: 1.5em;
    font-weight: 600;
    color: #303133;
    margin: 0;
    line-height: 1.3;
}

.notificacion-body p {
    font-size: 1em;
    color: #606266;
    line-height: 1.6;
    margin: 0;
    padding: 0 10px;
}

.notificacion-footer {
    margin-top: 10px;
    display: flex;
    justify-content: center;
}

.boton-ok {
    min-width: 100px;
    font-weight: 500;
}


.notificacion-progress-bar-container {
    height: 6px;
    background-color: #ebeef5;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 20px;
    width: 100%;
}

.notificacion-progress-bar {
    height: 100%;
    background-color: #409eff;
    /* Color por defecto */
    width: 100%;
    /* Inicia al 100% */
    border-radius: 3px;
    /* La animación se activa/desactiva con la clase 'animate-progress' */
}

.notificacion-progress-bar.animate-progress {
    animation-name: decreaseWidthModal;
    animation-timing-function: linear;
    animation-fill-mode: forwards;
    animation-duration: var(--progress-duration);
    /* Usar variable CSS */
}

.notificacion-container.tipo-success .notificacion-progress-bar {
    background-color: #67c23a;
}

.notificacion-container.tipo-error .notificacion-progress-bar {
    background-color: #f56c6c;
}

.notificacion-container.tipo-warning .notificacion-progress-bar {
    background-color: #e6a23c;
}

.notificacion-container.tipo-info .notificacion-progress-bar {
    background-color: #409eff;
}

@keyframes decreaseWidthModal {
    from {
        width: 100%;
    }

    to {
        width: 0%;
    }
}

@media (max-width: 480px) {
    /* ... tus media queries ... */
}
</style>