<script setup>
import { ref, watch, defineProps, defineEmits, computed } from 'vue'; 
import { ElMessage } from 'element-plus';
import GuardarButton from '@/Components/Biotica/GuardarButton.vue';

const props = defineProps({
    accion: {
        type: String,
        required: true, 
    },
    biblioEdit: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['cerrar', 'formSubmited']);

const form = ref({
    Autor: '',
    Anio: '',
    TituloPublicacion: '',
    TituloSubPublicacion: '',
    EditorialPaisPagina: '',
    NumeroVolumenAnio: '',
    EditoresCompiladores: '',
    ISBNISSN: '',
});

const formTitle = computed(() => {
    return props.accion === 'crear' ? 'Insertar nueva bibliografía' : 'Modificar bibliografía ';
});

watch(() => props.biblioEdit, (newVal) => {
    if (props.accion === 'editar' && newVal) {
        form.value = { ...newVal };
    } else {
        form.value = {
            Autor: '', Anio: '', TituloPublicacion: '', TituloSubPublicacion: '',
            EditorialPaisPagina: '', NumeroVolumenAnio: '', EditoresCompiladores: '', ISBNISSN: ''
        };
    }
}, { immediate: true, deep: true });

const referenciaCompleta = ref('');
watch(form, (newForm) => {
    referenciaCompleta.value = [
        newForm.Autor,
        newForm.Anio ? `(${newForm.Anio})` : '',
        newForm.TituloPublicacion,
        newForm.TituloSubPublicacion,
        newForm.EditorialPaisPagina,
        newForm.NumeroVolumenAnio,
        newForm.EditoresCompiladores ? `(Ed./Comp. ${newForm.EditoresCompiladores})` : '',
        newForm.ISBNISSN,
    ].filter(Boolean).join('. '); 
}, { deep: true });

const submitForm = () => {
    if (!form.value.Autor || !form.value.Anio || !form.value.TituloPublicacion) {
        ElMessage({
            type: 'error',
            message: 'Los campos marcados con * son obligatorios.',
        });
        return;
    }
    emit('formSubmited', form.value);
};
</script>

<template>
    <DialogGeneral v-model="dialogVisible" :bot-cerrar="true" :press-esc="true">
        <div class="dialog-header">
            <h3>{{ formTitle }}</h3>
        </div>
        <div class="header">
            <div class="dialog-body">
                <el-form :model="form" label-position="top" class="bibliografia-form">
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Autor(es)" required>
                                <el-input v-model="form.Autor" maxlength="255" show-word-limit placeholder="Autor(es) de la publicación"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Año(s)" required>
                                <el-input v-model="form.Anio" maxlength="50" show-word-limit placeholder="Año de la publicación"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Título de la publicación" required>
                                <el-input v-model="form.TituloPublicacion" maxlength="255" show-word-limit placeholder="Título principal"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Referencia completa">
                                <el-input type="textarea" v-model="referenciaCompleta" :rows="5" readonly disabled></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Título de la subpublicación">
                                <el-input v-model="form.TituloSubPublicacion" maxlength="255" show-word-limit placeholder="Ej. Título del capítulo"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Editorial, país, lugar, páginas">
                                <el-input v-model="form.EditorialPaisPagina" maxlength="255" show-word-limit placeholder="Datos de la editorial"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Número, volúmen, año, mes(es)">
                                <el-input v-model="form.NumeroVolumenAnio" maxlength="255" show-word-limit placeholder="Datos de la revista o serie"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Editor(es)/compilador(es)">
                                <el-input v-model="form.EditoresCompiladores" maxlength="255" show-word-limit placeholder="Si aplica"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="ISBN / ISSN">
                                <el-input v-model="form.ISBNISSN" maxlength="100" show-word-limit placeholder="ISBN o ISSN"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>

                <div class="form-actions">
                    <GuardarButton @click="submitForm" />
                </div>
            </div>
        </div>
    </DialogGeneral>
</template>

<style scoped>
/* ESTE ES EL CSS CORRECTO, COPIADO DEL OTRO FORMULARIO */
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
    background-color: #ffffff; 
    padding: 20px 24px;
    text-align: left;
    position: relative; 
    z-index: 10; 
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.08);
    border-radius: 10px;
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
    font-weight: 500 !important;
    padding-bottom: 4px !important;
    line-height: normal !important;
    font-size: 0.9em;
    color: #606266;
}
</style>