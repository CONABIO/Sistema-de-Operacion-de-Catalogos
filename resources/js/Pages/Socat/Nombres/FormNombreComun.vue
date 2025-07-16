<script setup>
import { ref, watch, nextTick, computed } from 'vue'; 
import DialogGeneral from '@/Components/Biotica/DialogGeneral.vue'; 
import GuardarButton from "@/Components/Biotica/GuardarButton.vue";
import { ElMessage } from 'element-plus';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    visible: Boolean,
    accion: String,
    nomComEdit: Object,
});

const emit = defineEmits(['cerrar', 'formSubmited']);

const dialogVisible = ref(false); 

const form = ref({
    NomComun: '',
    Observaciones: '',
    Lengua: '',
});

const formRef = ref(null);

const rules = {
    NomComun: [
        { required: true, message: 'El nombre común es un dato obligatorio, por lo que no puede quedar en blanco', trigger: 'blur' },
        { min: 1, max: 255, message: 'La longitud debe estar entre 1 y 255', trigger: 'blur' }
    ],
    Observaciones: [
        { max: 500, message: 'La longitud debe ser menor o igual a 500', trigger: 'blur' }
    ],
    Lengua: [
        { required: true, message: 'La lengua es un dato obligatorio, por lo que no puede quedar en blanco', trigger: 'blur' },
        { max: 50, message: 'La longitud debe ser menor o igual a 50', trigger: 'blur' }
    ],
};

const dialogTitle = computed(() => {
    return props.accion === 'crear' ? 'Ingresar un nuevo nombre común' : 'Modificar el nombre común seleccionado';
});

watch(() => props.visible, (newVal) => {
    dialogVisible.value = newVal; 
    if (newVal) {
        if (props.accion === 'editar' && props.nomComEdit) {
            form.value = {
                NomComun: props.nomComEdit?.NomComun || '',
                Observaciones: props.nomComEdit?.Observaciones || '',
                Lengua: props.nomComEdit?.Lengua || '',
            };
        } else {
            form.value = { NomComun: '', Observaciones: '', Lengua: '' };
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

    const isValid = await formRef.value.validate();
    if (isValid) {
        const datosParaEnviar = {
            ...form.value,
            idParaEditar: props.accion === 'editar' ? props.nomComEdit?.IdNomComun : null,
            accionOriginal: props.accion,
        };
        emit('formSubmited', datosParaEnviar);
    } else {
        ElMessage.error('Por favor, corrija los errores en el formulario.');
    }
};

const cerrarDialogo = () => {
    emit('cerrar');
};

</script>

<template>
    <DialogGeneral v-model="dialogVisible" :bot-cerrar="true" :press-esc="true">
        <div class="dialog-header">
                <h3>{{ dialogTitle }}</h3>
            </div>
        <div class="header">
            <div class="form-actions">
                <GuardarButton @click="intentarGuardar"/>
                <BotonSalir accion="cerrar" @salir="cerrarDialogo" />
            </div>
            <div class="dialog-body">
                <el-form :model="form" ref="formRef" :rules="rules" label-position="top">
                    <el-form-item label="NomComun" prop="NomComun">
                        <el-input type="text" v-model="form.NomComun" maxlength="255" show-word-limit />
                    </el-form-item>

                    <el-form-item label="Lengua" prop="Lengua">
                        <el-input type="text" v-model="form.Lengua" maxlength="50" show-word-limit />
                    </el-form-item>

                    <el-form-item label="Observaciones" prop="Observaciones">
                        <el-input type="textarea" v-model="form.Observaciones" maxlength="500" show-word-limit />
                    </el-form-item>

                </el-form>

            </div>
        </div>

    </DialogGeneral>
</template>

<style scoped>
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
    margin-top: 4px;
    margin-right: 35px;
    gap: 25px;
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