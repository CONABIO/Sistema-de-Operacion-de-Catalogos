<script setup>
import { ref, watch, nextTick, computed } from 'vue'; 
import DialogGeneral from '@/Components/Biotica/DialogGeneral.vue'; 
import { ElMessage } from 'element-plus'; 
import GuardarButton from "@/Components/Biotica/GuardarButton.vue";

const props = defineProps({
    visible: Boolean,
    accion: String,
    autTaxEdit: Object,
});

const emit = defineEmits(['cerrar', 'formSubmited']); 

const dialogVisible = ref(false);

const autorTax = ref({ nombreAutoridad: '', nombreCompleto: '', grupoTaxonomico: '' });
const autorTaxFormRef = ref(null);

const dialogTitle = computed(() => {
    return props.accion === 'crear' ? 'Ingresar nuevo autor taxonómico' : 'Modificar autor taxonómico';
});

const rules = {
    nombreAutoridad: [
        { required: true, message: 'El nombre de la autoridad es obligatorio', trigger: 'blur' },
        { min: 1, max: 100, message: 'El tamaño debe ser entre 1 y 100', trigger: ['blur', 'change'] }
    ],
    nombreCompleto: [
        { max: 255, message: 'El tamaño debe ser menor o igual a 255 caracteres', trigger: ['blur', 'change'] }
    ],
    grupoTaxonomico: [
        { required: true, message: 'El grupo taxonómico es obligatorio', trigger: 'blur' },
        { max: 255, message: 'El tamaño debe ser menor o igual a 255 caracteres', trigger: ['blur', 'change'] }
    ]
};

watch(() => props.visible, (newVal) => {
    dialogVisible.value = newVal; 
    if (newVal) {
        if (props.accion === 'editar' && props.autTaxEdit) {
            autorTax.value = {
                nombreAutoridad: props.autTaxEdit?.NombreAutoridad || '',
                nombreCompleto: props.autTaxEdit?.NombreCompleto || '',
                grupoTaxonomico: props.autTaxEdit?.GrupoTaxonomico || '',
            };
        } else { 
            autorTax.value = { nombreAutoridad: '', nombreCompleto: '', grupoTaxonomico: '' };
        }
        nextTick(() => {
            if (autorTaxFormRef.value) {
                autorTaxFormRef.value.clearValidate();
            }
        });
    }
}, { immediate: true });

watch(dialogVisible, (newVal) => {
    if (!newVal) {
        emit('cerrar');
    }
});

const intentarGuardar = async () => {
    if (!autorTaxFormRef.value) return;

    try {
        const isValid = await autorTaxFormRef.value.validate();
        if (isValid) {
            const datosParaEnviar = {
                ...autorTax.value, 
                idParaEditar: props.accion === 'editar' ? props.autTaxEdit?.IdAutorTaxon : null,
                accionOriginal: props.accion 
            };
            emit('formSubmited', datosParaEnviar);
        } else {
            ElMessage.error('Por favor, corrija los errores en el formulario.');
        }
    } catch (error) {
        console.log('Error de validación:', error);
    }
};
</script>

<template>
    <DialogGeneral v-model="dialogVisible" :bot-cerrar="true" :press-esc="true">
        <div class="dialog-header">
            <h3>{{ dialogTitle }}</h3>
        </div>
        <div class="header">
            <div class="dialog-body">
                <el-form 
                    :model="autorTax" 
                    :rules="rules" 
                    ref="autorTaxFormRef" 
                    label-position="top" 
                    @submit.prevent="intentarGuardar" 
                >
                    <el-form-item label="Nombre de la autoridad" prop="nombreAutoridad">
                        <el-input 
                            type="text" 
                            maxlength="100" 
                            v-model="autorTax.nombreAutoridad" 
                            show-word-limit 
                        ></el-input>
                    </el-form-item>

                    <el-form-item label="Nombre completo" prop="nombreCompleto">
                        <el-input 
                            type="text" 
                            maxlength="255" 
                            show-word-limit 
                            v-model="autorTax.nombreCompleto" 
                        ></el-input>
                    </el-form-item>

                    <el-form-item label="Grupo taxonómico" prop="grupoTaxonomico">
                        <el-input 
                            type="text" 
                            maxlength="255" 
                            show-word-limit 
                            v-model="autorTax.grupoTaxonomico" 
                        ></el-input>
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