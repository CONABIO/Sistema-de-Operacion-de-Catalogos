<script setup>
import { ref, watch, nextTick, computed } from 'vue';  
import DialogGeneral from '@/Components/Biotica/DialogGeneral.vue'; 
import GuardarButton from "@/Components/Biotica/GuardarButton.vue";
import { ElMessage } from 'element-plus'; 

const props = defineProps({
    visible: Boolean,
    accion: String,
    gpoTaxEdit: Object,
});

const emit = defineEmits(['cerrar', 'formSubmited']);

const dialogVisible = ref(false); 

const form = ref({
    GrupoSCAT: '',
    GrupoAbreviado: '',
    GrupoSNIB: '',
});

const formRef = ref(null);

const dialogTitle = computed(() => {
    return props.accion === 'crear' ? 'Ingresar nuevo grupo taxonómico' : 'Modificar grupo taxonómico';
});

const rules = {
    GrupoSCAT: [
        { required: true, message: 'El nombre del grupo es obligatorio', trigger: 'blur' },
        { min: 1, max: 255, message: 'La longitud debe estar entre 1 y 255', trigger: 'blur' }
    ],
    GrupoAbreviado: [{ max: 5, message: 'La longitud debe ser menor o igual a 5', trigger: 'blur' }], 
    GrupoSNIB: [{ max: 100, message: 'La longitud debe ser menor o igual a 100', trigger: 'blur' }],
};

watch(() => props.visible, (newVal) => {
    dialogVisible.value = newVal; 
    if (newVal) {
        if (props.accion === 'editar' && props.gpoTaxEdit) {
            form.value = {
                GrupoSCAT: props.gpoTaxEdit?.GrupoSCAT || '',
                GrupoAbreviado: props.gpoTaxEdit?.GrupoAbreviado || '',
                GrupoSNIB: props.gpoTaxEdit?.GrupoSNIB || '',
            };
        } else {
            form.value = { GrupoSCAT: '', GrupoAbreviado: '', GrupoSNIB: '' };
        }
        nextTick(() => {
            formRef.value?.clearValidate();
        });
    }
}, { immediate: true });

watch(dialogVisible, (newVal) => {
    if (!newVal) {
        emit('cerrar');
    }
});

const intentarGuardar = async () => {
    if (!formRef.value) return;

    try {
        const isValid = await formRef.value.validate();
        if (isValid) {
            const datosParaEnviar = {
                ...form.value,
                idParaEditar: props.accion === 'editar' ? props.gpoTaxEdit?.IdGrupoSCAT : null,
                accionOriginal: props.accion,
            };
            emit('formSubmited', datosParaEnviar);
        } else {
             ElMessage.error('Por favor, corrija los errores en el formulario.');
        }
    } catch (error) {
       console.log('Validación fallida, no se emite evento.');
    }
};

</script>

<template>
    <!-- Usamos la misma estructura de siempre -->
    <DialogGeneral v-model="dialogVisible" :bot-cerrar="true" :press-esc="true">
        <div class="dialog-header">
            <h3>{{ dialogTitle }}</h3>
        </div>
        <div class="header">
            <div class="dialog-body">
                <!-- Tu formulario de Grupo Taxonómico, sin tocar los campos -->
                <el-form :model="form" :rules="rules" ref="formRef" label-position="top" @submit.prevent="intentarGuardar">
                    <el-form-item label="Nombre del Grupo" prop="GrupoSCAT">
                        <el-input v-model="form.GrupoSCAT" maxlength="255" show-word-limit />
                    </el-form-item>
                    <el-form-item label="Abreviado" prop="GrupoAbreviado">
                        <el-input v-model="form.GrupoAbreviado" maxlength="5" show-word-limit />
                    </el-form-item>
                    <el-form-item label="Grupo SNIB" prop="GrupoSNIB">
                        <el-input v-model="form.GrupoSNIB" maxlength="100" show-word-limit />
                    </el-form-item>
                </el-form>

                <div class="form-actions">
                    <GuardarButton @click="intentarGuardar" />
                </div>
            </div>
        </div>
    </DialogGeneral>
</template>

<style scoped>
/* Y el CSS que nunca falla, directo de los otros componentes */
:deep(.el-dialog__body) {
    padding: 0 !important;
}

.dialog-header {
    background-color: #f1f7ff;
    padding: 20px 24px;
    border-bottom: 1px solid #e4e7ed;
    text-align: left;
    border-radius: 10px;
    margin-bottom: 10px;
}

.header {
    background-color: #f3f3f3;
    padding: 20px 24px;
    border: 3px;
    text-align: left;
    border-radius: 10px;
    background-color: #ffffff; 
    padding: 20px 24px;
    text-align: left;
    position: relative; 
    z-index: 10; 
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.08);
}

.dialog-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #303133;
}

.dialog-body {
    padding: 30px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 24px;
}

:deep(.el-form-item) {
    margin-bottom: 22px;
}

:deep(.el-form-item__label) {
    padding-bottom: 4px;
    line-height: normal;
    font-size: 0.9em;
    color: #606266;
}
</style>